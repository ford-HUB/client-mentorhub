<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\WebRTCService;
use Illuminate\Support\Facades\Auth;

class CallManager extends Component
{
    public $isInCall = false;
    public $callType = null;
    public $roomId = null;
    public $callerId = null;
    public $receiverId = null;
    public $callerName = null;
    public $receiverName = null;
    public $callerType = null;
    public $isCaller = false;
    public $isReceiver = false;
    public $callerProfilePicture = null;
    public $receiverProfilePicture = null;

    protected $listeners = [
        'initiateCall' => 'handleInitiateCall',
        'callIncoming' => 'handleIncomingCall',
        'callAnswered' => 'handleCallAnswered',
        'callEnded' => 'handleCallEnded',
    ];

    public function mount()
    {
        // Initialize all properties to null/false to prevent any injection issues
        $this->isInCall = false;
        $this->callType = null;
        $this->roomId = null;
        $this->callerId = null;
        $this->receiverId = null;
        $this->callerName = null;
        $this->receiverName = null;
        $this->callerType = null;
        $this->isCaller = false;
        $this->isReceiver = false;
        $this->callerProfilePicture = null;
        $this->receiverProfilePicture = null;
    }

    public function handleInitiateCall($data)
    {
        // Debug: Log the received data
        \Log::info('CallManager::handleInitiateCall received data:', $data);
        
        $callType = $data['callType'] ?? null;
        $receiverId = $data['receiverId'] ?? null;
        $receiverName = $data['receiverName'] ?? null;

        if (!$receiverId || !$callType) {
            \Log::error('CallManager::handleInitiateCall - Missing required data:', [
                'callType' => $callType,
                'receiverId' => $receiverId,
                'receiverName' => $receiverName
            ]);
            return;
        }

        \Log::info('CallManager::handleInitiateCall - Processing call:', [
            'callType' => $callType,
            'receiverId' => $receiverId,
            'receiverName' => $receiverName
        ]);

        $this->callType = $callType;
        $this->receiverId = $receiverId;
        $this->receiverName = $receiverName;
        
        // Get current user info - use provided caller info if available, otherwise get from current user
        if (isset($data['callerId']) && isset($data['callerName']) && isset($data['callerType'])) {
            // Use provided caller information
            $this->callerId = $data['callerId'];
            $this->callerName = $data['callerName'];
            $this->callerType = $data['callerType'];
            $this->callerProfilePicture = $data['callerProfilePicture'] ?? null;
            $this->receiverProfilePicture = $data['receiverProfilePicture'] ?? null;
            
            // Additional debug logging
            \Log::info('CallManager setting caller profile picture:', [
                'callerId' => $this->callerId,
                'callerName' => $this->callerName,
                'callerType' => $this->callerType,
                'callerProfilePicture' => $this->callerProfilePicture,
                'callerProfilePicturePath' => $this->callerProfilePicture ? asset('storage/' . $this->callerProfilePicture) : 'No picture'
            ]);
            
            // Debug logging
            \Log::info('CallManager received profile data:', [
                'callerProfilePicture' => $this->callerProfilePicture,
                'receiverProfilePicture' => $this->receiverProfilePicture,
                'callerName' => $this->callerName,
                'receiverName' => $this->receiverName,
                'isCaller' => $this->isCaller,
                'callerProfilePicturePath' => $this->callerProfilePicture ? asset('storage/' . $this->callerProfilePicture) : 'No picture',
                'receiverProfilePicturePath' => $this->receiverProfilePicture ? asset('storage/' . $this->receiverProfilePicture) : 'No picture',
                'raw_data_received' => $data
            ]);
        } else {
            // Fallback to getting from current user (for backward compatibility)
            if (Auth::guard('student')->check()) {
                $user = Auth::guard('student')->user();
                $this->callerId = $user->id;
                $this->callerName = $user->getFullName();
                $this->callerType = 'student';
                $this->callerProfilePicture = $user->profile_picture;
            } elseif (Auth::guard('tutor')->check()) {
                $user = Auth::guard('tutor')->user();
                $this->callerId = $user->id;
                $this->callerName = $user->getFullName();
                $this->callerType = 'tutor';
                $this->callerProfilePicture = $user->profile_picture;
            } else {
                return;
            }
            
            // Get receiver profile picture (fallback)
            $this->receiverProfilePicture = $this->getUserProfilePicture($this->receiverId, $this->callerType === 'student' ? 'tutor' : 'student');
        }
        
        
        $this->isCaller = true;
        $this->isReceiver = false;
        
        // Generate room ID
        $webRTCService = new WebRTCService();
        $this->roomId = $webRTCService->generateRoomId();
        
        $this->isInCall = true;
        
        // Log call initiation
        $webRTCService->logCallActivity(
            $this->callerId, 
            $callType, 
            'call_initiated'
        );

        // Send call notification to the receiver through Socket.IO
        // This will be handled by the JavaScript to send to the actual receiver
        $callData = [
            'roomId' => $this->roomId,
            'callType' => $this->callType,
            'callerId' => $this->callerId,
            'callerName' => $this->callerName,
            'callerType' => $this->callerType,
            'receiverId' => $this->receiverId,
            'receiverName' => $this->receiverName,
            'receiverType' => $data['receiverType'] ?? $this->getReceiverType()
        ];
        
        \Log::info('CallManager::handleInitiateCall - Dispatching sendCallToReceiver:', $callData);
        
        // Debug: Log each field individually
        \Log::info('CallManager::handleInitiateCall - Field details:', [
            'roomId' => $this->roomId,
            'callType' => $this->callType,
            'callerId' => $this->callerId,
            'callerName' => $this->callerName,
            'receiverId' => $this->receiverId,
            'receiverName' => $this->receiverName,
            'receiverType' => $data['receiverType'] ?? $this->getReceiverType(),
            'callerType' => $this->callerType
        ]);
        
        // Debug: Log the exact data being dispatched
        $dispatchData = [
            'roomId' => $this->roomId,
            'callType' => $this->callType,
            'callerId' => $this->callerId,
            'callerName' => $this->callerName,
            'receiverId' => $this->receiverId,
            'receiverName' => $this->receiverName,
            'receiverType' => $data['receiverType'] ?? $this->getReceiverType(),
            'callerType' => $this->callerType
        ];
        
        \Log::info('CallManager::handleInitiateCall - Data being dispatched:', $dispatchData);
        
        // Dispatch to JavaScript to send through socket
        // Ensure all data is properly serialized for JavaScript
        try {
        $this->dispatch('sendCallToReceiver', $dispatchData);
        
        // Also dispatch to initialize WebRTC for the caller
        $this->dispatch('initializeWebRTC', [
            'roomId' => $this->roomId,
            'isCaller' => true,
            'callType' => $this->callType
        ]);
        } catch (\Exception $e) {
            \Log::error('CallManager::handleInitiateCall - Error dispatching events:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'callData' => $dispatchData
            ]);
            
            // Reset call state on error
            $this->resetCall();
            
            // Dispatch error event to frontend
            $this->dispatch('callError', [
                'message' => 'Failed to initiate call. Please check your connection and try again.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function handleIncomingCall($data)
    {
        // This method handles when this user receives a call
        $this->roomId = $data['roomId'];
        $this->callType = $data['callType'];
        $this->callerId = $data['callerId'];
        $this->callerName = $data['callerName'];
        $this->receiverId = $data['receiverId'];
        
        // The UI schema dictates that "caller" = Local Viewport User, and "receiver" = Remote Counterpart User.
        if (Auth::guard('student')->check()) {
            $currentUser = Auth::guard('student')->user();
            // Local User (Student)
            $this->callerId = $currentUser->id;
            $this->callerName = $currentUser->getFullName();
            $this->callerProfilePicture = $currentUser->profile_picture;
            
            // Remote Counterpart (Tutor)
            $this->receiverId = $data['callerId'];
            $this->receiverName = $data['callerName'];
            $this->receiverProfilePicture = $this->getUserProfilePicture($data['callerId'], 'tutor');
            
            \Log::info('Student receiving call - mapping established:', [
                'localName' => $this->callerName,
                'remoteName' => $this->receiverName
            ]);
        } elseif (Auth::guard('tutor')->check()) {
            $currentUser = Auth::guard('tutor')->user();
            // Local User (Tutor)
            $this->callerId = $currentUser->id;
            $this->callerName = $currentUser->getFullName();
            $this->callerProfilePicture = $currentUser->profile_picture;
            
            // Remote Counterpart (Student)
            $this->receiverId = $data['callerId'];
            $this->receiverName = $data['callerName'];  
            $this->receiverProfilePicture = $this->getUserProfilePicture($data['callerId'], 'student');
            
            \Log::info('Tutor receiving call - mapping established:', [
                'localName' => $this->callerName,
                'remoteName' => $this->receiverName
            ]);
        }
        
        $this->isCaller = false;
        $this->isReceiver = true;
        $this->isInCall = true;
    }

    public function answerCall()
    {
        if (!$this->isInCall || !$this->isReceiver) {
            \Log::warning('CallManager::answerCall - Invalid call state', [
                'isInCall' => $this->isInCall,
                'isReceiver' => $this->isReceiver
            ]);
            return;
        }

        try {
        // Log call answered
        $webRTCService = new WebRTCService();
        $webRTCService->logCallActivity(
            $this->receiverId, 
            $this->callType, 
            'call_answered'
        );
        
        $answeredId = Auth::guard('student')->check()
            ? Auth::guard('student')->id()
            : Auth::guard('tutor')->id();
        $answeredType = Auth::guard('student')->check() ? 'student' : 'tutor';

        $this->dispatch('callAnswered', [
            'roomId' => $this->roomId,
            'answeredId' => $answeredId,
            'answeredType' => $answeredType,
        ]);

        $this->dispatch('socketCallAnswered', [
            'roomId' => $this->roomId,
            'answeredId' => $answeredId,
            'answeredType' => $answeredType,
        ]);
        } catch (\Exception $e) {
            \Log::error('CallManager::answerCall - Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('callError', [
                'message' => 'Failed to answer call. Please try again.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function declineCall()
    {
        if (!$this->isInCall || !$this->isReceiver) {
            return;
        }

        // Log call declined
        $webRTCService = new WebRTCService();
        $webRTCService->logCallActivity(
            $this->receiverId, 
            $this->callType, 
            'call_declined'
        );
        
        $declinedId = Auth::guard('student')->check()
            ? Auth::guard('student')->id()
            : Auth::guard('tutor')->id();
        $declinedType = Auth::guard('student')->check() ? 'student' : 'tutor';

        $this->dispatch('callDeclined', [
            'roomId' => $this->roomId,
            'declinedId' => $declinedId,
            'declinedType' => $declinedType,
        ]);

        $this->dispatch('socketCallDeclined', [
            'roomId' => $this->roomId,
            'declinedId' => $declinedId,
            'declinedType' => $declinedType,
        ]);
        
        $this->resetCall();
    }

    public function handleCallAnswered($data)
    {
        // This method handles when the call is answered by the receiver
        if ($this->isInCall && $this->isCaller) {
            \Log::info('Call answered by receiver, Caller continuing connection.');
            // Caller WebRTC is already initialized and waiting for the answer.
            // Do not re-dispatch initializeWebRTC here as it will reset the camera.
        }
    }

    public function handleCallEnded($data)
    {
        // This method handles when the call ends
        $this->resetCall();
    }

    public function endCall()
    {
        if ($this->isInCall) {
            try {
            // Get current user ID from appropriate guard
            $currentUserId = null;
            if (Auth::guard('student')->check()) {
                $currentUserId = Auth::guard('student')->id();
            } elseif (Auth::guard('tutor')->check()) {
                $currentUserId = Auth::guard('tutor')->id();
            }
            
            // Log call ended
            $webRTCService = new WebRTCService();
            $webRTCService->logCallActivity(
                $this->isCaller ? $this->callerId : $this->receiverId, 
                $this->callType, 
                'call_ended'
            );
            
            // Notify other party
            $this->dispatch('callEnded', [
                'roomId' => $this->roomId,
                'endedBy' => $currentUserId
            ]);

            // Also dispatch to Socket.IO for cross-tab communication
            $this->dispatch('socketCallEnded', [
                'roomId' => $this->roomId,
                'endedBy' => $currentUserId
            ]);
            } catch (\Exception $e) {
                \Log::error('CallManager::endCall - Error:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            } finally {
                // Always reset call state, even if there was an error
            $this->resetCall();
            }
        }
    }

    private function resetCall()
    {
        $this->isInCall = false;
        $this->callType = null;
        $this->roomId = null;
        $this->callerId = null;
        $this->receiverId = null;
        $this->callerName = null;
        $this->receiverName = null;
        $this->isCaller = false;
        $this->isReceiver = false;
    }

    private function getReceiverType()
    {
        // If the caller is a tutor, the receiver is a student
        // If the caller is a student, the receiver is a tutor
        if (Auth::guard('tutor')->check()) {
            return 'student';
        } elseif (Auth::guard('student')->check()) {
            return 'tutor';
        }
        return 'student'; // Default fallback
    }

    private function getUserProfilePicture($userId, $userType)
    {
        try {
            if ($userType === 'student') {
                $user = \App\Models\Student::find($userId);
            } else {
                $user = \App\Models\Tutor::find($userId);
            }
            
            $profilePicture = $user ? $user->profile_picture : null;
            
            
            return $profilePicture;
        } catch (\Exception $e) {
            \Log::error('Error getting user profile picture:', ['userId' => $userId, 'userType' => $userType, 'error' => $e->getMessage()]);
            return null;
        }
    }

    public function render()
    {
        return view('livewire.call-manager');
    }
}
