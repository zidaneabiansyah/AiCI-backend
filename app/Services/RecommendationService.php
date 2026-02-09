<?php

namespace App\Services;

use App\Models\ClassModel;
use App\Models\TestAttempt;
use App\Models\TestResult;
use Illuminate\Support\Collection;

/**
 * Service untuk generate class recommendations
 * 
 * Recommendation Algorithm:
 * 1. Analyze test results (score, level, category performance)
 * 2. Analyze user profile (age, education, experience)
 * 3. Match dengan available classes
 * 4. Calculate match percentage
 * 5. Rank recommendations
 */
class RecommendationService extends BaseService
{
    /**
     * Generate class recommendations based on test results
     * 
     * @param TestAttempt $attempt
     * @return TestResult
     */
    public function generateRecommendations(TestAttempt $attempt): TestResult
    {
        return $this->transaction(function () use ($attempt) {
            // Analyze user profile
            $userProfile = $this->analyzeUserProfile($attempt);

            // Get eligible classes
            $eligibleClasses = $this->getEligibleClasses($attempt);

            // Calculate match for each class
            $recommendations = $this->calculateMatches($attempt, $eligibleClasses, $userProfile);

            // Analyze strengths & weaknesses
            $analysis = $this->analyzePerformance($attempt);

            // Create test result
            $testResult = TestResult::create([
                'test_attempt_id' => $attempt->id,
                'user_id' => $attempt->user_id,
                'overall_score' => $attempt->score,
                'level_achieved' => $attempt->level_result,
                'category_scores' => $this->getCategoryScores($attempt),
                'strengths' => $analysis['strengths'],
                'weaknesses' => $analysis['weaknesses'],
                'recommended_classes' => $recommendations,
                'recommendation_reasons' => $this->generateReasons($recommendations),
                'performance_summary' => $this->generateSummary($attempt, $analysis),
                'next_steps' => $this->generateNextSteps($attempt, $recommendations),
            ]);

            $this->log('Recommendations generated', [
                'attempt_id' => $attempt->id,
                'result_id' => $testResult->id,
                'recommendations_count' => count($recommendations),
            ]);

            return $testResult;
        });
    }

    /**
     * Analyze user profile from test attempt
     * 
     * Extract relevant info:
     * - Age & education level
     * - Previous experience
     * - Interests
     * 
     * @param TestAttempt $attempt
     * @return array
     */
    protected function analyzeUserProfile(TestAttempt $attempt): array
    {
        return [
            'age' => $attempt->age,
            'education_level' => $attempt->education_level,
            'has_ai_experience' => $attempt->experience['ai'] ?? false,
            'has_robotics_experience' => $attempt->experience['robotics'] ?? false,
            'has_programming_experience' => $attempt->experience['programming'] ?? false,
            'interests' => $attempt->interests ?? [],
        ];
    }

    /**
     * Get classes that match user's criteria
     * 
     * Filter by:
     * - Age range
     * - Education level
     * - Minimum score requirement
     * - Active status
     * - Available capacity
     * 
     * @param TestAttempt $attempt
     * @return Collection
     */
    protected function getEligibleClasses(TestAttempt $attempt): Collection
    {
        return ClassModel::with('program')
            ->active()
            ->where(function ($query) use ($attempt) {
                // Age range filter
                $query->where(function ($q) use ($attempt) {
                    $q->where('min_age', '<=', $attempt->age)
                      ->where('max_age', '>=', $attempt->age);
                })
                ->orWhereNull('min_age'); // Classes without age restriction
            })
            ->where('min_score', '<=', $attempt->score) // Score requirement
            ->where(function ($query) use ($attempt) {
                // Education level match
                $query->whereHas('program', function ($q) use ($attempt) {
                    $q->where('education_level', $attempt->education_level);
                });
            })
            ->available() // Has capacity
            ->get();
    }

