<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tutor;
use App\Models\Session;
use App\Models\Wallet;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Message;
use App\Services\AchievementNotificationService;

class StudentSessionController extends Controller
{
    // Show all tutors and booking form
    public function index()
    {
        $student = Auth::guard('student')->user();

        // Only show approved tutors that are active
        $query = Tutor::where('registration_status', 'approved')
            ->where('is_active', true);

        // Don't show tutors that the student already has an accepted booking with
        if ($student) {
            $query->whereDoesntHave('sessions', function ($q) use ($student) {
                $q->where('student_id', $student->id)
                    ->where('status', 'accepted');
            });
        }

        $tutors = $query->with([
            'sessions' => function ($q) {
                $q->whereIn('status', ['accepted', 'pending'])
                    ->where('date', '>=', now()->toDateString());
            }
        ])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->get();

        // Smart match: Prioritize tutors whose specialization matches student's subjects of interest
        if ($student && $student->subjects_interest) {
            $studentInterests = $this->parseSubjects($student->subjects_interest);

            // Add match score to each tutor
            $tutors = $tutors->map(function ($tutor) use ($studentInterests) {
                $tutor->match_score = $this->calculateMatchScore($tutor, $studentInterests);
                $tutor->is_matched = $tutor->match_score > 0;
                $tutor->next_available = $this->calculateNextAvailableSlot($tutor->sessions);
                return $tutor;
            });

            // Sort by match score (matched tutors first), then by rating
            $tutors = $tutors->sortByDesc(function ($tutor) {
                // Primary sort: match score (matched tutors first)
                // Secondary sort: average rating
                $rating = $tutor->reviews_avg_rating ?? 0;
                return [$tutor->match_score, $rating];
            })->values();
        } else {
            // If no subjects of interest, just sort by rating
            $tutors = $tutors->map(function ($tutor) {
                $tutor->match_score = 0;
                $tutor->is_matched = false;
                $tutor->next_available = $this->calculateNextAvailableSlot($tutor->sessions);
                return $tutor;
            })->sortByDesc('reviews_avg_rating')->values();
        }
        $studentLevel = 1;
        $studentDiscount = 0;
        if ($student) {
            $studentLevel = $student->getLevel();
            $studentDiscount = min(50, ($studentLevel - 1) * 2);
        }

        return view('student.book-session', compact('tutors', 'student', 'studentLevel', 'studentDiscount'));
    }

    /**
     * Parse subjects from text (handles comma-separated, newline-separated, etc.)
     */
    private function parseSubjects($subjectsText)
    {
        if (empty($subjectsText)) {
            return [];
        }

        // Split by comma, newline, or semicolon, then trim and filter
        $subjects = preg_split('/[,;\n\r]+/', $subjectsText);
        $subjects = array_map('trim', $subjects);
        $subjects = array_filter($subjects, function ($subject) {
            return !empty($subject);
        });

        // Convert to lowercase for case-insensitive matching
        return array_map('strtolower', $subjects);
    }

    /**
     * Calculate match score between tutor specialization and student interests
     */
    private function calculateMatchScore($tutor, $studentInterests)
    {
        if (empty($tutor->specialization) || empty($studentInterests)) {
            return 0;
        }

        // Parse tutor specialization (can be comma-separated)
        $tutorSpecializations = $this->parseSubjects($tutor->specialization);

        if (empty($tutorSpecializations)) {
            return 0;
        }

        // Count matches (case-insensitive)
        $matches = 0;
        foreach ($studentInterests as $interest) {
            foreach ($tutorSpecializations as $specialization) {
                // Exact match
                if ($interest === $specialization) {
                    $matches++;
                    break;
                }
                // Partial match (contains)
                if (str_contains($specialization, $interest) || str_contains($interest, $specialization)) {
                    $matches += 0.5;
                    break;
                }
            }
        }

        // Return match score (higher is better)
        return $matches;
    }

