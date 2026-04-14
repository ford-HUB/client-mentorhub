<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\WebRTCOffer;
use App\Events\WebRTCAnswer;
use App\Events\WebRTCIceCandidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebRTCController extends Controller
{
    public function offer(Request $request)
    {
        $request->validate([
            'roomId' => 'required|string',
            'offer' => 'required|array',
            'from' => 'required|integer',
            'sessionId' => 'nullable|string|max:128',
        ]);

        try {
            broadcast(new WebRTCOffer(
                $request->input('roomId'),
                $request->input('offer'),
                (int) $request->input('from'),
                $request->input('sessionId') ? (string) $request->input('sessionId') : null
            ));

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('WebRTC offer broadcast failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function answer(Request $request)
    {
        $request->validate([
            'roomId' => 'required|string',
            'answer' => 'required|array',
            'from' => 'required|integer',
            'sessionId' => 'nullable|string|max:128',
        ]);

        try {
            broadcast(new WebRTCAnswer(
                $request->input('roomId'),
                $request->input('answer'),
                (int) $request->input('from'),
                $request->input('sessionId') ? (string) $request->input('sessionId') : null
            ));

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('WebRTC answer broadcast failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function iceCandidate(Request $request)
    {
        $request->validate([
            'roomId' => 'required|string',
            'candidate' => 'required|array',
            'from' => 'required|integer',
            'sessionId' => 'nullable|string|max:128',
        ]);

        try {
            $candidate = $request->input('candidate');

            $plainCandidate = [
                'candidate' => $candidate['candidate'] ?? null,
                'sdpMid' => $candidate['sdpMid'] ?? null,
                'sdpMLineIndex' => $candidate['sdpMLineIndex'] ?? null,
                'usernameFragment' => $candidate['usernameFragment'] ?? null,
            ];

            broadcast(new WebRTCIceCandidate(
                $request->input('roomId'),
                $plainCandidate,
                (int) $request->input('from'),
                $request->input('sessionId') ? (string) $request->input('sessionId') : null
            ));

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('WebRTC ICE broadcast failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}