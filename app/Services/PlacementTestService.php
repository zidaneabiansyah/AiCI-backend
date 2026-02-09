<?php

namespace App\Services;

use App\Enums\TestStatus;
use App\Models\PlacementTest;
use App\Models\TestAnswer;
use App\Models\TestAttempt;
use App\Models\TestQuestion;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk mengelola Placement Test
 * 
 * Responsibilities:
 * 1. Create test attempt (initialize test session)
 * 2. Save user answers
 * 3. Calculate scores
 * 4. Generate test results
 */
class PlacementTestService extends BaseService
{
    /**
     * Create new test attempt
     * 
     * Flow:
     * 1. Validate placement test exists & active
     * 2. Check if user can retake (cooldown period)
     * 3. Create test attempt record
     * 4. Set expiration time
     * 5. Return test attempt with questions
     * 
     * @param PlacementTest $placementTest
     * @param User $user
     * @param array $preAssessmentData
     * @return TestAttempt
     * @throws Exception
     */
    public function createAttempt(
        PlacementTest $placementTest,
        User $user,
        array $preAssessmentData
    ): TestAttempt {
        return $this->transaction(function () use ($placementTest, $user, $preAssessmentData) {
            // Validasi: Test harus active
            if (!$placementTest->is_active) {
                throw new Exception('Placement test tidak tersedia saat ini.');
            }

            // Check retake cooldown
            if (!$placementTest->allow_retake) {
                $existingAttempt = TestAttempt::where('user_id', $user->id)
                    ->where('placement_test_id', $placementTest->id)
                    ->where('status', TestStatus::COMPLETED->value)
                    ->exists();

                if ($existingAttempt) {
                    throw new Exception('Anda sudah pernah mengikuti test ini.');
                }
            } else {
                // Check cooldown period
                $lastAttempt = TestAttempt::where('user_id', $user->id)
                    ->where('placement_test_id', $placementTest->id)
                    ->where('status', TestStatus::COMPLETED->value)
                    ->latest('completed_at')
                    ->first();

                if ($lastAttempt) {
                    $cooldownUntil = $lastAttempt->completed_at
                        ->addDays($placementTest->retake_cooldown_days);

                    if (now()->isBefore($cooldownUntil)) {
                        $daysLeft = now()->diffInDays($cooldownUntil, false);
                        throw new Exception(
                            "Anda dapat mengulang test dalam {$daysLeft} hari lagi."
                        );
                    }
                }
            }

            // Get active questions
            $questions = $placementTest->getActiveQuestions();
            
            if ($questions->isEmpty()) {
                throw new Exception('Tidak ada soal tersedia untuk test ini.');
            }

            // Calculate expiration time
            $expiresAt = now()->addMinutes($placementTest->duration_minutes);

            // Create test attempt
            $attempt = TestAttempt::create([
                'user_id' => $user->id,
                'placement_test_id' => $placementTest->id,
                'status' => TestStatus::IN_PROGRESS,
                'full_name' => $preAssessmentData['full_name'],
                'email' => $preAssessmentData['email'],
                'age' => $preAssessmentData['age'],
                'education_level' => $preAssessmentData['education_level'],
                'current_grade' => $preAssessmentData['current_grade'] ?? null,
                'experience' => $preAssessmentData['experience'],
                'interests' => $preAssessmentData['interests'] ?? [],
                'started_at' => now(),
                'expires_at' => $expiresAt,
                'total_questions' => $questions->count(),
                'answered_questions' => 0,
                'correct_answers' => 0,
                'score' => 0,
            ]);

            $this->log('Test attempt created', [
                'attempt_id' => $attempt->id,
                'user_id' => $user->id,
                'test_id' => $placementTest->id,
            ]);

            return $attempt;
        });
    }

