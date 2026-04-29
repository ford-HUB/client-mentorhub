<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Session; // Added this import
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'student_id',
        'email',
        'password',
        'year_level',
        'course',
        'subjects_interest',
        'phone',
        'profile_picture',
        'verification_code',
        'verification_code_expires_at',
        'is_verified',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id')->where('user_type', 'student');
    }

    public function chatRooms()
    {
        return $this->hasMany(ChatRoom::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id')->where('sender_type', 'student');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function activitySubmissions(): HasMany
    {
        return $this->hasMany(ActivitySubmission::class);
    }

    // Helper methods
    public function getFullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getInitials()
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    public function getAvatar()
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }
        return $this->getInitials();
    }

    public function getLevel()
    {
        // Achievement Points = sum of scores earned on PASSED quests only.
        // A quest is "passed" when score >= activity.passing_score (if set), or score > 0 (fallback).
        $gradedSubmissions = \App\Models\ActivitySubmission::where('student_id', $this->id)
            ->whereIn('status', ['graded', 'submitted'])
            ->with('activity')
            ->get();

        $totalPoints    = 0;
        $completedQuests = 0;

        foreach ($gradedSubmissions as $sub) {
            $activity     = $sub->activity;
            $score        = (int) ($sub->score ?? 0);
            $passingScore = $activity ? $activity->passing_score : null;

            $passed = ($passingScore !== null)
                ? $score >= $passingScore
                : $score > 0;

            if ($passed) {
                $totalPoints += $score;
            }
            // Every graded quest (pass OR fail) counts toward completedQuests
            $completedQuests++;
        }

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

        return $level;
    }

    public function getWithdrawalFeePercent()
    {
        $level = $this->getLevel();
        $reduction = min(8, floor(($level - 1) / 2));
        return max(2, 10 - $reduction);
    }

    public static function generateStudentId(): string
    {
        do {
            // Generate an 8-digit number
            $number = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
            $id = 'STU-' . $number;
        } while (self::where('student_id', $id)->exists());

        return $id;
    }
}
