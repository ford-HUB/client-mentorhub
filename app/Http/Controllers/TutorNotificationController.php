<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Session;
use Illuminate\Support\Facades\Auth;

class TutorNotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::guard('tutor')->id())
            ->where('user_type', 'tutor')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tutor.notifications', compact('notifications'));
    }

    /**
     * Get all notifications for tutor dashboard (API endpoint)
     */
    public function getAll()
    {
        $tutor = Auth::guard('tutor')->user();
        
        $notifications = Notification::where('user_id', $tutor->id)
            ->where('user_type', 'tutor')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'created_at_full' => $notification->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json($notifications);
    }

    /**
     * Get full session + student details for a booking notification.
     * Called via AJAX from the notification page when the tutor clicks "View Student Info".
     */
    public function getBookingDetails($notificationId)
    {
        $tutor = Auth::guard('tutor')->user();

        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $tutor->id)
            ->where('user_type', 'tutor')
            ->whereIn('type', ['new_booking', 'booking_request'])
            ->firstOrFail();

        if (!$notification->related_id) {
            return response()->json(['error' => 'No booking linked to this notification.'], 404);
        }

        $session = Session::where('id', $notification->related_id)
            ->where('tutor_id', $tutor->id)
            ->with('student')
            ->first();

        if (!$session) {
            return response()->json(['error' => 'Booking not found.'], 404);
        }

        $student = $session->student;

        return response()->json([
            'session' => [
                'id'           => $session->id,
                'session_type' => $session->session_type,
                'booking_type' => $session->booking_type,
                'date'         => $session->date->format('F j, Y'),
                'start_time'   => date('g:i A', strtotime($session->start_time)),
                'end_time'     => date('g:i A', strtotime($session->end_time)),
                'status'       => $session->status,
                'rate'         => number_format($session->rate, 2),
                'notes'        => $session->notes,
            ],
            'student' => [
                'id'                => $student->id,
                'name'              => $student->first_name . ' ' . $student->last_name,
                'student_id'        => $student->student_id,
                'email'             => $student->email,
                'course'            => $student->course,
                'year_level'        => $student->year_level,
                'phone'             => $student->phone,
                'subjects_interest' => $student->subjects_interest,
                'avatar_initials'   => strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)),
            ],
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::guard('tutor')->id())
            ->where('user_type', 'tutor')
            ->firstOrFail();

        $notification->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::guard('tutor')->id())
            ->where('user_type', 'tutor')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::guard('tutor')->id())
            ->where('user_type', 'tutor')
            ->firstOrFail();

        $notification->delete();

        return response()->json(['success' => true]);
    }
}
