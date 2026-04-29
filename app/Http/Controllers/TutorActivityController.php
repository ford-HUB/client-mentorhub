<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use App\Models\Activity;
    use App\Models\ActivitySubmission;
    use App\Models\Student;
    use App\Models\Session;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;

    class TutorActivityController extends Controller
    {
        // Show all activities for the tutor
        public function index()
        {
            $tutor = Auth::guard('tutor')->user();
            
            $activities = Activity::where('tutor_id', $tutor->id)
                ->with(['student', 'session'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Get students who have sessions with this tutor
            $students = Student::whereHas('sessions', function($query) use ($tutor) {
                $query->where('tutor_id', $tutor->id);
            })->get();

            return view('tutor.my-sessions', compact('activities', 'students', 'tutor'));
        }

        // Show create activity form
        public function create(Request $request)
        {
            $tutor = Auth::guard('tutor')->user();
            
            $students = Student::whereHas('sessions', function($query) use ($tutor) {
                $query->where('tutor_id', $tutor->id);
            })->get();

            // Get selected student ID from query parameter
            $selectedStudentId = $request->query('student_id');
            
            // If student_id is provided, verify the student has sessions with this tutor
            if ($selectedStudentId) {
                $hasSessions = Session::where('tutor_id', $tutor->id)
                    ->where('student_id', $selectedStudentId)
                    ->exists();
                
                if (!$hasSessions) {
                    $selectedStudentId = null; // Reset if invalid
                }
            }

            $sessions = Session::where('tutor_id', $tutor->id)
                ->where('status', 'accepted')
                ->with('student')
                ->get();

            return view('tutor.activities.create', compact('students', 'sessions', 'tutor', 'selectedStudentId'));
        }

        // Store new activity
        public function store(Request $request)
        {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:activity,exam,assignment,quiz',
            'student_id' => 'required|exists:students,id',
            'session_id' => 'nullable|exists:sessions,id',
            'instructions' => 'nullable|string',
            'due_date' => 'nullable|date|after:now',
            'total_points' => 'required|integer|min:1',
            'passing_score' => 'nullable|integer|min:0',
            'time_limit' => 'nullable|integer|min:1',
            'questions' => 'nullable'
        ]);

        $tutor = Auth::guard('tutor')->user();

        // Handle questions - decode if it's a JSON string
        $questions = $request->questions;
        if (is_string($questions)) {
            $questions = json_decode($questions, true);
        }
        
        // Validate questions structure if provided
        if ($questions && is_array($questions)) {
            foreach ($questions as $question) {
                if (!isset($question['question']) || empty($question['question'])) {
                    return redirect()->back()
                        ->withErrors(['questions' => 'All questions must have question text.'])
                        ->withInput();
                }
                if (!isset($question['options']) || !is_array($question['options']) || count($question['options']) < 2) {
                    return redirect()->back()
                        ->withErrors(['questions' => 'Each question must have at least 2 options.'])
                        ->withInput();
                }
                if (!isset($question['type']) || $question['type'] !== 'multiple_choice') {
                    return redirect()->back()
                        ->withErrors(['questions' => 'All questions must be multiple choice type.'])
                        ->withInput();
                }
            }
        }

        $activity = Activity::create([
            'tutor_id' => $tutor->id,
            'student_id' => $request->student_id,
            'session_id' => $request->session_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'status' => 'sent',
            'instructions' => $request->instructions,
            'questions' => $questions,
            'attachments' => null, // No longer using attachments
            'due_date' => $request->due_date,
            'total_points' => $request->total_points,
            'passing_score' => $request->passing_score ?: null,
            'time_limit' => $request->time_limit,
        ]);

        // Create notification for student
        $student = \App\Models\Student::find($request->student_id);
        if ($student) {
            \App\Models\Notification::create([
                'user_id' => $student->id,
                'user_type' => 'student',
                'type' => 'activity_posted',
                'title' => 'New Activity Assigned',
                'message' => $tutor->first_name . ' ' . $tutor->last_name . ' has assigned you a new activity: "' . $activity->title . '".' . ($activity->due_date ? ' Due date: ' . $activity->due_date->format('M d, Y') : ''),
            ]);
        }

        // Check achievements for tutor
        $achievementService = new \App\Services\AchievementNotificationService();
        $achievementService->checkAndNotifyProgress($tutor, 'tutor', 'activities_created');

        // Update activity creation streak
        $streakService = new \App\Services\StreakService();
        $streakService->checkActivityCreationStreak($tutor, 'tutor');

        return redirect()->route('tutor.my-sessions')->with('success', 'Activity sent successfully!');
    }

    // Show activity details
    public function show(Activity $activity)
    {
        $tutor = Auth::guard('tutor')->user();
        
        // Ensure the tutor owns this activity
        if ($activity->tutor_id !== $tutor->id) {
            abort(403);
        }

        $activity->load(['student', 'session']);
        
        // Get the student submission
        $submission = $activity->studentSubmission($activity->student_id);
        
        // Get adaptive learning analysis if activity is graded
        $learningAnalysis = null;
        $suggestedDifficulty = null;
        $suggestedFrequency = null;
        $wrongAnswerSuggestions = [];
        
        if ($submission && $submission->status === 'graded') {
            $adaptiveLearningService = new \App\Services\AdaptiveLearningService();
            $learningAnalysis = $adaptiveLearningService->analyzeStudentPerformance($activity->student_id, $tutor->id);
            $suggestedDifficulty = $adaptiveLearningService->getSuggestedDifficulty($activity->student_id, $tutor->id);
            $suggestedFrequency = $adaptiveLearningService->getSuggestedFrequency($activity->student_id, $tutor->id);

            // Generate AI suggestions for wrong answers
            $geminiService = new \App\Services\GeminiService();
            $wrongAnswerSuggestions = [];
            if ($activity->questions && is_array($activity->questions)) {
                foreach ($activity->questions as $index => $question) {
                    $studentAnswerIndex = isset($submission->answers[$index]) ? (int)$submission->answers[$index] : null;
                    $correctAnswerIndex = isset($question['correct_answer']) ? (int)$question['correct_answer'] : null;

                    if ($studentAnswerIndex !== null && $correctAnswerIndex !== null && $studentAnswerIndex !== $correctAnswerIndex) {
                        $questionText = $question['question'] ?? '';
                        $wrongText = $question['options'][$studentAnswerIndex] ?? 'N/A';
                        $correctText = $question['options'][$correctAnswerIndex] ?? 'N/A';
                        $wrongAnswerSuggestions[$index] = $geminiService->generateWrongAnswerSuggestion($questionText, $wrongText, $correctText);
                    }
                }
            }
        }

        return view('tutor.activities.show', compact('activity', 'tutor', 'submission', 'learningAnalysis', 'suggestedDifficulty', 'suggestedFrequency', 'wrongAnswerSuggestions'));
    }

        // Grade an activity
        public function grade(Request $request, Activity $activity)
        {
            $tutor = Auth::guard('tutor')->user();
            
            // Ensure the tutor owns this activity
            if ($activity->tutor_id !== $tutor->id) {
                abort(403);
            }

            $request->validate([
                'score' => 'required|integer|min:0|max:' . $activity->total_points,
                'feedback' => 'nullable|string|max:1000'
            ]);

            // Get the submission
            $submission = $activity->submissions()->where('student_id', $activity->student_id)->first();
            
            if (!$submission) {
                return redirect()->route('tutor.activities.show', $activity)
                    ->with('error', 'No submitted activity found to grade.');
            }

            // Update the submission with grade and feedback
            $submission->update([
                'score' => $request->score,
                'feedback' => $request->feedback,
                'status' => 'graded',
                'graded_at' => now()
            ]);

            // Also update activity status
            $activity->update([
                'status' => 'graded',
                'graded_at' => now()
            ]);

            // Create notification for student
            \App\Models\Notification::create([
                'user_id' => $activity->student_id,
                'user_type' => 'student',
                'type' => 'activity_graded',
                'title' => 'Activity Graded',
                'message' => 'Your activity "' . $activity->title . '" has been graded. Score: ' . $request->score . '/' . $activity->total_points,
            ]);

            // If AJAX request, return JSON response
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Activity graded successfully! The student has been notified.',
                    'redirect' => route('tutor.my-sessions')
                ]);
            }

            return redirect()->route('tutor.my-sessions')
                ->with('success', 'Activity graded successfully! The student has been notified.');
        }

        // Get progress statistics
        public function getProgressStats()
        {
            $tutor = Auth::guard('tutor')->user();
            
            // Get all activities for this tutor
            $activities = Activity::where('tutor_id', $tutor->id)->get();
            
            // Total activities
            $totalActivities = $activities->count();
            
            // Get all activity IDs
            $activityIds = $activities->pluck('id')->toArray();
            
            // Get all submissions for these activities in one query
            $submissions = \App\Models\ActivitySubmission::whereIn('activity_id', $activityIds)
                ->get()
                ->keyBy(function($submission) {
                    return $submission->activity_id . '_' . $submission->student_id;
                });
            
            // Count completed activities (activities with graded submissions matching the activity's student_id)
            $completedActivities = 0;
            $pendingGrading = 0;
            $overdueActivities = 0;
            
            foreach ($activities as $activity) {
                // Get submission for this activity's student
                $submissionKey = $activity->id . '_' . $activity->student_id;
                $submission = $submissions->get($submissionKey);
                
                if ($submission) {
                    if ($submission->status === 'graded') {
                        $completedActivities++;
                    } elseif ($submission->status === 'submitted') {
                        $pendingGrading++;
                    }
                }
                
                // Check if overdue
                if ($activity->due_date && $activity->due_date->isPast()) {
                    if (!$submission || !in_array($submission->status, ['submitted', 'graded'])) {
                        $overdueActivities++;
                    }
                }
            }
            
            $stats = [
                'total_activities' => $totalActivities,
                'completed_activities' => $completedActivities,
                'pending_grading' => $pendingGrading,
                'overdue_activities' => $overdueActivities,
            ];

            return response()->json($stats);
        }

        /**
         * Download student submission attachment
         */
        public function downloadSubmissionAttachment(Activity $activity, $attachment)
        {
            $tutor = Auth::guard('tutor')->user();
            
            // Ensure the tutor owns this activity
            if ($activity->tutor_id !== $tutor->id) {
                abort(403, 'Unauthorized access to this activity.');
            }

            // Get the submission
            $submission = $activity->submissions()->where('student_id', $activity->student_id)->first();
            
            if (!$submission) {
                abort(404, 'Submission not found.');
            }

            // Decode the attachment path
            $attachmentPath = base64_decode($attachment);
            
            if (!$attachmentPath || !in_array($attachmentPath, $submission->attachments ?? [])) {
                abort(404, 'Attachment not found.');
            }

            // Check if file exists
            if (!Storage::disk('public')->exists($attachmentPath)) {
                abort(404, 'Attachment file not found.');
            }

            $filePath = Storage::disk('public')->path($attachmentPath);
            $fileName = basename($attachmentPath);
            
            // Determine content type
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $contentTypes = [
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'txt' => 'text/plain',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
            ];
            
            $contentType = $contentTypes[$extension] ?? 'application/octet-stream';

            return response()->download($filePath, $fileName, [
                'Content-Type' => $contentType,
            ]);
        }

        // Get student progress
        public function getStudentProgress($studentId)
        {
            $tutor = Auth::guard('tutor')->user();
            
            // Get the student
            $student = Student::findOrFail($studentId);
            
            // Verify the student has sessions with this tutor
            $hasSessions = Session::where('tutor_id', $tutor->id)
                ->where('student_id', $studentId)
                ->exists();
            
            if (!$hasSessions) {
                abort(403, 'You do not have access to this student\'s progress.');
            }
            
            // Get all activities for this student
            $activities = Activity::where('tutor_id', $tutor->id)
                ->where('student_id', $studentId)
                ->with(['submissions' => function($query) use ($studentId) {
                    $query->where('student_id', $studentId);
                }])
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Calculate statistics
            $totalActivities = $activities->count();
            $submittedActivities = $activities->filter(function($activity) use ($studentId) {
                $submission = $activity->studentSubmission($studentId);
                return $submission && $submission->status === 'submitted';
            })->count();
            
            $gradedActivities = $activities->filter(function($activity) use ($studentId) {
                $submission = $activity->studentSubmission($studentId);
                return $submission && $submission->status === 'graded';
            });
            
            $gradedCount = $gradedActivities->count();
            
            // Calculate average score from submissions
            $averageScore = $gradedActivities->avg(function($activity) use ($studentId) {
                $submission = $activity->studentSubmission($studentId);
                if ($submission && $submission->score && $activity->total_points) {
                    return ($submission->score / $activity->total_points) * 100;
                }
                return 0;
            }) ?? 0;
            
            $totalPoints = $gradedActivities->sum(function($activity) use ($studentId) {
                $submission = $activity->studentSubmission($studentId);
                return $submission ? $submission->score : 0;
            });
            
            $maxPoints = $gradedActivities->sum('total_points');
            
            // Get sessions count
            $sessionsCount = Session::where('tutor_id', $tutor->id)
                ->where('student_id', $studentId)
                ->count();
            
            $completedSessions = Session::where('tutor_id', $tutor->id)
                ->where('student_id', $studentId)
                ->where('status', 'completed')
                ->count();

            // Get adaptive learning analysis
            $adaptiveLearningService = new \App\Services\AdaptiveLearningService();
            $learningAnalysis = $adaptiveLearningService->analyzeStudentPerformance($studentId, $tutor->id);
            $suggestedDifficulty = $adaptiveLearningService->getSuggestedDifficulty($studentId, $tutor->id);
            $suggestedFrequency = $adaptiveLearningService->getSuggestedFrequency($studentId, $tutor->id);

            // Get behavioral analysis
            $behavioralAnalysis = $adaptiveLearningService->getBehavioralAnalysis($studentId, $tutor->id);

            // Build detailed AI analytics for the student progress
            $aiAnalytics = $this->buildStudentAIAnalytics($gradedActivities, $studentId, $averageScore, $learningAnalysis, $behavioralAnalysis);

            return view('tutor.students.progress', compact(
                'student',
                'tutor',
                'activities',
                'totalActivities',
                'submittedActivities',
                'gradedCount',
                'averageScore',
                'totalPoints',
                'maxPoints',
                'sessionsCount',
                'completedSessions',
                'learningAnalysis',
                'suggestedDifficulty',
                'suggestedFrequency',
                'aiAnalytics',
                'behavioralAnalysis'
            ));
        }

        /**
         * Build detailed AI analytics for student progress page
         * Analyzes grades, average scores, weak spots, and wrong answers
         */
        private function buildStudentAIAnalytics($gradedActivities, $studentId, $averageScore, $learningAnalysis, $behavioralAnalysis = null)
        {
            $totalQuestions = 0;
            $totalCorrect = 0;
            $totalWrong = 0;
            $wrongAnswerDetails = [];
            $activityBreakdown = [];
            $topicWeaknesses = [];

            foreach ($gradedActivities as $activity) {
                $submission = $activity->studentSubmission($studentId);
                if (!$submission || !$activity->questions || !is_array($activity->questions)) {
                    continue;
                }

                $activityCorrect = 0;
                $activityWrong = 0;
                $activityTotal = count($activity->questions);
                $wrongInActivity = [];

                foreach ($activity->questions as $qIndex => $question) {
                    $questionText = $question['question'] ?? '';
                    $studentAnswerIndex = isset($submission->answers[$qIndex]) ? (int)$submission->answers[$qIndex] : null;
                    $correctAnswerIndex = isset($question['correct_answer']) ? (int)$question['correct_answer'] : null;

                    $totalQuestions++;

                    if ($studentAnswerIndex !== null && $correctAnswerIndex !== null && $studentAnswerIndex === $correctAnswerIndex) {
                        $activityCorrect++;
                        $totalCorrect++;
                    } else {
                        $activityWrong++;
                        $totalWrong++;

                        $correctAnswerText = '';
                        $studentAnswerText = '';
                        if (isset($question['options'])) {
                            $correctAnswerText = $question['options'][$correctAnswerIndex] ?? 'N/A';
                            $studentAnswerText = $studentAnswerIndex !== null ? ($question['options'][$studentAnswerIndex] ?? 'No answer') : 'No answer';
                        }

                        $wrongInActivity[] = [
                            'question' => $questionText,
                            'student_answer' => $studentAnswerText,
                            'correct_answer' => $correctAnswerText,
                        ];
                    }
                }

                $activityAccuracy = $activityTotal > 0 ? round(($activityCorrect / $activityTotal) * 100, 1) : 0;

                $activityBreakdown[] = [
                    'title' => $activity->title,
                    'type' => $activity->type,
                    'score' => $submission->score ?? 0,
                    'total_points' => $activity->total_points,
                    'questions_total' => $activityTotal,
                    'questions_correct' => $activityCorrect,
                    'questions_wrong' => $activityWrong,
                    'accuracy' => $activityAccuracy,
                    'wrong_answers' => $wrongInActivity,
                    'date' => $activity->created_at->format('M d, Y'),
                ];

                // Track topic weaknesses by activity title/type
                if ($activityAccuracy < 70 && $activityTotal > 0) {
                    $topicWeaknesses[] = [
                        'topic' => $activity->title,
                        'type' => $activity->type,
                        'accuracy' => $activityAccuracy,
                        'wrong_count' => $activityWrong,
                    ];
                }
            }

            // Sort weaknesses by accuracy (lowest first)
            usort($topicWeaknesses, function($a, $b) {
                return $a['accuracy'] <=> $b['accuracy'];
            });

            // Collect all wrong answer details across all activities (limit to 10 most recent)
            $allWrongAnswers = [];
            foreach ($activityBreakdown as $ab) {
                foreach ($ab['wrong_answers'] as $wa) {
                    $allWrongAnswers[] = array_merge($wa, ['activity' => $ab['title']]);
                }
            }
            $allWrongAnswers = array_slice($allWrongAnswers, 0, 10);

            $overallAccuracy = $totalQuestions > 0 ? round(($totalCorrect / $totalQuestions) * 100, 1) : 0;

            // Generate AI narrative insight
            $aiInsight = $this->generateAIInsightNarrative(
                $averageScore, $overallAccuracy, $totalQuestions, $totalCorrect, $totalWrong,
                $topicWeaknesses, $learningAnalysis, $activityBreakdown, $behavioralAnalysis
            );

            return [
                'total_questions' => $totalQuestions,
                'total_correct' => $totalCorrect,
                'total_wrong' => $totalWrong,
                'overall_accuracy' => $overallAccuracy,
                'activity_breakdown' => $activityBreakdown,
                'topic_weaknesses' => $topicWeaknesses,
                'wrong_answers' => $allWrongAnswers,
                'ai_insight' => $aiInsight,
            ];
        }

        /**
         * Generate a comprehensive AI narrative insight for the tutor
         */
        private function generateAIInsightNarrative($averageScore, $overallAccuracy, $totalQuestions, $totalCorrect, $totalWrong, $topicWeaknesses, $learningAnalysis, $activityBreakdown, $behavioralAnalysis = null)
        {
            if ($totalQuestions === 0) {
                return [
                    'summary' => 'No graded activities with questions available yet. Once the student completes and gets graded on activities, AI-powered analytics will appear here with detailed insights into their strengths, weaknesses, and personalized learning recommendations.',
                    'grade_assessment' => 'Insufficient data',
                    'strengths' => [],
                    'weaknesses' => [],
                    'recommendations' => [],
                    'performance_level' => 'pending',
                ];
            }

            // Determine performance level
            $performanceLevel = 'needs_improvement';
            if ($averageScore >= 90) $performanceLevel = 'excellent';
            elseif ($averageScore >= 80) $performanceLevel = 'good';
            elseif ($averageScore >= 70) $performanceLevel = 'average';
            elseif ($averageScore >= 60) $performanceLevel = 'below_average';

            // Grade assessment text
            $gradeLabels = [
                'excellent' => 'Outstanding',
                'good' => 'Above Average',
                'average' => 'Satisfactory',
                'below_average' => 'Below Average',
                'needs_improvement' => 'Needs Improvement',
            ];
            $gradeAssessment = $gradeLabels[$performanceLevel] ?? 'N/A';

            // Identify strengths (activities where student scored > 80%)
            $strengths = [];
            foreach ($activityBreakdown as $ab) {
                if ($ab['accuracy'] >= 80 && $ab['questions_total'] > 0) {
                    $strengths[] = $ab['title'] . ' (' . $ab['accuracy'] . '% accuracy)';
                }
            }

            // Identify weaknesses
            $weaknesses = [];
            foreach ($topicWeaknesses as $tw) {
                $weaknesses[] = $tw['topic'] . ' (' . $tw['accuracy'] . '% accuracy — ' . $tw['wrong_count'] . ' wrong)';
            }

            // Generate recommendations
            $recommendations = [];
            if ($averageScore < 60) {
                $recommendations[] = 'Focus on foundational concepts before introducing advanced topics. Consider breaking lessons into smaller, digestible chunks.';
            }
            if (count($topicWeaknesses) > 0) {
                $weakTopics = array_column(array_slice($topicWeaknesses, 0, 3), 'topic');
                $recommendations[] = 'The student struggles most with: ' . implode(', ', $weakTopics) . '. Revisit these topics with additional practice questions and simpler explanations.';
            }
            if ($overallAccuracy < 50) {
                $recommendations[] = 'The student answers more than half the questions incorrectly. Consider one-on-one review sessions to build core understanding before assigning more quizzes.';
            }
            if ($overallAccuracy >= 50 && $overallAccuracy < 70) {
                $recommendations[] = 'The student shows partial understanding but needs reinforcement. Provide practice activities focusing on commonly missed question types.';
            }
            if ($overallAccuracy >= 80) {
                $recommendations[] = 'The student demonstrates strong comprehension. Challenge them with higher-difficulty activities and introduce more complex, application-based questions.';
            }
            if (isset($learningAnalysis['performance_trend']) && $learningAnalysis['performance_trend'] === 'declining') {
                $recommendations[] = 'Performance is trending downward. Check in with the student to identify external factors or content-related challenges causing the decline.';
            }
            if (isset($learningAnalysis['performance_trend']) && $learningAnalysis['performance_trend'] === 'improving') {
                $recommendations[] = 'Great news — performance is improving! Maintain the current teaching approach and gradually increase complexity.';
            }

            // Build summary
            $summary = "Based on {$totalQuestions} questions across " . count($activityBreakdown) . " graded activities, ";
            $summary .= "the student answered {$totalCorrect} correctly and {$totalWrong} incorrectly, ";
            $summary .= "achieving an overall accuracy of {$overallAccuracy}% with an average grade of " . round($averageScore, 1) . "%. ";

            if (count($topicWeaknesses) > 0) {
                $summary .= "The weakest areas include " . implode(', ', array_column(array_slice($topicWeaknesses, 0, 2), 'topic')) . ". ";
            }

            if ($performanceLevel === 'excellent') {
                $summary .= "This student is performing exceptionally well and is ready for advanced challenges.";
            } elseif ($performanceLevel === 'good') {
                $summary .= "The student shows solid understanding with room for growth in specific areas.";
            } elseif ($performanceLevel === 'average') {
                $summary .= "The student demonstrates basic understanding but needs targeted support to strengthen weak areas.";
            } else {
                $summary .= "The student requires significant support and a revised teaching approach to improve comprehension.";
            }

            // If we have behavioral analysis and Gemini is available, refine the summary
            if ($behavioralAnalysis) {
                $geminiService = new \App\Services\GeminiService();
                $perfData = [
                    'average_score' => $averageScore,
                    'accuracy' => $overallAccuracy,
                    'total_questions' => $totalQuestions,
                    'weaknesses' => array_column($topicWeaknesses, 'topic'),
                    'trend' => $learningAnalysis['performance_trend'] ?? 'stable',
                    'heuristic_behavior' => $behavioralAnalysis
                ];
                $aiSummary = $geminiService->generateBehavioralSummary($perfData);
                if ($aiSummary) {
                    $summary = $aiSummary;
                }
            }

            return [
                'summary' => $summary,
                'grade_assessment' => $gradeAssessment,
                'strengths' => $strengths,
                'weaknesses' => $weaknesses,
                'recommendations' => $recommendations,
                'performance_level' => $performanceLevel,
                'behavioral_analysis' => $behavioralAnalysis
            ];
        }

        // Show all students for the tutor
        public function students()
        {
            $tutor = Auth::guard('tutor')->user();
            
            // Get current students (with active sessions - accepted or pending)
            // A student is "current" if they have at least one accepted or pending session
            $currentStudents = Student::whereHas('sessions', function($query) use ($tutor) {
                $query->where('tutor_id', $tutor->id)
                    ->whereIn('status', ['accepted', 'pending']);
            })->with(['sessions' => function($query) use ($tutor) {
                $query->where('tutor_id', $tutor->id)
                    ->orderBy('date', 'desc')
                    ->orderBy('start_time', 'desc');
            }])->distinct()->get();

            // Get past students (with completed sessions only, and no active sessions)
            $pastStudents = Student::whereHas('sessions', function($query) use ($tutor) {
                $query->where('tutor_id', $tutor->id)
                    ->where('status', 'completed');
            })->whereDoesntHave('sessions', function($query) use ($tutor) {
                $query->where('tutor_id', $tutor->id)
                    ->whereIn('status', ['accepted', 'pending']);
            })->with(['sessions' => function($query) use ($tutor) {
                $query->where('tutor_id', $tutor->id)
                    ->orderBy('date', 'desc')
                    ->orderBy('start_time', 'desc');
            }])->distinct()->get();

            // Get rejected students (students whose sessions were rejected)
            $rejectedStudents = Student::whereHas('sessions', function($query) use ($tutor) {
                $query->where('tutor_id', $tutor->id)
                    ->where('status', 'rejected');
            })->whereDoesntHave('sessions', function($query) use ($tutor) {
                $query->where('tutor_id', $tutor->id)
                    ->whereIn('status', ['accepted', 'pending', 'completed']);
            })->with(['sessions' => function($query) use ($tutor) {
                $query->where('tutor_id', $tutor->id);
            }])->get();

            // Calculate stats for each student
            $currentStudents = $currentStudents->map(function($student) use ($tutor) {
                $sessions = $student->sessions->where('tutor_id', $tutor->id);
                $activities = Activity::where('tutor_id', $tutor->id)
                    ->where('student_id', $student->id)
                    ->with(['submissions' => function($query) use ($student) {
                        $query->where('student_id', $student->id);
                    }])
                    ->get();
                
                $gradedActivities = $activities->filter(function($activity) use ($student) {
                    $submission = $activity->studentSubmission($student->id);
                    return $submission && $submission->status === 'graded';
                });
                $totalPoints = $gradedActivities->sum(function($activity) use ($student) {
                    $submission = $activity->studentSubmission($student->id);
                    return $submission ? $submission->score : 0;
                });
                $maxPoints = $gradedActivities->sum('total_points');
                $averageScore = $maxPoints > 0 ? ($totalPoints / $maxPoints) * 100 : 0;
                
                $student->stats = [
                    'total_sessions' => $sessions->count(),
                    'completed_sessions' => $sessions->where('status', 'completed')->count(),
                    'total_activities' => $activities->count(),
                    'completed_activities' => $activities->whereIn('status', ['completed', 'graded'])->count(),
                    'average_score' => $averageScore,
                    'last_session' => $sessions->sortByDesc('created_at')->first()?->created_at,
                    'online_sessions' => $sessions->where('session_type', 'online')->count(),
                    'face_to_face_sessions' => $sessions->where('session_type', 'face_to_face')->count(),
                    'last_session_type' => $sessions->sortByDesc('created_at')->first()?->session_type,
                ];
                
                return $student;
            });

            $pastStudents = $pastStudents->map(function($student) use ($tutor) {
                $sessions = $student->sessions->where('tutor_id', $tutor->id);
                $activities = Activity::where('tutor_id', $tutor->id)
                    ->where('student_id', $student->id)
                    ->with(['submissions' => function($query) use ($student) {
                        $query->where('student_id', $student->id);
                    }])
                    ->get();
                
                $gradedActivities = $activities->filter(function($activity) use ($student) {
                    $submission = $activity->studentSubmission($student->id);
                    return $submission && $submission->status === 'graded';
                });
                $totalPoints = $gradedActivities->sum(function($activity) use ($student) {
                    $submission = $activity->studentSubmission($student->id);
                    return $submission ? $submission->score : 0;
                });
                $maxPoints = $gradedActivities->sum('total_points');
                $averageScore = $maxPoints > 0 ? ($totalPoints / $maxPoints) * 100 : 0;
                
                $student->stats = [
                    'total_sessions' => $sessions->count(),
                    'completed_sessions' => $sessions->where('status', 'completed')->count(),
                    'total_activities' => $activities->count(),
                    'completed_activities' => $activities->whereIn('status', ['completed', 'graded'])->count(),
                    'average_score' => $averageScore,
                    'last_session' => $sessions->sortByDesc('created_at')->first()?->created_at,
                    'online_sessions' => $sessions->where('session_type', 'online')->count(),
                    'face_to_face_sessions' => $sessions->where('session_type', 'face_to_face')->count(),
                    'last_session_type' => $sessions->sortByDesc('created_at')->first()?->session_type,
                ];
                
                return $student;
            });

            $rejectedStudents = $rejectedStudents->map(function($student) use ($tutor) {
                $sessions = $student->sessions->where('tutor_id', $tutor->id);
                $activities = Activity::where('tutor_id', $tutor->id)
                    ->where('student_id', $student->id)
                    ->with(['submissions' => function($query) use ($student) {
                        $query->where('student_id', $student->id);
                    }])
                    ->get();
                
                $gradedActivities = $activities->filter(function($activity) use ($student) {
                    $submission = $activity->studentSubmission($student->id);
                    return $submission && $submission->status === 'graded';
                });
                $totalPoints = $gradedActivities->sum(function($activity) use ($student) {
                    $submission = $activity->studentSubmission($student->id);
                    return $submission ? $submission->score : 0;
                });
                $maxPoints = $gradedActivities->sum('total_points');
                $averageScore = $maxPoints > 0 ? ($totalPoints / $maxPoints) * 100 : 0;
                
                $student->stats = [
                    'total_sessions' => $sessions->count(),
                    'rejected_sessions' => $sessions->where('status', 'rejected')->count(),
                    'total_activities' => $activities->count(),
                    'completed_activities' => $activities->whereIn('status', ['completed', 'graded'])->count(),
                    'average_score' => $averageScore,
                    'last_session' => $sessions->sortByDesc('created_at')->first()?->created_at,
                    'online_sessions' => $sessions->where('session_type', 'online')->count(),
                    'face_to_face_sessions' => $sessions->where('session_type', 'face_to_face')->count(),
                    'last_session_type' => $sessions->sortByDesc('created_at')->first()?->session_type,
                ];
                
                return $student;
            });

            return view('tutor.students.index', compact('currentStudents', 'pastStudents', 'rejectedStudents', 'tutor'));
        }

        // Show tutor schedule with calendar
        public function schedule(Request $request)
        {
            $tutor = Auth::guard('tutor')->user();
            
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
            
            // Get all accepted and completed sessions for the tutor in the requested month
            $sessions = Session::where('tutor_id', $tutor->id)
                ->whereIn('status', ['accepted', 'completed'])
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->with(['student'])
                ->orderBy('date')
                ->orderBy('start_time')
                ->get();
            
            // Group sessions by date
            $sessionsByDate = $sessions->groupBy(function($session) {
                return $session->date->format('Y-m-d');
            });
            
            // Get calendar data
            $calendarData = $this->generateCalendarData($year, $month, $sessionsByDate);
            
            return view('tutor.schedule.index', compact('tutor', 'sessions', 'sessionsByDate', 'calendarData', 'year', 'month'));
        }

        // Generate calendar data for the month
        private function generateCalendarData($year, $month, $sessionsByDate)
        {
            $firstDay = now()->setYear((int)$year)->setMonth((int)$month)->startOfMonth();
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

        /**
         * Get student details for AI quest generation (AJAX)
         */
        public function getStudentDetails($studentId)
        {
            $tutor = Auth::guard('tutor')->user();
            
            $student = Student::find($studentId);
            
            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            // Verify the student has sessions with this tutor
            $hasSessions = Session::where('tutor_id', $tutor->id)
                ->where('student_id', $studentId)
                ->exists();
            
            if (!$hasSessions) {
                return response()->json(['error' => 'Unauthorized access'], 403);
            }

            // Get past activity history for context
            $pastActivities = Activity::where('tutor_id', $tutor->id)
                ->where('student_id', $studentId)
                ->select('title', 'type', 'score', 'total_points', 'status')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'student' => [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'course' => $student->course ?? 'Not specified',
                    'year_level' => $student->year_level ?? 'Not specified',
                    'subjects_interest' => $student->subjects_interest ?? 'General',
                ],
                'past_activities' => $pastActivities,
                'tutor_specialization' => $tutor->specialization ?? 'General',
            ]);
        }

        /**
         * Generate AI-powered quest/activity using Gemini
         */
        public function generateAIQuest(Request $request)
        {
            $tutor = Auth::guard('tutor')->user();
            
            $request->validate([
                'student_id' => 'required|exists:students,id',
                'num_questions' => 'nullable|integer|min:1|max:20',
                'difficulty' => 'nullable|in:easy,medium,hard',
                'topic_focus' => 'nullable|string|max:255',
            ]);

            $student = Student::find($request->student_id);
            
            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            // Verify the student has sessions with this tutor
            $hasSessions = Session::where('tutor_id', $tutor->id)
                ->where('student_id', $student->id)
                ->exists();
            
            if (!$hasSessions) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $numQuestions = $request->num_questions ?? 5;
            $difficulty = $request->difficulty ?? 'medium';
            $topicFocus = $request->topic_focus ?? '';

            // Get past activity history for context
            $pastActivities = Activity::where('tutor_id', $tutor->id)
                ->where('student_id', $student->id)
                ->select('title', 'type', 'score', 'total_points', 'status')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $pastActivityContext = '';
            if ($pastActivities->count() > 0) {
                $pastActivityContext = "Past activities with this student:\n";
                foreach ($pastActivities as $pa) {
                    $scoreInfo = $pa->score !== null ? "Score: {$pa->score}/{$pa->total_points}" : "Not yet graded";
                    $pastActivityContext .= "- {$pa->title} (Type: {$pa->type}, Status: {$pa->status}, {$scoreInfo})\n";
                }
            }

            // Build the prompt for Gemini
            $prompt = "You are an educational AI assistant for MentorHub, a tutoring platform. Generate a personalized quiz/activity for a student based on their profile.\n\n";
            $prompt .= "STUDENT PROFILE:\n";
            $prompt .= "- Name: {$student->first_name} {$student->last_name}\n";
            $prompt .= "- Course: " . ($student->course ?? 'Not specified') . "\n";
            $prompt .= "- Year Level: " . ($student->year_level ?? 'Not specified') . "\n";
            $prompt .= "- Subjects of Interest: " . ($student->subjects_interest ?? 'General') . "\n\n";
            $prompt .= "TUTOR INFO:\n";
            $prompt .= "- Specialization: " . ($tutor->specialization ?? 'General') . "\n\n";
            
            if ($pastActivityContext) {
                $prompt .= $pastActivityContext . "\n";
            }

            $prompt .= "GENERATION SETTINGS:\n";
            $prompt .= "- Number of Questions: {$numQuestions}\n";
            $prompt .= "- Difficulty Level: {$difficulty}\n";
            if ($topicFocus) {
                $prompt .= "- Specific Topic Focus: {$topicFocus}\n";
            }
            
            $prompt .= "\nGENERATE a complete activity with the following JSON structure. Make the questions relevant to the student's interests and course. Each question should have exactly 4 options with one correct answer.\n\n";
            $prompt .= "Respond ONLY with valid JSON in this exact format:\n";
            $prompt .= "{\n";
            $prompt .= "  \"title\": \"Activity title relevant to the topic\",\n";
            $prompt .= "  \"type\": \"quiz\",\n";
            $prompt .= "  \"description\": \"Brief description of this activity\",\n";
            $prompt .= "  \"instructions\": \"Clear instructions for the student\",\n";
            $prompt .= "  \"total_points\": 100,\n";
            $prompt .= "  \"time_limit\": 30,\n";
            $prompt .= "  \"questions\": [\n";
            $prompt .= "    {\n";
            $prompt .= "      \"question\": \"Question text here\",\n";
            $prompt .= "      \"options\": [\"Option A\", \"Option B\", \"Option C\", \"Option D\"],\n";
            $prompt .= "      \"correct_answer\": 0\n";
            $prompt .= "    }\n";
            $prompt .= "  ]\n";
            $prompt .= "}\n\n";
            $prompt .= "IMPORTANT: The \"correct_answer\" field must be the zero-based index (0, 1, 2, or 3) of the correct option. Make questions educational, age-appropriate, and aligned with the student's course and interests. Vary the correct answers across questions.";

            $geminiApiKey = config('services.gemini.api_key');
            
            if (empty($geminiApiKey)) {
                return response()->json(['error' => 'AI service is not configured. Please contact administrator.'], 500);
            }

            try {
                $geminiUrl = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent';
                
                $response = Http::timeout(60)->post($geminiUrl . '?key=' . $geminiApiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 4096
                    ]
                ]);

                if ($response->successful()) {
                    $responseData = $response->json();
                    $content = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    
                    // Parse the JSON response
                    $activityData = json_decode($content, true);
                    
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        // Try to extract JSON from response
                        if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
                            $activityData = json_decode($matches[1], true);
                        } elseif (preg_match('/\{.*\}/s', $content, $matches)) {
                            $activityData = json_decode($matches[0], true);
                        }
                    }

                    if (!$activityData || !isset($activityData['questions'])) {
                        Log::error('AI Quest Generation: Invalid response format', ['content' => substr($content, 0, 500)]);
                        return response()->json(['error' => 'AI generated an invalid response. Please try again.'], 500);
                    }

                    // Validate and sanitize the response
                    $sanitized = [
                        'title' => $activityData['title'] ?? 'AI Generated Quiz',
                        'type' => in_array($activityData['type'] ?? '', ['activity', 'exam', 'assignment', 'quiz']) 
                            ? $activityData['type'] : 'quiz',
                        'description' => $activityData['description'] ?? 'AI-generated activity based on student interests.',
                        'instructions' => $activityData['instructions'] ?? 'Answer all questions carefully. Select the best answer for each question.',
                        'total_points' => (int)($activityData['total_points'] ?? 100),
                        'time_limit' => (int)($activityData['time_limit'] ?? 30),
                        'questions' => [],
                    ];

                    foreach ($activityData['questions'] as $q) {
                        if (!isset($q['question']) || !isset($q['options']) || !is_array($q['options'])) {
                            continue;
                        }
                        
                        $sanitized['questions'][] = [
                            'question' => $q['question'],
                            'options' => array_values($q['options']),
                            'correct_answer' => (int)($q['correct_answer'] ?? 0),
                        ];
                    }

                    if (empty($sanitized['questions'])) {
                        return response()->json(['error' => 'AI failed to generate valid questions. Please try again.'], 500);
                    }

                    Log::info('AI Quest generated successfully', [
                        'student_id' => $student->id,
                        'tutor_id' => $tutor->id,
                        'num_questions' => count($sanitized['questions']),
                    ]);

                    return response()->json([
                        'success' => true,
                        'activity' => $sanitized,
                        'student_name' => $student->first_name . ' ' . $student->last_name,
                    ]);
                } else {
                    Log::error('Gemini API failed', [
                        'status' => $response->status(),
                        'body' => substr($response->body(), 0, 300),
                    ]);
                    return response()->json(['error' => 'AI service is temporarily unavailable. Please try again later.'], 500);
                }
            } catch (\Exception $e) {
                Log::error('AI Quest Generation Error', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'An error occurred while generating the quest. Please try again.'], 500);
            }
        }
    }