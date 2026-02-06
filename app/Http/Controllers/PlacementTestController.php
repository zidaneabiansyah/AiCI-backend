<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlacementTest\CompleteTestRequest;
use App\Http\Requests\PlacementTest\StartTestRequest;
use App\Http\Requests\PlacementTest\SubmitAnswerRequest;
use App\Models\PlacementTest;
use App\Models\TestAttempt;
use App\Models\TestQuestion;
use App\Services\PlacementTestService;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Controller untuk Placement Test
 * 
 * Endpoints:
 * - GET /placement-test - List available tests
 * - GET /placement-test/{test} - Show test info & pre-assessment form
 * - POST /placement-test/{test}/start - Start test (create attempt)
 * - GET /placement-test/attempt/{attempt} - Show test questions
 * - POST /placement-test/attempt/{attempt}/answer - Submit answer
 * - POST /placement-test/attempt/{attempt}/complete - Complete test
 * - GET /placement-test/result/{attempt} - Show test result
 */
class PlacementTestController extends BaseController
{
    /**
     * Constructor - inject services
     */
    public function __construct(
        protected PlacementTestService $testService,
        protected RecommendationService $recommendationService
    ) {}

    /**
     * Display list of available placement tests
     * 
     * @return \Inertia\Response
     */
    public function index()
    {
        $tests = PlacementTest::active()
            ->orderBy('education_level')
            ->get()
            ->map(function ($test) {
                return [
                    'id' => $test->id,
                    'title' => $test->title,
                    'slug' => $test->slug,
                    'description' => $test->description,
                    'education_level' => $test->education_level,
                    'duration_minutes' => $test->duration_minutes,
                    'total_questions' => $test->total_questions,
                ];
            });

        return Inertia::render('PlacementTest/Index', [
            'tests' => $tests,
        ]);
    }

    /**
     * Show test info and pre-assessment form
     * 
     * @param PlacementTest $test
     * @return \Inertia\Response
     */
    public function show(PlacementTest $test)
    {
        // Check if user already has completed attempt
        $existingAttempt = null;
        if (auth()->check()) {
            $existingAttempt = TestAttempt::where('user_id', auth()->id())
                ->where('placement_test_id', $test->id)
                ->where('status', 'completed')
                ->latest()
                ->first();
        }

        return Inertia::render('PlacementTest/Show', [
            'test' => [
                'id' => $test->id,
                'title' => $test->title,
                'description' => $test->description,
                'instructions' => $test->instructions,
                'duration_minutes' => $test->duration_minutes,
                'total_questions' => $test->total_questions,
                'passing_score' => $test->passing_score,
                'allow_retake' => $test->allow_retake,
                'retake_cooldown_days' => $test->retake_cooldown_days,
            ],
            'existingAttempt' => $existingAttempt ? [
                'id' => $existingAttempt->id,
                'score' => $existingAttempt->score,
                'level_result' => $existingAttempt->level_result,
                'completed_at' => $existingAttempt->completed_at,
            ] : null,
        ]);
    }

    /**
     * Start placement test (create test attempt)
     * 
     * Flow:
     * 1. Validate pre-assessment data
     * 2. Create test attempt
     * 3. Redirect to test page
     * 
     * @param StartTestRequest $request
     * @param PlacementTest $test
     * @return \Illuminate\Http\RedirectResponse
     */
    public function start(StartTestRequest $request, PlacementTest $test)
    {
        try {
            $user = auth()->user() ?? auth()->loginUsingId(1); // Guest support (temp)

            $attempt = $this->testService->createAttempt(
                $test,
                $user,
                $request->validated()
            );

            return $this->redirectWithSuccess(
                'placement-test.attempt',
                'Test dimulai! Selamat mengerjakan.',
                ['attempt' => $attempt->id]
            );

        } catch (\Exception $e) {
            return $this->backWithError($e->getMessage());
        }
    }