    /**
     * Calculate the next available 1-hour slot for a tutor based on their sessions.
     */
    private function calculateNextAvailableSlot($sessions, $startFrom = null)
    {
        $now = $startFrom ? $startFrom->copy() : now();
        $startHour = 8; // 8 AM
        $endHour = 20; // 8 PM

        $checkTime = $now->copy();

        if (!$startFrom) {
            if ($checkTime->hour >= $endHour) {
                $checkTime->addDay()->setHour($startHour)->setMinute(0)->setSecond(0);
            } elseif ($checkTime->hour < $startHour) {
                $checkTime->setHour($startHour)->setMinute(0)->setSecond(0);
            } else {
                $checkTime->addHour()->setMinute(0)->setSecond(0);
                if ($checkTime->hour >= $endHour) {
                    $checkTime->addDay()->setHour($startHour)->setMinute(0)->setSecond(0);
                }
            }
        } else {
            if ($checkTime->hour >= $endHour) {
                $checkTime->addDay()->setHour($startHour)->setMinute(0)->setSecond(0);
            } elseif ($checkTime->hour < $startHour) {
                $checkTime->setHour($startHour)->setMinute(0)->setSecond(0);
            } else {
                $checkTime->addHour()->setMinute(0)->setSecond(0);
                if ($checkTime->hour >= $endHour) {
                    $checkTime->addDay()->setHour($startHour)->setMinute(0)->setSecond(0);
                }
            }
        }

        for ($i = 0; $i < 30; $i++) {
            $date = $checkTime->copy()->toDateString();

            while ($checkTime->hour < $endHour) {
                $slotStart = $checkTime->copy();
                $slotEnd = $checkTime->copy()->addHour();

                $conflict = false;
                foreach ($sessions as $session) {
                    $sessionDateStr = $session->date instanceof \Carbon\Carbon ? $session->date->toDateString() : \Carbon\Carbon::parse($session->date)->toDateString();
                    if ($sessionDateStr === $date) {
                        $sessionStart = \Carbon\Carbon::parse($sessionDateStr . ' ' . $session->start_time);
                        $sessionEnd = \Carbon\Carbon::parse($sessionDateStr . ' ' . $session->end_time);

                        if ($slotStart->lt($sessionEnd) && $slotEnd->gt($sessionStart)) {
                            $conflict = true;
                            break;
                        }
                    }
                }

                if (!$conflict) {
                    return [
                        'date' => $slotStart->format('Y-m-d'),
                        'start_time' => $slotStart->format('H:i'),
                        'end_time' => $slotEnd->format('H:i'),
                        'is_today' => $slotStart->isToday(),
                        'is_tomorrow' => $slotStart->isTomorrow(),
                        'formatted_date' => $slotStart->format('M d, Y')
                    ];
                }

                $checkTime->addHour();
            }

            $checkTime->addDay()->setHour($startHour)->setMinute(0)->setSecond(0);
        }

        return null;
    }

