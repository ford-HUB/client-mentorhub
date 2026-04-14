<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CallController extends Controller
{
    public function initiate(Request $request)
    {
        $request->validate([
            'callType' => 'required|in:voice,video',
            'callerId' => 'required|integer',
            'callerName' => 'required|string',
            'receiverId' => 'required|integer',
            'receiverType' => 'required|in:student,tutor',
            'roomId' => 'required|string'
        ]);

        try {
            // Derive authenticated caller identity from active guard.
            $authUser = null;
            $authType = null;
            if (Auth::guard('student')->check()) {
                $authUser = Auth::guard('student')->user();
                $authType = 'student';
            } elseif (Auth::guard('tutor')->check()) {
                $authUser = Auth::guard('tutor')->user();
                $authType = 'tutor';
            }

            if (!$authUser || !$authType) {
                return response()->json(['success' => false, 'error' => 'Unauthenticated caller.'], 401);
            }

            // Prevent forged/mismatched caller payloads.
            if ((int) $request->callerId !== (int) $authUser->id) {
                return response()->json(['success' => false, 'error' => 'Caller ID mismatch.'], 422);
            }

            // Prevent self-calls (same user + same type).
            if (
                (int) $request->callerId === (int) $request->receiverId &&
                (string) $authType === (string) $request->receiverType
            ) {
                return response()->json(['success' => false, 'error' => 'Cannot call your own account.'], 422);
            }

            // Enforce cross-role calling for this app (student <-> tutor).
            if ((string) $request->receiverType === (string) $authType) {
                return response()->json(['success' => false, 'error' => 'Invalid receiver type for this caller.'], 422);
            }

            broadcast(new \App\Events\IncomingCall([
                'callType' => $request->callType,
                'callerId' => $request->callerId,
                'callerName' => $request->callerName,
                'receiverId' => $request->receiverId,
                'receiverType' => $request->receiverType,
                'roomId' => $request->roomId
            ]));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error initiating call: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function answer(Request $request)
    {
        $request->validate([
            'roomId' => 'required|string',
            'answeredId' => 'sometimes|integer',
            'answeredType' => 'sometimes|in:student,tutor',
            'receiverId' => 'sometimes|integer',
            'receiverType' => 'sometimes|in:student,tutor',
        ]);

        try {
            $answeredId = $request->input('answeredId') ?? $request->input('receiverId');
            $answeredType = $request->input('answeredType') ?? $request->input('receiverType');

            if ($answeredId === null || $answeredType === null) {
                return response()->json([
                    'success' => false,
                    'error' => 'answeredId/answeredType (or legacy receiverId/receiverType) required.',
                ], 422);
            }

            $answeredId = (int) $answeredId;

            broadcast(new \App\Events\CallAnswered([
                'roomId' => $request->roomId,
                'answeredId' => $answeredId,
                'answeredType' => $answeredType,
            ]));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function decline(Request $request)
    {
        $request->validate([
            'roomId' => 'required|string',
            'declinedId' => 'sometimes|integer',
            'declinedType' => 'sometimes|in:student,tutor',
            'receiverId' => 'sometimes|integer',
            'receiverType' => 'sometimes|in:student,tutor',
        ]);

        try {
            $declinedId = $request->input('declinedId') ?? $request->input('receiverId');
            $declinedType = $request->input('declinedType') ?? $request->input('receiverType');

            if ($declinedId === null || $declinedType === null) {
                return response()->json([
                    'success' => false,
                    'error' => 'declinedId/declinedType (or legacy receiverId/receiverType) required.',
                ], 422);
            }

            $declinedId = (int) $declinedId;

            broadcast(new \App\Events\CallDeclined([
                'roomId' => $request->roomId,
                'declinedId' => $declinedId,
                'declinedType' => $declinedType,
            ]));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error declining call: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function end(Request $request)
    {
        $request->validate([
            'roomId' => 'required|string',
            'endedBy' => 'required|integer'
        ]);

        try {
            broadcast(new \App\Events\CallEnded([
                'roomId' => $request->roomId,
                'endedBy' => $request->endedBy
            ]));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}