    /**
     * Calculate match percentage for each class
     * 
     * Matching factors (weighted):
     * 1. Score match (40%) - How well score matches class level
     * 2. Experience match (30%) - Previous experience alignment
     * 3. Interest match (20%) - Interest alignment
     * 4. Age appropriateness (10%) - Age range fit
     * 
     * @param TestAttempt $attempt
     * @param Collection $classes
     * @param array $userProfile
     * @return array
     */
    protected function calculateMatches(
        TestAttempt $attempt,
        Collection $classes,
        array $userProfile
    ): array {
        $recommendations = [];

        foreach ($classes as $class) {
            $matchScore = 0;

            // 1. Score match (40%)
            $scoreMatch = $this->calculateScoreMatch($attempt->score, $class->level);
            $matchScore += $scoreMatch * 0.4;

            // 2. Experience match (30%)
            $experienceMatch = $this->calculateExperienceMatch($userProfile, $class);
            $matchScore += $experienceMatch * 0.3;

            // 3. Interest match (20%)
            $interestMatch = $this->calculateInterestMatch($userProfile['interests'], $class);
            $matchScore += $interestMatch * 0.2;

            // 4. Age appropriateness (10%)
            $ageMatch = $this->calculateAgeMatch($userProfile['age'], $class);
            $matchScore += $ageMatch * 0.1;

            $recommendations[] = [
                'class_id' => $class->id,
                'class_name' => $class->name,
                'program_name' => $class->program->name,
                'match_percentage' => round($matchScore, 2),
                'price' => $class->price,
                'duration_hours' => $class->duration_hours,
                'level' => $class->level,
                'reasons' => $this->generateMatchReasons($scoreMatch, $experienceMatch, $interestMatch, $ageMatch),
            ];
        }

        // Sort by match percentage (descending)
        usort($recommendations, function ($a, $b) {
            return $b['match_percentage'] <=> $a['match_percentage'];
        });

        // Return top 3 recommendations
        return array_slice($recommendations, 0, 3);
    }

    /**
     * Calculate score match percentage
     * 
     * Compare user score dengan level requirement:
     * - Beginner: 0-39 optimal
     * - Elementary: 40-59 optimal
     * - Intermediate: 60-79 optimal
     * - Advanced: 80-100 optimal
     * 
     * @param float $userScore
     * @param string $classLevel
     * @return float (0-100)
     */
    protected function calculateScoreMatch(float $userScore, string $classLevel): float
    {
        $optimalRanges = [
            'beginner' => [0, 39],
            'elementary' => [40, 59],
            'intermediate' => [60, 79],
            'advanced' => [80, 100],
        ];

        $range = $optimalRanges[$classLevel] ?? [0, 100];
        [$min, $max] = $range;

        // Perfect match if score is in optimal range
        if ($userScore >= $min && $userScore <= $max) {
            return 100;
        }

        // Calculate distance from optimal range
        if ($userScore < $min) {
            $distance = $min - $userScore;
        } else {
            $distance = $userScore - $max;
        }

        // Reduce match by distance (max 50% reduction)
        return max(50, 100 - ($distance * 2));
    }

    /**
     * Calculate experience match
     * 
     * Check if user's experience aligns with class prerequisites
     * 
     * @param array $userProfile
     * @param ClassModel $class
     * @return float (0-100)
     */
    protected function calculateExperienceMatch(array $userProfile, ClassModel $class): float
    {
        $matchScore = 100;

        // Beginner classes prefer no experience
        if ($class->level === 'beginner') {
            $hasAnyExperience = $userProfile['has_ai_experience'] 
                || $userProfile['has_robotics_experience'] 
                || $userProfile['has_programming_experience'];
            
            return $hasAnyExperience ? 70 : 100;
        }

        // Advanced classes prefer experience
        if ($class->level === 'advanced') {
            $experienceCount = 0;
            if ($userProfile['has_ai_experience']) {
                $experienceCount++;
            }
            if ($userProfile['has_robotics_experience']) {
                $experienceCount++;
            }
            if ($userProfile['has_programming_experience']) {
                $experienceCount++;
            }

            return min(100, 50 + ($experienceCount * 16.67)); // 50% base + up to 50% from experience
        }

        return $matchScore;
    }

    /**
     * Calculate interest match
     * 
     * @param array $userInterests
     * @param ClassModel $class
     * @return float (0-100)
     */
    protected function calculateInterestMatch(array $userInterests, ClassModel $class): float
    {
        if (count($userInterests) === 0) {
            return 50; // Neutral if no interests specified
        }

        // Keywords untuk matching
        $classKeywords = [
            'ai', 'artificial intelligence', 'robot', 'robotics', 
            'programming', 'coding', 'technology', 'smart'
        ];

        $matchCount = 0;
        foreach ($userInterests as $interest) {
            $interest = strtolower($interest);
            foreach ($classKeywords as $keyword) {
                if (str_contains($interest, $keyword)) {
                    $matchCount++;
                    break;
                }
            }
        }

        return min(100, 50 + ($matchCount * 25)); // 50% base + bonus for matches
    }

    /**
     * Calculate age appropriateness
     * 
     * @param int $userAge
     * @param ClassModel $class
     * @return float (0-100)
     */
    protected function calculateAgeMatch(int $userAge, ClassModel $class): float
    {
        if (!$class->min_age || !$class->max_age) {
            return 100; // No age restriction
        }

        // Perfect match if in range
        if ($userAge >= $class->min_age && $userAge <= $class->max_age) {
            return 100;
        }

        // Calculate distance from range
        if ($userAge < $class->min_age) {
            $distance = $class->min_age - $userAge;
        } else {
            $distance = $userAge - $class->max_age;
        }

        // Reduce match by distance
        return max(0, 100 - ($distance * 10));
    }

