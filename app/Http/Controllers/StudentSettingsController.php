<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\Session;
use App\Models\ActivitySubmission;
use App\Models\Review;

class StudentSettingsController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();

        // Get all achievements for students
        $achievements = Achievement::where(function ($query) {
            $query->where('type', 'student')
                ->orWhere('type', 'both');
        })->where('is_active', true)->get();

        // Get user's achievements with progress
        $userAchievements = [];
        $totalPoints = 0;
        $unlockedCount = 0;

        foreach ($achievements as $achievement) {
            $userAchievement = UserAchievement::where('achievement_id', $achievement->id)
                ->where('user_type', 'App\Models\Student')
                ->where('user_id', $student->id)
                ->first();

            if (!$userAchievement) {
                // Handle "Welcome!" achievement or achievements with no requirement
                if (!$achievement->requirement_type) {
                    $progress = 100;
                    $isUnlocked = true;
                } else {
                    $progress = $this->calculateProgress($student, $achievement);
                    $isUnlocked = $progress >= 100;
                }

                $userAchievement = UserAchievement::create([
                    'achievement_id' => $achievement->id,
                    'user_type' => 'App\Models\Student',
                    'user_id' => $student->id,
                    'progress' => $progress,
                    'is_unlocked' => $isUnlocked,
                    'unlocked_at' => $isUnlocked ? now() : null,
                ]);
            } else {
                // Update progress (skip for achievements with no requirement)
                if ($achievement->requirement_type) {
                    $progress = $this->calculateProgress($student, $achievement);
                    $userAchievement->progress = $progress;

                    // Check if achievement should be unlocked
                    if (!$userAchievement->is_unlocked && $progress >= 100) {
                        $userAchievement->is_unlocked = true;
                        $userAchievement->unlocked_at = now();
                    }
                    $userAchievement->save();
                }
            }

            if ($userAchievement->is_unlocked) {
                $totalPoints += $achievement->points;
                $unlockedCount++;
            }

            $userAchievements[] = [
                'achievement' => $achievement,
                'user_achievement' => $userAchievement,
            ];
        }

        // Count completed quests (activities submitted)
        $completedQuests = ActivitySubmission::where('student_id', $student->id)
            ->where('status', 'submitted')
            ->count();

        // Calculate level based on total points and completed quests
        $level = 1;
        $maxLevel = 50;
        for ($i = 2; $i <= $maxLevel; $i++) {
            $requiredPoints = ($i - 1) * 100;
            $requiredQuests = ($i - 1) * 2;

            if ($totalPoints >= $requiredPoints && $completedQuests >= $requiredQuests) {
                $level = $i;
            } else {
                break;
            }
        }

        $nextLevelPointsReq = $level * 100;
        $nextLevelQuestsReq = $level * 2;

        $pointsForNextLevel = max(0, $nextLevelPointsReq - $totalPoints);
        $questsForNextLevel = max(0, $nextLevelQuestsReq - $completedQuests);

        $bookingDiscount = min(50, ($level - 1) * 2);
        $withdrawalFeeReduction = min(8, floor(($level - 1) / 2));
        $currentWithdrawalFee = max(2, 10 - $withdrawalFeeReduction);

        $nextLevel = $level + 1;
        $nextBookingDiscount = min(50, ($nextLevel - 1) * 2);
        $nextWithdrawalFeeReduction = min(8, floor(($nextLevel - 1) / 2));
        $nextWithdrawalFee = max(2, 10 - $nextWithdrawalFeeReduction);

        $levelMilestones = [];
        for ($i = 1; $i <= min($level + 5, 50); $i++) {
            $lvlDiscount = min(50, ($i - 1) * 2);
            $lvlFeeReduction = min(8, floor(($i - 1) / 2));
            $lvlFee = max(2, 10 - $lvlFeeReduction);
            $lvlPointsReq = ($i - 1) * 100;
            $lvlQuestsReq = ($i - 1) * 2;
            $levelMilestones[] = [
                'level' => $i,
                'points_required' => $lvlPointsReq,
                'quests_required' => $lvlQuestsReq,
                'booking_discount' => $lvlDiscount,
                'withdrawal_fee' => $lvlFee,
                'is_current' => $i == $level,
                'is_unlocked' => $i <= $level,
            ];
        }

        return view('student.achievements', compact(
            'userAchievements',
            'totalPoints',
            'unlockedCount',
            'level',
            'pointsForNextLevel',
            'questsForNextLevel',
            'completedQuests',
            'nextLevelPointsReq',
            'nextLevelQuestsReq',
            'student',
            'bookingDiscount',
            'currentWithdrawalFee',
            'withdrawalFeeReduction',
            'nextBookingDiscount',
            'nextWithdrawalFee',
            'levelMilestones'
        ));
    }

    private function calculateProgress($student, $achievement)
    {
        if (!$achievement->requirement_type || !$achievement->requirement_value) {
            return 0;
        }

        $current = 0;

        switch ($achievement->requirement_type) {
            case 'sessions_completed':
                $current = Session::where('student_id', $student->id)
                    ->where('status', 'completed')
                    ->count();
                break;
            case 'sessions_booked':
                $current = Session::where('student_id', $student->id)->count();
                break;
            case 'activities_submitted':
                $current = ActivitySubmission::where('student_id', $student->id)
                    ->where('status', 'submitted')
                    ->count();
                break;
            case 'perfect_ratings':
                // Count reviews with 5-star ratings given by students
                $current = Review::where('student_id', $student->id)
                    ->where('rating', 5)
                    ->count();
                break;
        }

        $progress = min(100, ($current / $achievement->requirement_value) * 100);
        return (int) $progress;
    }
}
