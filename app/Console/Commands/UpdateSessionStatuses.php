<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Session;
use App\Services\AchievementNotificationService;
use Carbon\Carbon;

class UpdateSessionStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update session statuses based on current time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $achievementService = new AchievementNotificationService();

        // Find sessions that are 'accepted' but whose end_time has passed
        $sessionsToComplete = Session::where('status', 'accepted')
            ->where(function ($query) use ($now) {
                $query->where('date', '<', $now->toDateString())
                    ->orWhere(function ($q) use ($now) {
                        $q->where('date', $now->toDateString())
                          ->where('end_time', '<', $now->toTimeString());
                    });
            })
            ->get();

        $count = 0;
        foreach ($sessionsToComplete as $session) {
            $session->update(['status' => 'completed']);
            
            // Check achievements
            $tutor = $session->tutor;
            $student = $session->student;

            if ($tutor) {
                $achievementService->checkAndNotifyProgress($tutor, 'tutor', 'sessions_completed');
            }
            if ($student) {
                $achievementService->checkAndNotifyProgress($student, 'student', 'sessions_completed');
            }

            $count++;
        }

        $this->info("Updated {$count} sessions to 'completed'.");
        
        // Optional: Handle 'pending' sessions that are now in the past?
        // Usually, if a tutor hasn't accepted a session by the time it should have started, it should be 'expired' or 'cancelled'.
        $expiredSessions = Session::where('status', 'pending')
            ->where(function ($query) use ($now) {
                $query->where('date', '<', $now->toDateString())
                    ->orWhere(function ($q) use ($now) {
                        $q->where('date', $now->toDateString())
                          ->where('start_time', '<', $now->toTimeString());
                    });
            })
            ->get();
            
        $expiredCount = 0;
        foreach ($expiredSessions as $session) {
            // Cancel and refund student
            $session->update(['status' => 'cancelled', 'notes' => 'Session expired - not accepted by tutor in time.']);
            
            // Refund student
            $student = $session->student;
            if ($student) {
                $wallet = $student->wallet; // Assuming relationship exists, or find it manually
                if (!$wallet) {
                    $wallet = \App\Models\Wallet::where('user_id', $student->id)->where('user_type', 'student')->first();
                }
                
                if ($wallet && $session->rate > 0) {
                    $wallet->addFunds($session->rate, 'refund', [
                        'booking_id' => $session->id,
                        'reason' => 'Session expired - not accepted by tutor in time.',
                    ]);
                }
            }
            
            $expiredCount++;
        }
        
        if ($expiredCount > 0) {
            $this->info("Cancelled {$expiredCount} expired pending sessions.");
        }
    }
}
