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
use Illuminate\Http\JsonResponse;

/**
 * Controller untuk Placement Test (API Version)
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
     * @return JsonResponse
     */
    public function index(): JsonResponse
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

        return $this->successResponse($tests, 'Available placement tests retrieved');
    }

    /**
     * Show test info and pre-assessment form
     * 
     * @param PlacementTest $test
     * @return JsonResponse
     */
    public function show(PlacementTest $test): JsonResponse
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

        return $this->successResponse([
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
        ], 'Placement test details retrieved');
    }

    /**
     * Start placement test (create test attempt)
     * 
     * @param StartTestRequest $request
     * @param PlacementTest $test
     * @return JsonResponse
     */
    public function start(StartTestRequest $request, PlacementTest $test): JsonResponse
    {
        try {
            $user = auth()->user();

            $attempt = $this->testService->createAttempt(
                $test,
                $user,
                $request->validated()
            );

            return $this->successResponse([
                'attempt_id' => $attempt->id
            ], 'Test started successfully');

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Show test questions
     * 
     * @param TestAttempt $attempt
     * @return JsonResponse
     */
    public function attempt(TestAttempt $attempt): JsonResponse
    {
        // Authorization: Only owner can access
        if ($attempt->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized access to test attempt.', null, 403);
        }

        // Check if already completed
        if ($attempt->status->value === 'completed') {
            return $this->successResponse([
                'status' => 'completed',
                'redirect_to_result' => true
            ], 'Test already completed');
        }

        // Check if expired
        if ($attempt->isExpired()) {
            $attempt->updateStatus('expired');
            return $this->errorResponse('Waktu test telah habis.', ['status' => 'expired']);
        }

        // Get test data
        $testData = $this->testService->getTestData($attempt);

        return $this->successResponse([
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
        ], 'Test questions retrieved');
    }

    /**
     * Submit answer for a question
     * 
     * @param SubmitAnswerRequest $request
     * @param TestAttempt $attempt
     * @return JsonResponse
     */
    public function submitAnswer(SubmitAnswerRequest $request, TestAttempt $attempt): JsonResponse
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
     * @return JsonResponse
     */
    public function complete(CompleteTestRequest $request, TestAttempt $attempt): JsonResponse
    {
        // Authorization
        if ($attempt->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', null, 403);
        }

        try {
            // Complete test
            $attempt = $this->testService->completeTest($attempt);

            // Generate recommendations
            $this->recommendationService->generateRecommendations($attempt);

            return $this->successResponse([
                'attempt_id' => $attempt->id
            ], 'Test completed successfully');

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Show test result and recommendations
     * 
     * @param TestAttempt $attempt
     * @return JsonResponse
     */
    public function result(TestAttempt $attempt): JsonResponse
    {
        // Authorization
        if ($attempt->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', null, 403);
        }

        // Load relationships
        $attempt->load(['result', 'placementTest']);

        if (!$attempt->result) {
            return $this->errorResponse('Hasil test belum tersedia.');
        }

        $result = $attempt->result;

        // Get recommended classes
        $recommendedClasses = $result->getRecommendedClassModels();

        return $this->successResponse([
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
        ], 'Test result retrieved successfully');
    }

    /**
     * Download test result PDF
     * 
     * @param TestAttempt $attempt
     * @return mixed
     */
    public function downloadResult(TestAttempt $attempt): mixed
    {
        // Authorization
        if ($attempt->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', null, 403);
        }

        try {
            $pdf = $this->testService->generateResultPdf($attempt);
            
            return $pdf->download("test-result-{$attempt->id}.pdf");

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
