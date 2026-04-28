<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\Session;
use App\Models\Activity;
use App\Models\Review;

class TutorSettingsController extends Controller
{
    public function index()
    {
        $tutor = Auth::guard('tutor')->user();
        
        // Get all achievements for tutors
        $achievements = Achievement::where(function($query) {
            $query->where('type', 'tutor')
                  ->orWhere('type', 'both');
        })->where('is_active', true)->get();
        
        // Get user's achievements with progress
        $userAchievements = [];
        $totalPoints = 0;
        $unlockedCount = 0;
        
        foreach ($achievements as $achievement) {
            $userAchievement = UserAchievement::where('achievement_id', $achievement->id)
                ->where('user_type', 'App\Models\Tutor')
                ->where('user_id', $tutor->id)
                ->first();
            
            if (!$userAchievement) {
                // Handle "Welcome!" achievement or achievements with no requirement
                if (!$achievement->requirement_type) {
                    $progress = 100;
                    $isUnlocked = true;
                } else {
                $progress = $this->calculateProgress($tutor, $achievement);
                $isUnlocked = $progress >= 100;
                }
                
                $userAchievement = UserAchievement::create([
                    'achievement_id' => $achievement->id,
                    'user_type' => 'App\Models\Tutor',
                    'user_id' => $tutor->id,
                    'progress' => $progress,
                    'is_unlocked' => $isUnlocked,
                    'unlocked_at' => $isUnlocked ? now() : null,
                ]);
            } else {
                // Update progress (skip for achievements with no requirement)
                if ($achievement->requirement_type) {
                $progress = $this->calculateProgress($tutor, $achievement);
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
        
        // Count completed sessions as "quests"
        $completedQuests = Session::where('tutor_id', $tutor->id)
            ->where('status', 'completed')
            ->count();

        // Calculate level based on total points and completed quests
        $level = 1;
        $maxLevel = 50;
        for ($i = 2; $i <= $maxLevel; $i++) {
            $requiredPoints = ($i - 1) * 100;
            $requiredQuests = ($i - 1) * 2; // 2 quests per level
            
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
        
        return view('tutor.achievements', compact(
            'userAchievements', 'totalPoints', 'unlockedCount', 'level', 
            'pointsForNextLevel', 'questsForNextLevel', 'completedQuests', 
            'nextLevelPointsReq', 'nextLevelQuestsReq', 'tutor'
        ));
    }
    
    private function calculateProgress($tutor, $achievement)
    {
        if (!$achievement->requirement_type || !$achievement->requirement_value) {
            return 0;
        }
        
        $current = 0;
        
        switch ($achievement->requirement_type) {
            case 'sessions_completed':
                $current = Session::where('tutor_id', $tutor->id)
                    ->where('status', 'completed')
                    ->count();
                break;
            case 'sessions_accepted':
                $current = Session::where('tutor_id', $tutor->id)
                    ->where('status', 'accepted')
                    ->count();
                break;
            case 'activities_created':
                $current = Activity::where('tutor_id', $tutor->id)->count();
                break;
            case 'perfect_ratings':
                // Count reviews with 5-star ratings received by tutor
                $current = Review::where('tutor_id', $tutor->id)
                    ->where('rating', 5)
                    ->count();
                break;
            case 'students_taught':
                $current = Session::where('tutor_id', $tutor->id)
                    ->distinct('student_id')
                    ->count('student_id');
                break;
        }
        
        $progress = min(100, ($current / $achievement->requirement_value) * 100);
        return (int) $progress;
    }
}