    /**
     * Save user answer for a question
     * 
     * Flow:
     * 1. Validate test attempt is in progress
     * 2. Validate question belongs to this test
     * 3. Check if already answered (prevent duplicate)
     * 4. Check correct answer
     * 5. Calculate points earned
     * 6. Save answer
     * 7. Update attempt statistics
     * 
     * @param TestAttempt $attempt
     * @param TestQuestion $question
     * @param string $userAnswer
     * @param int $timeSpentSeconds
     * @return TestAnswer
     * @throws Exception
     */
    public function saveAnswer(
        TestAttempt $attempt,
        TestQuestion $question,
        string $userAnswer,
        int $timeSpentSeconds
    ): TestAnswer {
        return $this->transaction(function () use ($attempt, $question, $userAnswer, $timeSpentSeconds) {
            // Validasi: Test harus in progress
            if ($attempt->status !== TestStatus::IN_PROGRESS) {
                throw new Exception('Test sudah selesai atau expired.');
            }

            // Validasi: Check expiration
            if ($attempt->isExpired()) {
                $attempt->updateStatus(TestStatus::EXPIRED);
                throw new Exception('Waktu test telah habis.');
            }

            // Validasi: Question harus dari test yang sama
            if ($question->placement_test_id !== $attempt->placement_test_id) {
                throw new Exception('Soal tidak valid untuk test ini.');
            }

            // Check if already answered
            $existingAnswer = TestAnswer::where('test_attempt_id', $attempt->id)
                ->where('test_question_id', $question->id)
                ->first();

            if ($existingAnswer) {
                throw new Exception('Soal ini sudah dijawab.');
            }

            // Check if answer is correct
            $isCorrect = $this->checkAnswer($question, $userAnswer);

            // Calculate points earned (dengan weight dari difficulty)
            $pointsEarned = $isCorrect 
                ? $question->points * $question->difficulty->weight()
                : 0;

            // Save answer
            $answer = TestAnswer::create([
                'test_attempt_id' => $attempt->id,
                'test_question_id' => $question->id,
                'user_answer' => $userAnswer,
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
                'time_spent_seconds' => $timeSpentSeconds,
            ]);

            // Update attempt statistics
            $attempt->increment('answered_questions');
            if ($isCorrect) {
                $attempt->increment('correct_answers');
            }
            $attempt->increment('time_spent_seconds', $timeSpentSeconds);

            $this->log('Answer saved', [
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'is_correct' => $isCorrect,
                'points' => $pointsEarned,
            ]);

            return $answer;
        });
    }

    /**
     * Check if user answer is correct
     * 
     * Case-insensitive comparison, trim whitespace
     * 
     * @param TestQuestion $question
     * @param string $userAnswer
     * @return bool
     */
    protected function checkAnswer(TestQuestion $question, string $userAnswer): bool
    {
        $correctAnswer = trim(strtolower($question->correct_answer));
        $userAnswer = trim(strtolower($userAnswer));

        return $correctAnswer === $userAnswer;
    }

    /**
     * Complete test and calculate final score
     * 
     * Flow:
     * 1. Validate all questions answered (optional)
     * 2. Calculate overall score
     * 3. Calculate category scores
     * 4. Determine level achieved
     * 5. Update attempt status
     * 6. Return attempt with scores
     * 
     * @param TestAttempt $attempt
     * @return TestAttempt
     * @throws Exception
     */
    public function completeTest(TestAttempt $attempt): TestAttempt
    {
        return $this->transaction(function () use ($attempt) {
            // Validasi: Test harus in progress
            if ($attempt->status !== TestStatus::IN_PROGRESS) {
                throw new Exception('Test sudah selesai atau expired.');
            }

            // Calculate scores
            $scores = $this->calculateScores($attempt);

            // Update attempt
            $attempt->update([
                'status' => TestStatus::COMPLETED,
                'completed_at' => now(),
                'score' => $scores['overall_score'],
                'level_result' => $scores['level_achieved'],
            ]);

            $this->log('Test completed', [
                'attempt_id' => $attempt->id,
                'score' => $scores['overall_score'],
                'level' => $scores['level_achieved'],
            ]);

            return $attempt->fresh();
        });
    }