    /**
     * Generate match reasons
     * 
     * @param float $scoreMatch
     * @param float $experienceMatch
     * @param float $interestMatch
     * @param float $ageMatch
     * @return array
     */
    protected function generateMatchReasons(
        float $scoreMatch,
        float $experienceMatch,
        float $interestMatch,
        float $ageMatch
    ): array {
        $reasons = [];

        if ($scoreMatch >= 90) {
            $reasons[] = 'Skor test Anda sangat sesuai dengan level kelas ini';
        }
        if ($experienceMatch >= 80) {
            $reasons[] = 'Pengalaman Anda cocok dengan materi kelas';
        }
        if ($interestMatch >= 70) {
            $reasons[] = 'Sesuai dengan minat Anda';
        }
        if ($ageMatch >= 90) {
            $reasons[] = 'Usia Anda ideal untuk kelas ini';
        }

        return $reasons;
    }

    /**
     * Analyze performance to identify strengths & weaknesses
     * 
     * @param TestAttempt $attempt
     * @return array
     */
    protected function analyzePerformance(TestAttempt $attempt): array
    {
        $categoryScores = $this->getCategoryScores($attempt);
        
        $strengths = [];
        $weaknesses = [];

        foreach ($categoryScores as $category => $score) {
            if ($score >= 70) {
                $strengths[] = $this->getCategoryLabel($category);
            } elseif ($score < 50) {
                $weaknesses[] = $this->getCategoryLabel($category);
            }
        }

        return [
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
        ];
    }

    /**
     * Get category scores from test answers
     * 
     * @param TestAttempt $attempt
     * @return array
     */
    protected function getCategoryScores(TestAttempt $attempt): array
    {
        $answers = $attempt->answers()->with('testQuestion')->get();
        
        $categoryScores = [];
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
     * Get human-readable category label
     * 
     * @param string $category
     * @return string
     */
    protected function getCategoryLabel(string $category): string
    {
        return match($category) {
            'logical_thinking' => 'Logical Thinking',
            'basic_programming' => 'Basic Programming',
            'ai_awareness' => 'AI Awareness',
            'robotics_fundamentals' => 'Robotics Fundamentals',
            'interest_mapping' => 'Interest Mapping',
            default => ucwords(str_replace('_', ' ', $category)),
        };
    }

    /**
     * Generate recommendation reasons
     * 
     * @param array $recommendations
     * @return array
     */
    protected function generateReasons(array $recommendations): array
    {
        $reasons = [];
        foreach ($recommendations as $rec) {
            $reasons[$rec['class_id']] = $rec['reasons'];
        }
        return $reasons;
    }

    /**
     * Generate performance summary
     * 
     * @param TestAttempt $attempt
     * @param array $analysis
     * @return string
     */
    protected function generateSummary(TestAttempt $attempt, array $analysis): string
    {
        $level = match($attempt->level_result) {
            'beginner' => 'Pemula',
            'elementary' => 'Dasar',
            'intermediate' => 'Menengah',
            'advanced' => 'Lanjutan',
        };

        $summary = "Anda berada di level {$level} dengan skor {$attempt->score}. ";

        if (isset($analysis['strengths']) && count($analysis['strengths']) > 0) {
            $summary .= "Kekuatan Anda: " . implode(', ', $analysis['strengths']) . ". ";
        }

        if (isset($analysis['weaknesses']) && count($analysis['weaknesses']) > 0) {
            $summary .= "Area yang perlu ditingkatkan: " . implode(', ', $analysis['weaknesses']) . ".";
        }

        return $summary;
    }

    /**
     * Generate next steps recommendations
     * 
     * @param TestAttempt $attempt
     * @param array $recommendations
     * @return array
     */
    protected function generateNextSteps(TestAttempt $attempt, array $recommendations): array
    {
        $steps = [];

        if (count($recommendations) > 0) {
            $steps[] = "Pilih salah satu kelas yang direkomendasikan di bawah ini";
            $steps[] = "Daftar dan lakukan pembayaran";
            $steps[] = "Mulai perjalanan belajar AI & Robotics Anda!";
        } else {
            $steps[] = "Hubungi admin untuk konsultasi kelas yang sesuai";
            $steps[] = "Tingkatkan kemampuan dasar Anda terlebih dahulu";
        }

        return $steps;
    }
}
