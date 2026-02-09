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
use Illuminate\Support\Collection;
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
            $this->validateTestAvailability($placementTest);
            $this->validateRetakeEligibility($placementTest, $user);
            
            $questions = $this->getValidatedQuestions($placementTest);
            $expiresAt = now()->addMinutes($placementTest->duration_minutes);
            
            $attempt = $this->createTestAttemptRecord(
                $placementTest,
                $user,
                $preAssessmentData,
                $questions,
                $expiresAt
            );

            $this->logAttemptCreation($attempt, $user, $placementTest);

            return $attempt;
        });
    }

    /**
     * Validate test is available
     * 
     * @param PlacementTest $placementTest
     * @throws Exception
     */
    protected function validateTestAvailability(PlacementTest $placementTest): void
    {
        if (!$placementTest->is_active) {
            throw new Exception('Placement test tidak tersedia saat ini.');
        }
    }

    /**
     * Validate user can retake test
     * 
     * @param PlacementTest $placementTest
     * @param User $user
     * @throws Exception
     */
    protected function validateRetakeEligibility(PlacementTest $placementTest, User $user): void
    {
        if (!$placementTest->allow_retake) {
            $this->checkNoExistingAttempt($placementTest, $user);
        } else {
            $this->checkCooldownPeriod($placementTest, $user);
        }
    }

    /**
     * Check user has no existing completed attempt
     * 
     * @param PlacementTest $placementTest
     * @param User $user
     * @throws Exception
     */
    protected function checkNoExistingAttempt(PlacementTest $placementTest, User $user): void
    {
        $existingAttempt = TestAttempt::where('user_id', $user->id)
            ->where('placement_test_id', $placementTest->id)
            ->where('status', TestStatus::COMPLETED->value)
            ->exists();

        if ($existingAttempt) {
            throw new Exception('Anda sudah pernah mengikuti test ini.');
        }
    }

    /**
     * Check cooldown period for retake
     * 
     * @param PlacementTest $placementTest
     * @param User $user
     * @throws Exception
     */
    protected function checkCooldownPeriod(PlacementTest $placementTest, User $user): void
    {
        $lastAttempt = TestAttempt::where('user_id', $user->id)
            ->where('placement_test_id', $placementTest->id)
            ->where('status', TestStatus::COMPLETED->value)
            ->latest('completed_at')
            ->first();

        if (!$lastAttempt) {
            return;
        }

        $cooldownUntil = $lastAttempt->completed_at->addDays($placementTest->retake_cooldown_days);

        if (now()->isBefore($cooldownUntil)) {
            $daysLeft = now()->diffInDays($cooldownUntil, false);
            throw new Exception("Anda dapat mengulang test dalam {$daysLeft} hari lagi.");
        }
    }

    /**
     * Get and validate questions
     * 
     * @param PlacementTest $placementTest
     * @return Collection
     * @throws Exception
     */
    protected function getValidatedQuestions(PlacementTest $placementTest): Collection
    {
        $questions = $placementTest->getActiveQuestions();
        
        if ($questions->isEmpty()) {
            throw new Exception('Tidak ada soal tersedia untuk test ini.');
        }

        return $questions;
    }

    /**
     * Create test attempt record
     * 
     * @param PlacementTest $placementTest
     * @param User $user
     * @param array $preAssessmentData
     * @param Collection $questions
     * @param Carbon $expiresAt
     * @return TestAttempt
     */
    protected function createTestAttemptRecord(
        PlacementTest $placementTest,
        User $user,
        array $preAssessmentData,
        Collection $questions,
        Carbon $expiresAt
    ): TestAttempt {
        return TestAttempt::create([
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
    }

    /**
     * Log attempt creation
     * 
     * @param TestAttempt $attempt
     * @param User $user
     * @param PlacementTest $placementTest
     */
    protected function logAttemptCreation(
        TestAttempt $attempt,
        User $user,
        PlacementTest $placementTest
    ): void {
        $this->log('Test attempt created', [
            'attempt_id' => $attempt->id,
            'user_id' => $user->id,
            'test_id' => $placementTest->id,
        ]);
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
            $this->validateAnswerSubmission($attempt, $question);
            
            $isCorrect = $this->checkAnswer($question, $userAnswer);
            $pointsEarned = $this->calculatePointsEarned($question, $isCorrect);
            
            $answer = $this->createAnswerRecord($attempt, $question, $userAnswer, $isCorrect, $pointsEarned, $timeSpentSeconds);
            $this->updateAttemptStatistics($attempt, $isCorrect, $timeSpentSeconds);
            $this->logAnswerSaved($attempt, $question, $isCorrect, $pointsEarned);

            return $answer;
        });
    }

    /**
     * Validate answer submission
     * 
     * @param TestAttempt $attempt
     * @param TestQuestion $question
     * @throws Exception
     */
    protected function validateAnswerSubmission(TestAttempt $attempt, TestQuestion $question): void
    {
        if ($attempt->status !== TestStatus::IN_PROGRESS) {
            throw new Exception('Test sudah selesai atau expired.');
        }

        if ($attempt->isExpired()) {
            $attempt->updateStatus(TestStatus::EXPIRED);
            throw new Exception('Waktu test telah habis.');
        }

        if ($question->placement_test_id !== $attempt->placement_test_id) {
            throw new Exception('Soal tidak valid untuk test ini.');
        }

        $this->checkNotAlreadyAnswered($attempt, $question);
    }

    /**
     * Check question not already answered
     * 
     * @param TestAttempt $attempt
     * @param TestQuestion $question
     * @throws Exception
     */
    protected function checkNotAlreadyAnswered(TestAttempt $attempt, TestQuestion $question): void
    {
        $existingAnswer = TestAnswer::where('test_attempt_id', $attempt->id)
            ->where('test_question_id', $question->id)
            ->first();

        if ($existingAnswer) {
            throw new Exception('Soal ini sudah dijawab.');
        }
    }

    /**
     * Calculate points earned for answer
     * 
     * @param TestQuestion $question
     * @param bool $isCorrect
     * @return float
     */
    protected function calculatePointsEarned(TestQuestion $question, bool $isCorrect): float
    {
        return $isCorrect ? $question->points * $question->difficulty->weight() : 0;
    }

    /**
     * Create answer record
     * 
     * @param TestAttempt $attempt
     * @param TestQuestion $question
     * @param string $userAnswer
     * @param bool $isCorrect
     * @param float $pointsEarned
     * @param int $timeSpentSeconds
     * @return TestAnswer
     */
    protected function createAnswerRecord(
        TestAttempt $attempt,
        TestQuestion $question,
        string $userAnswer,
        bool $isCorrect,
        float $pointsEarned,
        int $timeSpentSeconds
    ): TestAnswer {
        return TestAnswer::create([
            'test_attempt_id' => $attempt->id,
            'test_question_id' => $question->id,
            'user_answer' => $userAnswer,
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
            'time_spent_seconds' => $timeSpentSeconds,
        ]);
    }

    /**
     * Update attempt statistics
     * 
     * @param TestAttempt $attempt
     * @param bool $isCorrect
     * @param int $timeSpentSeconds
     */
    protected function updateAttemptStatistics(TestAttempt $attempt, bool $isCorrect, int $timeSpentSeconds): void
    {
        $attempt->increment('answered_questions');
        
        if ($isCorrect) {
            $attempt->increment('correct_answers');
        }
        
        $attempt->increment('time_spent_seconds', $timeSpentSeconds);
    }

    /**
     * Log answer saved
     * 
     * @param TestAttempt $attempt
     * @param TestQuestion $question
     * @param bool $isCorrect
     * @param float $pointsEarned
     */
    protected function logAnswerSaved(
        TestAttempt $attempt,
        TestQuestion $question,
        bool $isCorrect,
        float $pointsEarned
    ): void {
        $this->log('Answer saved', [
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'is_correct' => $isCorrect,
            'points' => $pointsEarned,
        ]);
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
            return $this->getEmptyScoreResult();
        }

        $totalPossiblePoints = $this->calculateTotalPossiblePoints($answers);
        $totalEarnedPoints = $answers->sum('points_earned');
        $overallScore = $this->calculateOverallScore($totalEarnedPoints, $totalPossiblePoints);

        return [
            'overall_score' => round($overallScore, 2),
            'level_achieved' => $this->determineLevelAchieved($overallScore),
            'category_scores' => $this->calculateCategoryScores($answers),
        ];
    }

    /**
     * Get empty score result
     * 
     * @return array
     */
    protected function getEmptyScoreResult(): array
    {
        return [
            'overall_score' => 0,
            'level_achieved' => 'beginner',
            'category_scores' => [],
        ];
    }

    /**
     * Calculate total possible points with difficulty weight
     * 
     * @param Collection $answers
     * @return float
     */
    protected function calculateTotalPossiblePoints(Collection $answers): float
    {
        return $answers->sum(function ($answer) {
            return $answer->testQuestion->points * $answer->testQuestion->difficulty->weight();
        });
    }

    /**
     * Calculate overall score percentage
     * 
     * @param float $earnedPoints
     * @param float $possiblePoints
     * @return float
     */
    protected function calculateOverallScore(float $earnedPoints, float $possiblePoints): float
    {
        return $possiblePoints > 0 ? $earnedPoints / $possiblePoints * 100 : 0;
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