    /**
     * Calculate test scores
     * 
     * Scoring algorithm:
     * 1. Calculate raw score (correct answers / total questions * 100)
     * 2. Apply difficulty weight
     * 3. Calculate category breakdown
     * 4. Determine level based on score thresholds
     * 
     * @param TestAttempt $attempt
     * @return array
     */
    protected function calculateScores(TestAttempt $attempt): array
    {
        $answers = $attempt->answers()->with('testQuestion')->get();
        
        if ($answers->isEmpty()) {
            return [
                'overall_score' => 0,
                'level_achieved' => 'beginner',
                'category_scores' => [],
            ];
        }

        // Calculate total possible points (dengan weight)
        $totalPossiblePoints = $answers->sum(function ($answer) {
            return $answer->testQuestion->points * $answer->testQuestion->difficulty->weight();
        });

        // Calculate earned points
        $totalEarnedPoints = $answers->sum('points_earned');

        // Calculate overall score (0-100)
        $overallScore = $totalPossiblePoints > 0
            ? $totalEarnedPoints / $totalPossiblePoints * 100
            : 0;

        // Calculate category scores
        $categoryScores = $this->calculateCategoryScores($answers);

        // Determine level achieved
        $levelAchieved = $this->determineLevelAchieved($overallScore);

        return [
            'overall_score' => round($overallScore, 2),
            'level_achieved' => $levelAchieved,
            'category_scores' => $categoryScores,
        ];
    }

    /**
     * Calculate scores per category
     * 
     * @param \Illuminate\Support\Collection $answers
     * @return array
     */
    protected function calculateCategoryScores($answers): array
    {
        $categoryScores = [];

        // Group by category
        $byCategory = $answers->groupBy(function ($answer) {
            return $answer->testQuestion->category->value;
        });

        foreach ($byCategory as $category => $categoryAnswers) {
            $totalPoints = $categoryAnswers->sum(function ($answer) {
                return $answer->testQuestion->points * $answer->testQuestion->difficulty->weight();
            });

            $earnedPoints = $categoryAnswers->sum('points_earned');

            $categoryScores[$category] = $totalPoints > 0
                ? round($earnedPoints / $totalPoints * 100, 2)
                : 0;
        }

        return $categoryScores;
    }

    /**
     * Determine level achieved based on score
     * 
     * Thresholds:
     * - 0-39: Beginner
     * - 40-59: Elementary
     * - 60-79: Intermediate
     * - 80-100: Advanced
     * 
     * @param float $score
     * @return string
     */
    protected function determineLevelAchieved(float $score): string
    {
        if ($score >= 80) {
            return 'advanced';
        }
        
        if ($score >= 60) {
            return 'intermediate';
        }
        
        if ($score >= 40) {
            return 'elementary';
        }
        
        return 'beginner';
    }

    /**
     * Get test attempt with questions
     * 
     * @param TestAttempt $attempt
     * @return array
     */
    public function getTestData(TestAttempt $attempt): array
    {
        $questions = $this->getQuestionsWithAnswers($attempt);

        return [
            'attempt' => $attempt,
            'questions' => $questions,
            'progress' => $this->getProgressData($attempt),
            'time_remaining' => $this->getTimeRemaining($attempt),
        ];
    }

    /**
     * Get questions with user answers
     * 
     * @param TestAttempt $attempt
     * @return Collection
     */
    protected function getQuestionsWithAnswers(TestAttempt $attempt): Collection
    {
        return $attempt->placementTest
            ->getActiveQuestions()
            ->map(function ($question) use ($attempt) {
                $answer = $this->findUserAnswer($attempt, $question);

                return [
                    'id' => $question->id,
                    'question' => $question->question,
                    'type' => $question->type->value,
                    'options' => $question->options,
                    'image' => $question->image,
                    'time_limit_seconds' => $question->time_limit_seconds,
                    'is_answered' => $answer !== null,
                    'user_answer' => $answer?->user_answer,
                ];
            });
    }

    /**
     * Find user's answer for a question
     * 
     * @param TestAttempt $attempt
     * @param TestQuestion $question
     * @return TestAnswer|null
     */
    protected function findUserAnswer(TestAttempt $attempt, TestQuestion $question): ?TestAnswer
    {
        return TestAnswer::where('test_attempt_id', $attempt->id)
            ->where('test_question_id', $question->id)
            ->first();
    }

    /**
     * Get test progress data
     * 
     * @param TestAttempt $attempt
     * @return array
     */
    protected function getProgressData(TestAttempt $attempt): array
    {
        return [
            'answered' => $attempt->answered_questions,
            'total' => $attempt->total_questions,
            'percentage' => $attempt->getCompletionPercentage(),
        ];
    }

    /**
     * Get remaining time in seconds
     * 
     * @param TestAttempt $attempt
     * @return int|null
     */
    protected function getTimeRemaining(TestAttempt $attempt): ?int
    {
        if (!$attempt->expires_at) {
            return null;
        }

        return max(0, now()->diffInSeconds($attempt->expires_at, false));
    }
}