    /**
     * Show test questions page
     * 
     * @param TestAttempt $attempt
     * @return \Inertia\Response
     */
    public function attempt(TestAttempt $attempt)
    {
        // Authorization: Only owner can access
        if ($attempt->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to test attempt.');
        }

        // Check if already completed
        if ($attempt->status->value === 'completed') {
            return redirect()->route('placement-test.result', $attempt);
        }

        // Check if expired
        if ($attempt->isExpired()) {
            $attempt->updateStatus('expired');
            return redirect()
                ->route('placement-test.show', $attempt->placementTest)
                ->with('error', 'Waktu test telah habis.');
        }

        // Get test data
        $testData = $this->testService->getTestData($attempt);

        return Inertia::render('PlacementTest/Attempt', [
            'attempt' => [
                'id' => $attempt->id,
                'status' => $attempt->status->value,
                'started_at' => $attempt->started_at,
                'expires_at' => $attempt->expires_at,
            ],
            'test' => [
                'title' => $attempt->placementTest->title,
                'duration_minutes' => $attempt->placementTest->duration_minutes,
            ],
            'questions' => $testData['questions'],
            'progress' => $testData['progress'],
            'timeRemaining' => $testData['time_remaining'],
        ]);
    }

    /**
     * Submit answer for a question
     * 
     * @param SubmitAnswerRequest $request
     * @param TestAttempt $attempt
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitAnswer(SubmitAnswerRequest $request, TestAttempt $attempt)
    {
        // Authorization
        if ($attempt->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', null, 403);
        }

        try {
            $question = TestQuestion::findOrFail($request->test_question_id);

            $answer = $this->testService->saveAnswer(
                $attempt,
                $question,
                $request->user_answer,
                $request->time_spent_seconds
            );

            // Refresh attempt to get updated stats
            $attempt->refresh();

            return $this->successResponse([
                'answer' => [
                    'id' => $answer->id,
                    'is_correct' => $answer->is_correct,
                    'points_earned' => $answer->points_earned,
                ],
                'progress' => [
                    'answered' => $attempt->answered_questions,
                    'total' => $attempt->total_questions,
                    'percentage' => $attempt->getCompletionPercentage(),
                ],
            ], 'Jawaban berhasil disimpan');

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Complete test and calculate results
     * 
     * @param CompleteTestRequest $request
     * @param TestAttempt $attempt
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(CompleteTestRequest $request, TestAttempt $attempt)
    {
        // Authorization
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            // Complete test
            $attempt = $this->testService->completeTest($attempt);

            // Generate recommendations
            $this->recommendationService->generateRecommendations($attempt);

            return $this->redirectWithSuccess(
                'placement-test.result',
                'Test selesai! Lihat hasil dan rekomendasi kelas Anda.',
                ['attempt' => $attempt->id]
            );

        } catch (\Exception $e) {
            return $this->backWithError($e->getMessage());
        }
    }

    /**
     * Show test result and recommendations
     * 
     * @param TestAttempt $attempt
     * @return \Inertia\Response
     */
    public function result(TestAttempt $attempt)
    {
        // Authorization
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        // Load relationships
        $attempt->load(['result', 'placementTest']);

        if (!$attempt->result) {
            return redirect()
                ->route('placement-test.show', $attempt->placementTest)
                ->with('error', 'Hasil test belum tersedia.');
        }

        $result = $attempt->result;

        // Get recommended classes
        $recommendedClasses = $result->getRecommendedClassModels();

        return Inertia::render('PlacementTest/Result', [
            'attempt' => [
                'id' => $attempt->id,
                'score' => $attempt->score,
                'level_result' => $attempt->level_result,
                'completed_at' => $attempt->completed_at,
                'time_spent_seconds' => $attempt->time_spent_seconds,
                'answered_questions' => $attempt->answered_questions,
                'correct_answers' => $attempt->correct_answers,
                'total_questions' => $attempt->total_questions,
            ],
            'result' => [
                'overall_score' => $result->overall_score,
                'level_achieved' => $result->level_achieved,
                'category_scores' => $result->category_scores,
                'strengths' => $result->strengths,
                'weaknesses' => $result->weaknesses,
                'performance_summary' => $result->performance_summary,
                'next_steps' => $result->next_steps,
            ],
            'recommendations' => $result->recommended_classes,
            'recommendedClasses' => $recommendedClasses->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'slug' => $class->slug,
                    'level' => $class->level,
                    'price' => $class->price,
                    'duration_hours' => $class->duration_hours,
                    'description' => $class->description,
                    'program_name' => $class->program->name,
                ];
            }),
        ]);
    }
}