    // Handle booking submission
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'tutor_id' => 'required|exists:tutors,id',
                'booking_type' => 'required|in:hourly,monthly',
                'session_type' => 'required|in:face_to_face,online',
                'date' => 'required|date|after_or_equal:today',
                'start_time' => 'required',
                'end_time' => 'required',
                'subject' => 'required|string|max:255',
                'notes' => 'nullable|string|max:500',
            ]);

            $startTimeStr = $request->start_time;
            $endTimeStr = $request->end_time;

            try {
                $startTime = \Carbon\Carbon::parse($startTimeStr);
                $endTime = \Carbon\Carbon::parse($endTimeStr);

                if ($request->booking_type === 'hourly' && $endTime->lt($startTime)) {
                    $endTime->addDay();
                }

                if ($endTime->lte($startTime)) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['end_time' => 'The end time must be after the start time.']);
                }
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'Invalid time format provided.']);
            }





            if ($request->booking_type === 'hourly') {
                $conflict = Session::where('tutor_id', $request->tutor_id)
                    ->whereIn('status', ['accepted', 'pending'])
                    ->where('date', $request->date)
                    ->where(function ($query) use ($request) {
                        $query->where('start_time', '<', $request->end_time)
                            ->where('end_time', '>', $request->start_time);
                    })
                    ->exists();

                if ($conflict) {
                    $tutorSessions = Session::where('tutor_id', $request->tutor_id)
                        ->whereIn('status', ['accepted', 'pending'])
                        ->where('date', '>=', $request->date)
                        ->get();
                    
                    $requestedStart = \Carbon\Carbon::parse($request->date . ' ' . $request->start_time);
                    $nextAvailable = $this->calculateNextAvailableSlot($tutorSessions, $requestedStart);
                    
                    $errorMessage = 'The selected time conflicts with another session.';
                    if ($nextAvailable) {
                        if ($nextAvailable['date'] === $request->date) {
                            $errorMessage .= " However, the tutor is available later today at {$nextAvailable['start_time']} to {$nextAvailable['end_time']}.";
                        } else {
                            $errorMessage .= " The next available slot is on {$nextAvailable['formatted_date']} at {$nextAvailable['start_time']} to {$nextAvailable['end_time']}.";
                        }
                    } else {
                        $errorMessage .= " Please choose a different time.";
                    }

                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['error' => $errorMessage]);
                }
            }

            $tutor = Tutor::findOrFail($request->tutor_id);

            $hourlyRate = null;
            $hours = null;

            if ($request->booking_type === 'monthly') {
                $sessionRate = $tutor->session_rate ?? 0;
            } else {
                $hourlyRate = $tutor->hourly_rate ?? $tutor->session_rate ?? 0;

                // Calculate hours between start and end time

                $totalMinutes = $startTime->diffInMinutes($endTime);
                $hours = $totalMinutes / 60;

                $sessionRate = $hourlyRate * $hours;
            }

            $student = Auth::guard('student')->user();

            if (!$student) {
                return redirect()->route('login.student')->with('error', 'Please log in to book a session.');
            }

            // Apply level-based discount (2% per level starting at level 2, max 50%)
            $level = $student->getLevel();
            $discountPercentage = min(50, ($level - 1) * 2);
            $originalRate = $sessionRate;

            if ($discountPercentage > 0) {
                $discountMultiplier = 1 - ($discountPercentage / 100);
                $sessionRate = $sessionRate * $discountMultiplier;
            }

            $studentId = $student->id;

            // Check if tutor is approved and active
            if ($tutor->registration_status !== 'approved') {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'This tutor is not yet available for booking.']);
            }

            if (!$tutor->is_active) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'This tutor is currently inactive.']);
            }

            // Get or create student's wallet
            $wallet = Wallet::where('user_id', $studentId)
                ->where('user_type', 'student')
                ->first();

            if (!$wallet) {
                $wallet = Wallet::create([
                    'user_id' => $studentId,
                    'user_type' => 'student',
                    'balance' => 0.00,
                    'currency' => 'PHP'
                ]);
            }

            // Check if student has sufficient balance
            if (!$wallet->canAfford($sessionRate)) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'Insufficient balance. Please add funds to your wallet first.']);
            }

            // Deduct payment from wallet
            $transaction = $wallet->deductFunds($sessionRate, 'session_booking', [
                'tutor_id' => $tutor->id,
                'tutor_name' => $tutor->first_name . ' ' . $tutor->last_name,
                'session_type' => $request->session_type,
                'date' => $request->date,
            ]);

            if (!$transaction) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'Failed to process payment. Please try again.']);
            }

            // Create session booking
            $session = Session::create([
                'student_id' => $studentId,
                'tutor_id' => $request->tutor_id,
                'session_type' => $request->session_type,
                'booking_type' => $request->booking_type,
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'subject' => $request->subject,
                'notes' => $request->notes,
                'rate' => $sessionRate,
                'status' => 'pending',
            ]);

            // Notify tutor about the new booking
            $student = Auth::guard('student')->user();
            $sessionDate = \Carbon\Carbon::parse($request->date)->format('F j, Y');
            $startTimeFmt = date('g:i A', strtotime($request->start_time));
            Notification::create([
                'user_id' => $request->tutor_id,
                'user_type' => 'tutor',
                'type' => 'new_booking',
                'title' => 'New Booking Request',
                'message' => $student->first_name . ' ' . $student->last_name .
                    ' has requested a ' . ucfirst(str_replace('_', '-', $request->session_type)) .
                    ' session on ' . $sessionDate . ' at ' . $startTimeFmt . '.',
                'related_id' => $session->id,
            ]);

            // Check achievements for student
            $achievementService = new AchievementNotificationService();
            $achievementService->checkAndNotifyProgress($student, 'student', 'sessions_booked');

            DB::commit();

            if ($request->booking_type === 'monthly') {
                $message = 'Session booking request sent successfully! Payment of ₱' . number_format($sessionRate, 2) . '/month has been deducted from your wallet.';
            } else {
                $message = 'Session booking request sent successfully! Payment of ₱' . number_format($sessionRate, 2) . ' (₱' . number_format($hourlyRate, 2) . '/hour) has been deducted from your wallet.';
            }

            if (isset($discountPercentage) && $discountPercentage > 0) {
                $message .= ' A Level ' . $level . ' discount of ' . $discountPercentage . '% (₱' . number_format($originalRate - $sessionRate, 2) . ') was applied!';
            }

            return redirect()->route('student.book-session')->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Booking Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'user_id' => Auth::guard('student')->id()
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'An error occurred while creating your booking: ' . $e->getMessage()]);
        }
    }

    // Get tutor details for modal
    public function getTutorDetails($id)
    {
        $tutor = Tutor::where('id', $id)
            ->with([
                'sessions' => function ($q) {
                    $q->whereIn('status', ['accepted', 'pending'])
                        ->where('date', '>=', now()->toDateString());
                }
            ])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->firstOrFail();

        // Add calculated ratings that include both session reviews and assignment answer ratings
        $tutor->average_rating = $tutor->getAverageRating();
        $tutor->rating_count = $tutor->getRatingCount();
        $tutor->next_available = $this->calculateNextAvailableSlot($tutor->sessions);

        return response()->json($tutor);
    }

    // Get student's booking history
    public function myBookings()
    {
        $bookings = Session::where('student_id', Auth::guard('student')->id())
            ->with('tutor')
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        return view('student.my-bookings', compact('bookings'));
    }

    public function getUpcomingSessions()
    {
        try {
            $studentId = Auth::guard('student')->id();
            if (!$studentId) {
                return response()->json(['error' => 'Student not authenticated'], 401);
            }

            $sessions = Session::where('student_id', $studentId)
                ->where('status', 'accepted')
                ->where('date', '>=', now()->toDateString())
                ->with('tutor')
                ->orderBy('date', 'asc')
                ->orderBy('start_time', 'asc')
                ->limit(5)
                ->get();

            return response()->json($sessions);

        } catch (\Exception $e) {
            \Log::error('Error in getUpcomingSessions for student: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while loading upcoming sessions.'], 500);
        }
    }

    public function messages()
    {
        return view('student.chat.student-messages');
    }

    // Show student schedule with calendar
    public function schedule(Request $request)
    {
        $student = Auth::guard('student')->user();

        // Get the requested month/year or default to current
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        // Validate month and year ranges
        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }
        if ($year < 2020 || $year > 2030) {
            $year = now()->year;
        }

        // Handle month overflow/underflow
        if ($month > 12) {
            $year += 1;
            $month = 1;
        } elseif ($month < 1) {
            $year -= 1;
            $month = 12;
        }

        // Get all accepted sessions for the student in the requested month
        $sessions = Session::where('student_id', $student->id)
            ->whereIn('status', ['accepted', 'completed'])
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with(['tutor'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        // Group sessions by date
        $sessionsByDate = $sessions->groupBy(function ($session) {
            return $session->date->format('Y-m-d');
        });

        // Get calendar data
        $calendarData = $this->generateCalendarData($year, $month, $sessionsByDate);

        return view('student.schedule.index', compact('student', 'sessions', 'sessionsByDate', 'calendarData', 'year', 'month'));
    }

    // Generate calendar data for the month
    private function generateCalendarData($year, $month, $sessionsByDate)
    {
        $firstDay = now()->setYear((int) $year)->setMonth((int) $month)->startOfMonth();
        $lastDay = $firstDay->copy()->endOfMonth();
        $startOfWeek = $firstDay->copy()->startOfWeek();
        $endOfWeek = $lastDay->copy()->endOfWeek();

        $calendar = [];
        $current = $startOfWeek->copy();

        while ($current->lte($endOfWeek)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $date = $current->copy();
                $dateString = $date->format('Y-m-d');

                $dayData = [
                    'date' => $date,
                    'isCurrentMonth' => $date->month == $month,
                    'isToday' => $date->isToday(),
                    'sessions' => $sessionsByDate->get($dateString, collect()),
                    'sessionCount' => $sessionsByDate->get($dateString, collect())->count(),
                ];

                $week[] = $dayData;
                $current->addDay();
            }
            $calendar[] = $week;
        }

        return $calendar;
    }
}