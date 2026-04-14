<div wire:ignore.self>
    <!-- Call Interface -->
    @if($isInCall)
        <div class="call-overlay" id="call-overlay">
            <!-- Global Hidden Media Players (Ensures persistent audio/video playback during UI swaps) -->
            <video id="globalRemoteMedia" autoplay playsinline style="display: none;"></video>
            <video id="globalLocalMedia" autoplay muted playsinline style="display: none;"></video>

            <div class="call-container">
                <!-- Call Header -->
                <div class="call-header">
                    <h3>
                        @if(isset($callType) && $callType === 'video')
                            Video Call
                        @else
                            Voice Call
                        @endif
                    </h3>
                    <p>
                        @if(isset($isCaller) && $isCaller)
                            Calling {{ $receiverName ?? 'User' }}
                        @else
                            {{ $receiverName ?? 'Someone' }} is calling you
                        @endif
                    </p>
                </div>

                <!-- End Call Notification -->
                <div class="end-call-notification" id="endCallNotification" style="display: none;">
                    <div class="notification-content">
                        <div class="notification-icon">📞</div>
                        <div class="notification-text">
                            <h3>Call Ended</h3>
                            <p id="endCallMessage">The call has been ended.</p>
                        </div>
                    </div>
                </div>

                <!-- Video/Audio Elements -->
                <div class="media-container" id="mediaContainer" wire:ignore>
                    <div class="video-grid" id="videoGrid" style="display: none;">
                        <div class="local-video-container">
                            <video id="localVideo" autoplay muted playsinline style="display: none;"></video>
                            <div class="profile-placeholder" id="localProfilePlaceholder" style="display: flex;">
                                @if(isset($callerProfilePicture) && $callerProfilePicture)
                                    <img src="{{ asset('storage/' . $callerProfilePicture) }}" alt="You" class="profile-image"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                         onload="this.style.display='block'; this.nextElementSibling.style.display='none';"
                                         style="display: block;">
                                    <div class="profile-initials" style="display: none;">{{ strtoupper(substr($callerName ?? 'You', 0, 2)) }}</div>
                                @else
                                    <div class="profile-initials">{{ strtoupper(substr($callerName ?? 'You', 0, 2)) }}</div>
                                @endif
                            </div>
                            <div class="video-label">You</div>
                        </div>
                        <div class="remote-video-container">
                            <video id="remoteVideo" autoplay playsinline style="display: none;"></video>
                            <div class="profile-placeholder" id="remoteProfilePlaceholder" style="display: flex;">
                                @if(isset($isCaller) && $isCaller)
                                    @if(isset($receiverProfilePicture) && $receiverProfilePicture)
                                        <img src="{{ asset('storage/' . $receiverProfilePicture) }}" alt="{{ $receiverName ?? 'User' }}" class="profile-image"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                             onload="this.style.display='block'; this.nextElementSibling.style.display='none';"
                                             style="display: block;">
                                        <div class="profile-initials" style="display: none;">{{ strtoupper(substr($receiverName ?? 'RU', 0, 2)) }}</div>
                                    @else
                                        <div class="profile-initials">{{ strtoupper(substr($receiverName ?? 'RU', 0, 2)) }}</div>
                                    @endif
                                @else
                                    @if(isset($callerProfilePicture) && $callerProfilePicture)
                                        <img src="{{ asset('storage/' . $callerProfilePicture) }}" alt="{{ $callerName ?? 'User' }}" class="profile-image"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                             onload="this.style.display='block'; this.nextElementSibling.style.display='none';"
                                             style="display: block;">
                                        <div class="profile-initials" style="display: none;">{{ strtoupper(substr($callerName ?? 'CU', 0, 2)) }}</div>
                                    @else
                                        <div class="profile-initials">{{ strtoupper(substr($callerName ?? 'CU', 0, 2)) }}</div>
                                    @endif
                                @endif
                            </div>
                            <div class="video-label" id="remoteVideoLabel">
                                User
                            </div>
                        </div>
                    </div>

                    <div class="audio-call-interface" id="audioCallInterface" style="display: none;">
                        <audio id="remoteAudio" autoplay playsinline style="display: none;"></audio>
                        <div class="caller-avatar">
                            <div class="avatar-circle" id="audioCallAvatar">U</div>
                        </div>
                        <div class="call-status">
                            <div class="pulse-ring"></div>
                        </div>
                    </div>
                </div>

                <!-- Call Controls -->
                <div class="call-controls">
                    <button class="control-btn mute-btn" id="muteBtn" title="Mute">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                            <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                        </svg>
                    </button>

                    @if(isset($callType) && $callType === 'video')
                        <button class="control-btn camera-btn" id="cameraBtn" title="Toggle Camera">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/>
                            </svg>
                        </button>
                    @endif

                    <button class="control-btn screen-share-btn" id="screenShareBtn" title="Share Screen">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20 18c1.1 0 1.99-.9 1.99-2L22 5c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v11c0 1.1.9 2 2 2H0c0 1.1.9 2 2 2h20c1.1 0 2-.9 2-2h-4zM4 5h16v11H4V5zm8 14c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z"/>
                        </svg>
                    </button>

                    <button class="control-btn end-call-btn" wire:click="endCall" title="End Call">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 9c-1.6 0-3.15.25-4.6.72v3.1c0 .39-.23.74-.56.9-.98.49-1.87 1.12-2.66 1.85-.18.18-.43.28-.7.28-.28 0-.53-.11-.71-.29L.29 13.08c-.18-.17-.29-.42-.29-.7 0-.28.11-.53.29-.71C3.34 8.78 7.46 7 12 7s8.66 1.78 11.71 4.67c.18.18.29.43.29.71 0 .28-.11.53-.29.71l-2.48 2.48c-.18.18-.43.29-.71.29-.27 0-.52-.11-.7-.28-.79-.74-1.69-1.36-2.67-1.85-.33-.16-.56-.5-.56-.9v-3.1C15.15 9.25 13.6 9 12 9z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif


    <!-- Incoming Call Modal -->
    <div class="incoming-call-modal" id="incomingCallModal" style="display: none;" wire:ignore>
        <div class="modal-content">
            <div class="caller-info">
                <div class="caller-avatar">
                    <div class="avatar-circle" id="incomingCallerAvatar"></div>
                </div>
                <h3 id="incomingCallerName"></h3>
                <p id="incomingCallType"></p>
            </div>
            <div class="incoming-call-controls">
                <button class="answer-btn" id="answerCallBtn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                    </svg>
                    Answer
                </button>
                <button class="decline-btn" id="declineCallBtn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 9c-1.6 0-3.15.25-4.6.72v3.1c0 .39-.23.74-.56.9-.98.49-1.87 1.12-2.66 1.85-.18.18-.43.28-.7.28-.28 0-.53-.11-.71-.29L.29 13.08c-.18-.17-.29-.42-.29-.7 0-.28.11-.53.29-.71C3.34 8.78 7.46 7 12 7s8.66 1.78 11.71 4.67c.18.18.29.43.29.71 0 .28-.11.53-.29.71l-2.48 2.48c-.18.18-.43.29-.71.29-.27 0-.52-.11-.7-.28-.79-.74-1.69-1.36-2.67-1.85-.33-.16-.56-.5-.56-.9v-3.1C15.15 9.25 13.6 9 12 9z"/>
                    </svg>
                    Decline
                </button>
            </div>
        </div>
    </div>

    <div wire:ignore>
    <style>
        .call-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .call-container {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            max-width: 800px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .call-header h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .call-header p {
            color: #666;
            margin-bottom: 2rem;
        }

        .video-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .local-video-container,
        .remote-video-container {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            background: #f0f0f0;
        }

        .local-video-container {
            grid-column: 2;
            grid-row: 1;
        }

        .remote-video-container {
            grid-column: 1;
            grid-row: 1;
        }

        video {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 15px;
            background: #000;
        }

        video:not([srcObject]) {
            background: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
        }

        video:not([srcObject])::before {
            content: "No video stream";
        }

        .profile-placeholder {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            z-index: 10;
            border-radius: 50%;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }

        .profile-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .profile-initials {
            font-size: 4rem;
            font-weight: bold;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .video-label {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
        }

        .audio-call-interface {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
        }

        .caller-avatar {
            margin-bottom: 1rem;
        }

        .avatar-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
        }

        .call-status {
            position: relative;
        }

        .pulse-ring {
            width: 60px;
            height: 60px;
            border: 3px solid #4ecdc4;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(78, 205, 196, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(78, 205, 196, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(78, 205, 196, 0);
            }
        }

        .call-controls {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .control-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .mute-btn {
            background: #6c757d;
        }

        .mute-btn:hover {
            background: #5a6268;
            transform: scale(1.1);
        }

        .camera-btn {
            background: #17a2b8;
        }

        .camera-btn:hover {
            background: #138496;
            transform: scale(1.1);
        }

        .end-call-btn {
            background: #dc3545;
        }

        .end-call-btn:hover {
            background: #c82333;
            transform: scale(1.1);
        }

        .screen-share-btn {
            background: #28a745;
        }

        .screen-share-btn:hover {
            background: #218838;
            transform: scale(1.1);
        }

        .screen-share-btn.active {
            background: #dc3545;
        }

        .screen-share-btn.active:hover {
            background: #c82333;
        }

        .incoming-call-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .caller-info {
            margin-bottom: 2rem;
        }

        .incoming-call-controls {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .answer-btn,
        .decline-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .answer-btn {
            background: #28a745;
            color: white;
        }

        .answer-btn:hover {
            background: #218838;
            transform: scale(1.05);
        }

        .decline-btn {
            background: #dc3545;
            color: white;
        }

        .decline-btn:hover {
            background: #c82333;
            transform: scale(1.05);
        }

        /* End Call Notification Styles */
        .end-call-notification {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.9);
            border-radius: 15px;
            padding: 2rem;
            z-index: 1000;
            animation: fadeInScale 0.3s ease-out;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .notification-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            color: white;
        }

        .notification-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            animation: pulse 1s ease-in-out infinite;
        }

        .notification-text h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #ff6b6b;
            font-weight: 600;
        }

        .notification-text p {
            font-size: 1rem;
            color: #e0e0e0;
            margin: 0;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
    </style>

    <script>
    const MENTORHUB_ICE_SERVERS = @json(config('webrtc.ice_servers'));
    let localStream = null;
    let remoteStream = null;
    let peerConnection = null;
    let isMuted = false;
    let isCameraOff = false;
    let isScreenSharing = false;
    let screenStream = null;
    let retryTimeoutId = null;
    let noOfferTimeoutId = null;
    let disconnectedTimeoutId = null;
    let callerOfferFallbackTimeoutId = null;
    let pendingIceCandidates = [];
    let retryCount = 0;
    const MAX_RETRIES = 3;
    let lastRetryTime = 0;
    const RETRY_DEBOUNCE_MS = 3000;
    window.currentIncomingCall = window.currentIncomingCall || null;
    window.currentActiveRoomId = window.currentActiveRoomId || null;
    let isCallAnswered = false;
    let pendingOffer = null;
    let pendingRoomIdForOffer = null;

    /** Caller/receiver role for WebRTC (do not rely on @this inside async Pusher handlers). */
    window.mentorHubWebRtcIsCaller = window.mentorHubWebRtcIsCaller ?? false;

    /**
     * Authoritative call type for getUserMedia / SDP (video vs voice).
     * Set from socket and Livewire payloads so the caller never falls through to stale @this.callType.
     */
    window.mentorHubWebRtcCallType = window.mentorHubWebRtcCallType ?? null;

    function normalizeCallType(value) {
        const normalized = String(value || '').trim().toLowerCase();
        return normalized === 'video' ? 'video' : 'voice';
    }

    function syncMentorHubWebRtcCallTypeFromPayload(data) {
        data = normalizePayload(data);
        if (data && data.callType !== undefined && data.callType !== null) {
            window.mentorHubWebRtcCallType = normalizeCallType(data.callType);
        }
    }

    // Unique session ID to isolate WebRTC instances and prevent identical tabs from accepting their own signals
    if (!window.webrtcSessionId) {
        window.webrtcSessionId = Math.random().toString(36).substring(2, 15);
    }


    function normalizePayload(data) {
        if (Array.isArray(data) && data.length > 0) {
            return data[0];
        }
        return data;
    }

    function normalizeSessionDescription(desc, fallbackType = 'offer') {
        desc = normalizePayload(desc);

        if (!desc) return null;

        if (typeof desc === 'string') {
            try {
                desc = JSON.parse(desc);
            } catch (e) {
                desc = { type: fallbackType, sdp: desc };
            }
        }

        let sdp = String(desc.sdp || '');

        if ((sdp.startsWith('"') && sdp.endsWith('"')) || (sdp.startsWith("'") && sdp.endsWith("'"))) {
            sdp = sdp.slice(1, -1);
        }

        const startIndex = sdp.indexOf('v=0');
        if (startIndex > 0) {
            sdp = sdp.slice(startIndex);
        }

        sdp = sdp
            .replace(/\u0000/g, '')
            .replace(/\\\\r\\\\n/g, '\r\n')
            .replace(/\\r\\n/g, '\r\n')
            .replace(/\\\\n/g, '\n')
            .replace(/\\n/g, '\n')
            .replace(/\r?\n/g, '\r\n')
            .trim();

        if (!sdp.endsWith('\r\n')) {
            sdp += '\r\n';
        }

        return {
            type: desc.type || fallbackType,
            sdp: sdp
        };
    }

    function toPlainRTCSessionDescription(desc) {
        const normalized = normalizeSessionDescription(desc, desc?.type || 'offer');
        if (!normalized) return null;
        return {
            type: normalized.type ?? null,
            sdp: normalized.sdp ?? null
        };
    }

    function toPlainIceCandidate(candidate) {
        if (!candidate) return null;

        candidate = normalizePayload(candidate);

        return {
            candidate: candidate.candidate ?? null,
            sdpMid: candidate.sdpMid ?? null,
            sdpMLineIndex: candidate.sdpMLineIndex ?? null,
            usernameFragment: candidate.usernameFragment ?? null
        };
    }

    function resolveRoomId(data = null, explicitFallback = null) {
        data = normalizePayload(data);

        const resolved =
            explicitFallback ||
            data?.roomId ||
            window.currentIncomingCall?.roomId ||
            window.currentActiveRoomId ||
            null;

        console.log('Resolved roomId:', resolved, 'from payload:', data);
        return resolved;
    }

    function getCurrentCallType(data = null) {
        data = normalizePayload(data);
        const resolved = (
            data?.callType ||
            window.mentorHubWebRtcCallType ||
            window.currentIncomingCall?.callType ||
            @this.callType ||
            'voice'
        );
        return normalizeCallType(resolved);
    }

    function getSocketClient() {
        return window.chatSocket ? window.chatSocket.socket : null;
    }

    function clearRetryTimers() {
        if (retryTimeoutId) {
            clearTimeout(retryTimeoutId);
            retryTimeoutId = null;
        }

        if (noOfferTimeoutId) {
            clearTimeout(noOfferTimeoutId);
            noOfferTimeoutId = null;
        }

        if (disconnectedTimeoutId) {
            clearTimeout(disconnectedTimeoutId);
            disconnectedTimeoutId = null;
        }

        if (callerOfferFallbackTimeoutId) {
            clearTimeout(callerOfferFallbackTimeoutId);
            callerOfferFallbackTimeoutId = null;
        }
    }

    async function flushPendingIceCandidates() {
        if (!peerConnection || !peerConnection.remoteDescription) {
            return;
        }

        while (pendingIceCandidates.length > 0) {
            const candidate = pendingIceCandidates.shift();

            try {
                await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                console.log('Queued ICE candidate added successfully');
            } catch (error) {
                console.error('Error adding queued ICE candidate:', error);
            }
        }
    }

    document.addEventListener('livewire:init', () => {
        console.log('=== CALLMANAGER COMPONENT INITIALIZED ===');
        console.log('Current user ID:', window.currentUserId);
        console.log('Current user type:', window.currentUserType);
        console.log('Window location:', window.location.href);

        if (window.chatSocket) {
            console.log('Socket client found:', window.chatSocket);
            console.log('Socket connected:', window.chatSocket.socket?.connected);
            console.log('Socket ID:', window.chatSocket.socket?.id);
        } else {
            console.log('Socket client not ready yet. Waiting for socket-client.js initialization...');
        }

        Livewire.on('callIncoming', (data) => {
            data = normalizePayload(data);
            console.log('Incoming call received:', data);
            showIncomingCallModal(data);
        });

        // Do NOT call @this.handleInitiateCall from JS here.
        // The Livewire PHP listener already handles 'initiateCall', and calling it again
        // from JS creates duplicate rooms/events and breaks signaling in production.

        window.handleCallInitiation = function(callData) {
            callData = normalizePayload(callData);
            console.log('=== DIRECT CALL INITIATION ===');
            console.log('Call data received directly:', callData);

            if (!callData || typeof callData !== 'object') {
                console.error('Invalid call data received directly');
                return;
            }

            if (!callData.roomId || !callData.callType || !callData.receiverId) {
                console.error('Missing required fields in call data:', callData);
                return;
            }

            window.currentActiveRoomId = callData.roomId;
            window.mentorHubWebRtcIsCaller = true;
            syncMentorHubWebRtcCallTypeFromPayload(callData);
            sendCallToSocket(callData);
            showCallingInterface(callData);
        };

        Livewire.on('callInitiationDirect', (data) => {
            data = normalizePayload(data);
            console.log('=== CALL INITIATION DIRECT EVENT ===');
            console.log('Direct event data:', data);

            if (window.handleCallInitiation) {
                window.handleCallInitiation(data);
            }
        });

        document.addEventListener('socket:incoming-call-global', (event) => {
            const data = normalizePayload(event.detail);
            console.log('Global incoming call event received:', data);

            if (isCallAnswered || (window.currentActiveRoomId && window.currentActiveRoomId === data.roomId)) {
                console.log('Ignoring redundant global incoming call packet (already answered or active).');
                return;
            }

            if (data.receiverId == window.currentUserId && data.receiverType == window.currentUserType) {
                window.currentIncomingCall = data;
                window.currentActiveRoomId = data.roomId || window.currentActiveRoomId;
                syncMentorHubWebRtcCallTypeFromPayload(data);
                @this.call('handleIncomingCall', data);
                showIncomingCallModal(data);
            }
        });

        Livewire.on('callAnswered', (data) => {
            data = normalizePayload(data);
            console.log('Call answered:', data);
            // Caller does NOT need to re-initialize here.
        });

        Livewire.on('callEnded', (data) => {
            data = normalizePayload(data);
            console.log('Call ended:', data);
            endCall();
        });

        Livewire.on('initializeWebRTC', (data) => {
            data = normalizePayload(data);
            console.log('Initializing WebRTC:', data);

            const roomId = resolveRoomId(data);
            if (!roomId) {
                console.error('initializeWebRTC: missing roomId');
                return;
            }

            window.mentorHubWebRtcIsCaller = !!data.isCaller;
            syncMentorHubWebRtcCallTypeFromPayload(data);
            initializeCall(roomId);
        });

        Livewire.on('sendCallToReceiver', (data) => {
            data = normalizePayload(data);
            console.log('=== sendCallToReceiver EVENT RECEIVED ===');
            console.log('Processed data:', data);

            if (!data || typeof data !== 'object') {
                console.error('Invalid data received:', data);
                return;
            }

            if (!data.roomId) {
                console.error('Missing roomId in call data');
                return;
            }

            if (!data.receiverType) {
                data.receiverType = 'student';
            }

            window.currentActiveRoomId = data.roomId;
            window.mentorHubWebRtcIsCaller = true;
            syncMentorHubWebRtcCallTypeFromPayload(data);
            sendCallToSocket(data);

            if (@this.isCaller) {
                showCallingInterface(data);
            }
        });

        Livewire.on('socketCallAnswered', (data) => {
            data = normalizePayload(data);
            console.log('Sending call answered to Socket.IO:', data);
            sendCallAnsweredToSocket(data);
        });

        Livewire.on('socketCallDeclined', (data) => {
            data = normalizePayload(data);
            console.log('Sending call declined to Socket.IO:', data);
            sendCallDeclinedToSocket(data);
        });

        Livewire.on('socketCallEnded', (data) => {
            data = normalizePayload(data);
            console.log('Sending call ended to Socket.IO:', data);
            sendCallEndedToSocket(data);
        });
    });

    if (!window.callEventListenersAdded) {
        window.callEventListenersAdded = true;

        document.addEventListener('socket:*', (event) => {
        console.log('=== SOCKET EVENT RECEIVED ===');
        console.log('Event type:', event.type);
        console.log('Event detail:', event.detail);
        console.log('Current user ID:', window.currentUserId);
        console.log('Current user type:', window.currentUserType);
    });

    document.addEventListener('socket:incoming-call', (event) => {
        const data = normalizePayload(event.detail);
        console.log('=== INCOMING CALL RECEIVED FROM SOCKET-CLIENT.JS ===');
        console.log('Event detail:', data);

        if (isCallAnswered || (window.currentActiveRoomId && window.currentActiveRoomId === data.roomId)) {
            console.log('Ignoring redundant incoming call packet (already answered or active).');
            return;
        }

        if (data.receiverId == window.currentUserId && data.receiverType == window.currentUserType) {
            window.currentIncomingCall = data;
            window.currentActiveRoomId = data.roomId || window.currentActiveRoomId;
            syncMentorHubWebRtcCallTypeFromPayload(data);
            @this.call('handleIncomingCall', data);
            showIncomingCallModal(data);
        }
    });

    document.addEventListener('socket:incoming-call-debug', (event) => {
        const data = normalizePayload(event.detail);
        console.log('=== INCOMING CALL DEBUG RECEIVED ===');
        console.log('Debug event detail:', data);

        if (isCallAnswered || (window.currentActiveRoomId && window.currentActiveRoomId === data.roomId)) {
            return;
        }

        if (data.receiverId == window.currentUserId && data.receiverType == window.currentUserType) {
            window.currentIncomingCall = data;
            window.currentActiveRoomId = data.roomId || window.currentActiveRoomId;
            syncMentorHubWebRtcCallTypeFromPayload(data);
            @this.call('handleIncomingCall', data);
            showIncomingCallModal(data);
        }
    });

    document.addEventListener('socket:call-answered', async (event) => {
        const data = normalizePayload(event.detail);
        console.log('Call answered received from socket-client.js:', data);

        if (data?.roomId) {
            window.currentActiveRoomId = data.roomId;
        }

        // Callee has joined the call channel; caller must send SDP offer only now (Pusher does not queue for late subscribers).
        if (!window.mentorHubWebRtcIsCaller) {
            return;
        }

        const roomId = resolveRoomId(data);
        if (!roomId) {
            return;
        }

        const trySendOfferWhenReady = async () => {
            for (let i = 0; i < 50; i++) {
                if (!window.mentorHubWebRtcIsCaller) {
                    return;
                }
                if (peerConnection) {
                    if (peerConnection.localDescription || peerConnection.signalingState === 'have-local-offer') {
                        return;
                    }
                    await sendCallerWebRtcOffer(roomId);
                    return;
                }
                await new Promise((r) => setTimeout(r, 100));
            }
            console.error('Call answered: caller peer connection never became ready; offer not sent.');
        };

        await trySendOfferWhenReady();
    });

    document.addEventListener('socket:call-ended', (event) => {
        const data = normalizePayload(event.detail);
        console.log('Call ended received from socket-client.js:', data);
        const me = window.currentUserId != null ? Number(window.currentUserId) : null;
        const endedBy = data?.endedBy != null ? Number(data.endedBy) : null;
        if (me !== null && endedBy !== null && endedBy === me) {
            return;
        }
        showEndCallNotification('The other person ended the call.');
        setTimeout(() => endCall(), 1000);
    });

    document.addEventListener('socket:call-declined', (event) => {
        const data = normalizePayload(event.detail);
        console.log('Call declined received from socket-client.js:', data);
        showEndCallNotification('The call was declined.');
        setTimeout(() => endCall(), 1000);
    });

    document.addEventListener('socket:webrtc-offer', (event) => {
        const data = normalizePayload(event.detail);
        
        // Split-Brain Anti-Reflection Shield: The Caller generates have-local-offer. 
        // If we receive an offer while in this state, it's our own broadcast echoing back from Pusher.
        if (peerConnection && peerConnection.signalingState === 'have-local-offer') {
            console.log('Ignoring local mirrored WebRTC offer because we are the Caller.');
            return;
        }

        if (data.sessionId === window.webrtcSessionId) {
            console.log('Ignoring local mirrored WebRTC offer via sessionId.');
            return;
        }
        
        console.log('WebRTC offer received from socket-client.js:', data);

        const roomId = resolveRoomId(data);
        handleOffer(data.offer, roomId);
    });

    document.addEventListener('socket:webrtc-answer', (event) => {
        const data = normalizePayload(event.detail);
        
        // The sender of the Answer transitions natively to 'stable' after setLocalDescription.
        // It must NOT process the echoing answer.
        if (peerConnection && peerConnection.signalingState === 'stable') {
            console.log('Ignoring local mirrored WebRTC answer.');
            return;
        }

        if (data.sessionId === window.webrtcSessionId) {
            console.log('Ignoring local mirrored WebRTC answer via sessionId.');
            return;
        }
        console.log('WebRTC answer received from socket-client.js:', data);

        const roomId = resolveRoomId(data);
        handleAnswer(data.answer, roomId);
    });

    document.addEventListener('socket:webrtc-ice-candidate', (event) => {
        const data = normalizePayload(event.detail);
        if (data.sessionId === window.webrtcSessionId) {
            return; // Ignore local mirrored ICE candidates
        }
        console.log('WebRTC ICE candidate received from socket-client.js:', data);

        if (!data?.candidate) {
            console.error('Missing ICE candidate payload');
            return;
        }

        const roomId = resolveRoomId(data);

        if (window.currentActiveRoomId && roomId && roomId !== window.currentActiveRoomId) {
            console.warn('Ignoring ICE candidate for stale room:', roomId, 'active room:', window.currentActiveRoomId);
            return;
        }

        handleIceCandidate(data.candidate, roomId);
    });
    } // End of window.callEventListenersAdded check

    function showIncomingCallModal(data) {
        data = normalizePayload(data);
        console.log('Showing incoming call modal with data:', data);

        syncMentorHubWebRtcCallTypeFromPayload(data);

        const modal = document.getElementById('incomingCallModal');
        const avatar = document.getElementById('incomingCallerAvatar');
        const name = document.getElementById('incomingCallerName');
        const type = document.getElementById('incomingCallType');

        const callerName = data.callerName || 'Unknown';
        const callType = data.callType || 'video';

        avatar.textContent = callerName.charAt(0);
        name.textContent = callerName;
        type.textContent = callType === 'video' ? 'Incoming Video Call' : 'Incoming Voice Call';

        modal.style.display = 'flex';
        window.currentIncomingCall = data;
        window.currentActiveRoomId = data.roomId || window.currentActiveRoomId;

        document.getElementById('answerCallBtn').onclick = async () => {
            modal.style.display = 'none';
            isCallAnswered = true;
            window.mentorHubWebRtcIsCaller = false;

            const roomId = resolveRoomId(data);
            console.log('Answering call, resolved roomId:', roomId);

            if (!roomId) {
                console.error('Answer call failed: missing roomId');
                return;
            }

            if (window.chatSocket && typeof window.chatSocket.joinCallRoom === 'function') {
                try {
                    await window.chatSocket.joinCallRoom(roomId);
                } catch (e) {
                    console.error('joinCallRoom before answer failed:', e);
                }
            }

            const remoteAudio = document.getElementById('remoteAudio');
            const globalRemoteMedia = document.getElementById('globalRemoteMedia');
            if (remoteAudio) remoteAudio.play().catch(() => {});
            if (globalRemoteMedia) globalRemoteMedia.play().catch(() => {});

            @this.call('answerCall');

            await initializeCall(roomId);
            console.log('WebRTC Initialized. Waiting for offer from caller...');
            if (pendingOffer) {
                console.log('Processing buffered offer now that call is answered');
                handleOffer(pendingOffer, pendingRoomIdForOffer || roomId);
                pendingOffer = null;
                pendingRoomIdForOffer = null;
            }
        };

        document.getElementById('declineCallBtn').onclick = () => {
            modal.style.display = 'none';
            window.mentorHubWebRtcCallType = null;
            @this.call('declineCall');
        };
    }

    function showCallingInterface(data) {
        data = normalizePayload(data);
        console.log('Showing calling interface for caller:', data);

        if (data?.roomId) {
            window.currentActiveRoomId = data.roomId;
        }

        const callOverlay = document.getElementById('call-overlay');
        if (callOverlay) {
            callOverlay.style.display = 'flex';
        }
    }

    async function checkCameraPermissions() {
        try {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('getUserMedia is not supported in this browser');
            }

            if (navigator.permissions) {
                const currentCallType = getCurrentCallType();

                if (currentCallType === 'video') {
                    const cameraPermission = await navigator.permissions.query({ name: 'camera' });
                    console.log('Camera permission:', cameraPermission.state);
                    if (cameraPermission.state === 'denied') {
                        throw new Error('Camera access denied');
                    }
                }

                const microphonePermission = await navigator.permissions.query({ name: 'microphone' });
                console.log('Microphone permission:', microphonePermission.state);
                if (microphonePermission.state === 'denied') {
                    throw new Error('Microphone access denied');
                }
            }

            return true;
        } catch (error) {
            console.error('Permission check failed:', error);
            return false;
        }
    }

    /**
     * Send the SDP offer only after the callee has answered and subscribed to the call channel,
     * so Pusher does not drop the signaling message.
     */
    async function sendCallerWebRtcOffer(roomId) {
        roomId = resolveRoomId({ roomId }, roomId);
        if (!roomId) {
            console.error('sendCallerWebRtcOffer: missing roomId');
            return;
        }
        if (!peerConnection) {
            console.warn('sendCallerWebRtcOffer: peer connection not ready');
            return;
        }
        if (peerConnection.signalingState === 'have-local-offer') {
            console.log('Caller offer already sent (have-local-offer); skipping.');
            return;
        }
        if (peerConnection.localDescription) {
            console.log('Caller local description already set; skipping duplicate offer.');
            return;
        }

        try {
            const currentCallType = getCurrentCallType();
            console.log('Creating WebRTC offer as caller (callee is on the call channel)...');

            const offer = await peerConnection.createOffer({
                offerToReceiveAudio: true,
                offerToReceiveVideo: currentCallType === 'video'
            });

            await peerConnection.setLocalDescription(offer);
            console.log('Local description set:', offer);

            const globalSocket = getSocketClient();
            if (globalSocket) {
                globalSocket.emit('webrtc_offer', {
                    roomId,
                    offer: toPlainRTCSessionDescription(offer),
                    from: window.currentUserId,
                    sessionId: window.webrtcSessionId
                });
                console.log('✅ Offer sent to signaling server');

                if (noOfferTimeoutId) {
                    clearTimeout(noOfferTimeoutId);
                }
                noOfferTimeoutId = setTimeout(() => {
                    if (peerConnection && peerConnection.signalingState !== 'stable') {
                        console.log('⚠️ Signaling not stable after 60s, receiver may not have completed negotiation.');
                    }
                }, 60000);
            }
        } catch (error) {
            console.error('sendCallerWebRtcOffer failed:', error);
        }
    }

    async function initializeCall(roomId) {
        try {
            roomId = resolveRoomId({ roomId }, roomId);
            const currentCallType = getCurrentCallType();

            console.log('Initializing call with roomId:', roomId);
            console.log('Call type:', currentCallType);

            if (!roomId) {
                console.error('initializeCall aborted: missing roomId');
                return;
            }

            if (peerConnection && peerConnection.signalingState !== 'closed' && window.currentActiveRoomId === roomId) {
                console.log('WebRTC is already initialized for this room! Preventing duplicate initialization destruction.');
                return;
            }

            // Dynamic UI adjustment based on callType (circumvents wire:ignore limitations)
            const videoGrid = document.getElementById('videoGrid');
            const audioCallInterface = document.getElementById('audioCallInterface');

            if (currentCallType === 'video') {
                if (videoGrid) videoGrid.style.display = 'grid';
                if (audioCallInterface) audioCallInterface.style.display = 'none';
            } else {
                if (videoGrid) videoGrid.style.display = 'none';
                if (audioCallInterface) audioCallInterface.style.display = 'flex';
                
                const audioCallAvatar = document.getElementById('audioCallAvatar');
                if (audioCallAvatar) {
                    let dispName = window.currentIncomingCall ? window.currentIncomingCall.callerName : 'U';
                    audioCallAvatar.textContent = dispName ? dispName.charAt(0).toUpperCase() : 'U';
                }
            }

            clearRetryTimers();
            window.currentActiveRoomId = roomId;
            pendingIceCandidates = [];

            {
                const joinSock = getSocketClient();
                if (window.chatSocket && typeof window.chatSocket.joinCallRoom === 'function') {
                    try {
                        await window.chatSocket.joinCallRoom(roomId);
                        console.log('Joined call room for signaling (before getUserMedia):', roomId);
                    } catch (e) {
                        console.error('Early joinCallRoom failed:', e);
                    }
                } else if (joinSock) {
                    joinSock.emit('join_call_room', { roomId });
                    console.log('Joined call room for signaling (before getUserMedia):', roomId);
                }
            }

            if (peerConnection) {
                try {
                    peerConnection.ontrack = null;
                    peerConnection.onicecandidate = null;
                    peerConnection.onconnectionstatechange = null;
                    peerConnection.oniceconnectionstatechange = null;
                    peerConnection.close();
                } catch (e) {}
                peerConnection = null;
            }

            setTimeout(() => {
                console.log('Initial check for remote video stream');
                checkRemoteVideoStream();
            }, 500);

            // Voice-only: profile placeholders in hidden video grid. For video calls, these hide local/remote
            // preview and break the camera UI — skip entirely when callType is video.
            if (currentCallType !== 'video') {
                setTimeout(() => {
                    console.log('Force showing remote profile placeholder initially');
                    forceShowRemoteProfileAlways();
                }, 100);

                setTimeout(() => {
                    console.log('Showing remote profile placeholder by default');
                    forceShowRemoteProfileAlways();
                }, 200);

                setTimeout(() => forceShowBothProfiles(), 300);
            }

            setTimeout(() => reloadProfileImages(), 2000);
            setTimeout(() => reloadLocalProfileImage(), 1000);

            const hasPermissions = await checkCameraPermissions();
            if (!hasPermissions) {
                console.warn('Permission check failed, but continuing with getUserMedia...');
            }

            const constraints = {
                video: currentCallType === 'video'
                    ? {
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        facingMode: 'user'
                    }
                    : false,
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true
                }
            };

            console.log('Media constraints:', constraints);

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('getUserMedia is not supported in this browser');
            }

            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
                localStream = null;
            }

            try {
                localStream = await navigator.mediaDevices.getUserMedia(constraints);
            } catch (mediaError) {
                if (currentCallType === 'video' && mediaError && mediaError.name === 'NotReadableError') {
                    console.warn('Primary video getUserMedia failed (NotReadableError). Retrying with audio-only local stream.', mediaError);
                    localStream = await navigator.mediaDevices.getUserMedia({
                        video: false,
                        audio: {
                            echoCancellation: true,
                            noiseSuppression: true
                        }
                    });
                } else {
                    throw mediaError;
                }
            }
            console.log('Local stream obtained:', localStream);
            console.log('Local stream tracks:', localStream.getTracks());

            if (currentCallType === 'video') {
                const localVideo = document.getElementById('localVideo');
                const localProfilePlaceholder = document.getElementById('localProfilePlaceholder');
                if (localVideo) {
                    localVideo.srcObject = localStream;
                    localVideo.muted = true;
                    localVideo.style.display = 'block';
                    if (localProfilePlaceholder) {
                        localProfilePlaceholder.style.display = 'none';
                    }
                    await localVideo.play().catch(() => {});
                    console.log('Local video element updated and playing');
                }
            }

            const globalLocalMedia = document.getElementById('globalLocalMedia');
            if (globalLocalMedia) {
                globalLocalMedia.srcObject = localStream;
                globalLocalMedia.muted = true;
                globalLocalMedia.play().catch(() => {});
            }

            const configuration = {
                iceServers: Array.isArray(MENTORHUB_ICE_SERVERS) && MENTORHUB_ICE_SERVERS.length
                    ? MENTORHUB_ICE_SERVERS
                    : [
                        { urls: 'stun:stun.l.google.com:19302' },
                        { urls: 'stun:stun1.l.google.com:19302' },
                        { urls: 'stun:stun2.l.google.com:19302' }
                    ],
                iceCandidatePoolSize: 10
            };

            peerConnection = new RTCPeerConnection(configuration);

            localStream.getTracks().forEach(track => {
                console.log('Adding track to peer connection:', track.kind, track.label);
                peerConnection.addTrack(track, localStream);
            });

            peerConnection.ontrack = (event) => {
                console.log('=== REMOTE TRACK RECEIVED ===');
                console.log('Event:', event);
                console.log('Streams:', event.streams);
                console.log('Track:', event.track);

                if (!remoteStream) {
                    remoteStream = new MediaStream();
                    console.log('Created persistent remote MediaStream');
                }

                const incomingStream = (event.streams && event.streams.length > 0) ? event.streams[0] : null;
                if (incomingStream) {
                    incomingStream.getTracks().forEach((track) => {
                        const exists = remoteStream.getTracks().some(t => t.id === track.id);
                        if (!exists) {
                            remoteStream.addTrack(track);
                            console.log('Merged incoming stream track into remoteStream:', track.kind, track.id);
                        }
                    });
                }

                if (event.track) {
                    const exists = remoteStream.getTracks().some(t => t.id === event.track.id);
                    if (!exists) {
                        remoteStream.addTrack(event.track);
                        console.log('Merged direct event.track into remoteStream:', event.track.kind, event.track.id);
                    }
                }

                console.log('Remote stream tracks after merge:', remoteStream.getTracks().map(t => ({
                    id: t.id,
                    kind: t.kind,
                    enabled: t.enabled,
                    readyState: t.readyState
                })));

                const hasIncomingVideoTrack = remoteStream.getVideoTracks().some((t) => t.readyState === 'live');
                if (hasIncomingVideoTrack) {
                    const remoteVideo = document.getElementById('remoteVideo');
                    if (remoteVideo) {
                        if (remoteVideo.srcObject !== remoteStream) {
                            remoteVideo.srcObject = remoteStream;
                        }
                        remoteVideo.style.display = 'block';
                        remoteVideo.muted = false;
                        remoteVideo.play().catch(() => {});
                    }
                    hideRemoteProfilePlaceholder();
                    console.log('Incoming live video track detected; forcing remote video render.');
                }

                const track = event.track;
                if (track && !track._mentorHubListenersAttached) {
                    track._mentorHubListenersAttached = true;

                    track.addEventListener('ended', () => {
                        if (track.kind === 'video') showRemoteProfilePlaceholder();
                    });

                    track.addEventListener('mute', () => {
                        if (track.kind === 'video') showRemoteProfilePlaceholder();
                    });

                    track.addEventListener('unmute', () => {
                        if (track.kind === 'video') hideRemoteProfilePlaceholder();
                        if (track.kind === 'audio') {
                            console.log('Audio track explicitly unmuted by WebRTC network');
                            const targetAudio = document.getElementById('remoteAudio');
                            if (targetAudio && targetAudio.paused) {
                                targetAudio.play().catch(e => console.warn('Unmute play error:', e));
                            }
                        }
                    });
                }

                setTimeout(() => checkRemoteVideoStream(), 1000);
                setTimeout(() => checkRemoteVideoStream(), 2000);

                    // Always pipe to the persistent global media object to guarantee audio plays (for VIDEO calls)
                    if (currentCallType === 'video') {
                        const globalRemoteMedia = document.getElementById('globalRemoteMedia');
                        if (globalRemoteMedia) {
                            if (globalRemoteMedia.srcObject !== remoteStream) {
                                globalRemoteMedia.srcObject = remoteStream;
                            }
                            globalRemoteMedia.muted = false;
                            globalRemoteMedia.play().then(() => {
                                console.log('✅ Global Remote media playing successfully');
                            }).catch(e => {
                                console.error('❌ Global Remote media play error:', e);
                                setTimeout(() => globalRemoteMedia.play().catch(() => {}), 1000);
                            });
                        }
                    }

                    // Also bind to the specific UI elements if they exist
                    if (currentCallType === 'video') {
                        const remoteVideo = document.getElementById('remoteVideo');
                        if (remoteVideo) {
                            if (remoteVideo.srcObject !== remoteStream) {
                                remoteVideo.srcObject = remoteStream;
                            }
                            remoteVideo.muted = false;
                            remoteVideo.play().catch(e => {
                                console.error('❌ Remote video UI play error:', e);
                            });
                        }

                        const remoteAudio = document.getElementById('remoteAudio');
                        if (remoteAudio) {
                            if (remoteAudio.srcObject !== remoteStream) {
                                remoteAudio.srcObject = remoteStream;
                            }
                            remoteAudio.muted = false;
                            remoteAudio.volume = 1.0;
                            remoteAudio.play().catch(e => {
                                console.warn('Video-call remoteAudio fallback play error:', e);
                            });
                        }

                        if (!window.videoAudioStatsInterval) {
                            window.videoAudioStatsInterval = setInterval(async () => {
                                if (peerConnection && peerConnection.connectionState === 'connected') {
                                    try {
                                        const stats = await peerConnection.getStats();
                                        let audioInboundFound = false;
                                        stats.forEach(report => {
                                            if (report.type === 'inbound-rtp' && report.kind === 'audio') {
                                                audioInboundFound = true;
                                                console.log(`[Video Stats] Audio Bytes Received: ${report.bytesReceived}, Packets Lost: ${report.packetsLost}`);
                                            }
                                        });
                                        if (!audioInboundFound) {
                                            console.log('[Video Stats] No inbound-rtp audio track established yet.');
                                        }
                                    } catch (err) {
                                        console.warn('[Video Stats] Failed to read getStats():', err);
                                    }
                                }
                            }, 3000);
                        }
                    } else {
                        if (hasIncomingVideoTrack) {
                            console.warn('Voice UI branch detected while remote stream has video; keeping remote video visible.');
                        }
                        const remoteAudio = document.getElementById('remoteAudio');
                        if (remoteAudio) {
                            if (remoteAudio.srcObject !== remoteStream) {
                                remoteAudio.srcObject = remoteStream;
                            }
                            remoteAudio.muted = false;
                            remoteAudio.volume = 1.0;
                            console.log('Attempting to play remoteAudio tag...');
                            remoteAudio.play().then(() => {
                                console.log('✅ Remote audio UI playing successfully in voice call');
                            }).catch(e => {
                                console.error('❌ Remote audio UI play error:', e.name, e.message, e);
                            });
                        } else {
                            console.error('❌ remoteAudio element NOT FOUND in DOM!');
                        }

                        const globalRemoteMedia = document.getElementById('globalRemoteMedia');
                        if (globalRemoteMedia) {
                            if (globalRemoteMedia.srcObject !== remoteStream) {
                                globalRemoteMedia.srcObject = remoteStream;
                            }
                            globalRemoteMedia.muted = false;
                            globalRemoteMedia.volume = 1.0;
                            globalRemoteMedia.play().then(() => {
                                console.log('✅ Global remote media playing (voice fallback)');
                            }).catch(e => {
                                console.warn('Global remote media play (voice):', e);
                                setTimeout(() => globalRemoteMedia.play().catch(() => {}), 500);
                            });
                        }
                        
                        // Set up stats monitoring to confirm if bytes are crossing the network natively
                        if (!window.audioStatsInterval) {
                            window.audioStatsInterval = setInterval(async () => {
                                if (peerConnection && peerConnection.connectionState === 'connected') {
                                    const stats = await peerConnection.getStats();
                                    let audioInboundFound = false;
                                    stats.forEach(report => {
                                        if (report.type === 'inbound-rtp' && report.kind === 'audio') {
                                            audioInboundFound = true;
                                            console.log(`[Stats] Audio Bytes Received: ${report.bytesReceived}, Packets Lost: ${report.packetsLost}`);
                                        }
                                    });
                                    if (!audioInboundFound) {
                                        console.log('[Stats] No inbound-rtp audio track established yet.');
                                    }
                                }
                            }, 3000);
                        }
                    }
            };

            peerConnection.onconnectionstatechange = () => {
                console.log('Connection state:', peerConnection.connectionState);

                if (peerConnection.connectionState === 'connected') {
                    clearRetryTimers();
                    retryCount = 0; // Reset retry counter on successful connection
                    setTimeout(() => ensureRemoteVideoPlaying(), 1000);
                } else if (peerConnection.connectionState === 'failed') {
                    console.log('Connection failed manually checking if we should retry.');
                    // Don't auto-retry aggressively
                }
            };

            peerConnection.oniceconnectionstatechange = () => {
                console.log('ICE connection state:', peerConnection.iceConnectionState);

                if (peerConnection.iceConnectionState === 'connected' || peerConnection.iceConnectionState === 'completed') {
                    clearRetryTimers();
                    retryCount = 0; // Reset retry counter on successful connection
                    setTimeout(() => ensureRemoteVideoPlaying(), 500);
                } else if (peerConnection.iceConnectionState === 'failed') {
                    console.log('ICE connection failed explicitly.');
                }
            };

            peerConnection.onicecandidate = (event) => {
                if (event.candidate) {
                    console.log('Sending ICE candidate:', event.candidate);
                    sendIceCandidate(event.candidate);
                }
            };

            if (window.mentorHubWebRtcIsCaller) {
                console.log('Caller: media and signaling ready; SDP offer is sent when call-answered is received (callee must be on the call channel).');

                // Fallback: in production, if the call-answered event is missed/racy, send an offer once after a short delay.
                // sendCallerWebRtcOffer() is idempotent and will skip duplicates if an offer already exists.
                callerOfferFallbackTimeoutId = setTimeout(async () => {
                    if (!window.mentorHubWebRtcIsCaller) {
                        return;
                    }
                    if (!peerConnection) {
                        return;
                    }
                    if (peerConnection.localDescription || peerConnection.signalingState === 'have-local-offer') {
                        return;
                    }

                    console.warn('Caller fallback triggered: no local offer yet after call init; sending offer now.');
                    await sendCallerWebRtcOffer(roomId);
                }, 7000);
            } else {
                console.log('Waiting for offer as receiver...');
                noOfferTimeoutId = setTimeout(() => {
                    if (peerConnection && peerConnection.signalingState === 'stable' && !peerConnection.remoteDescription) {
                        console.log('⚠️ No offer received after 60s.');
                    }
                }, 60000);
            }
        } catch (error) {
            console.error('Error initializing call:', error);
            let errorMessage = 'Failed to initialize call. ';

            if (error.name === 'NotAllowedError') {
                errorMessage += 'Please allow camera and microphone access.';
            } else if (error.name === 'NotFoundError') {
                errorMessage += 'Camera or microphone not found.';
            } else if (error.name === 'NotSupportedError') {
                errorMessage += 'Your browser does not support video calls.';
            } else {
                errorMessage += 'Please check your camera and microphone permissions.';
            }

            alert(errorMessage);
        }
    }

    if (!window.callClickEventListenersAdded) {
        window.callClickEventListenersAdded = true;

        document.addEventListener('click', (e) => {
            if (e.target.closest('#muteBtn') && localStream) {
            const audioTrack = localStream.getAudioTracks()[0];
            if (audioTrack) {
                audioTrack.enabled = !audioTrack.enabled;
                isMuted = !audioTrack.enabled;
                const btn = e.target.closest('#muteBtn');
                btn.style.background = isMuted ? '#dc3545' : '#6c757d';
            }
        }
    });

    document.addEventListener('click', (e) => {
        if (e.target.closest('#cameraBtn') && localStream) {
            const videoTrack = localStream.getVideoTracks()[0];
            if (videoTrack) {
                videoTrack.enabled = !videoTrack.enabled;
                isCameraOff = !videoTrack.enabled;
                const btn = e.target.closest('#cameraBtn');
                btn.style.background = isCameraOff ? '#dc3545' : '#17a2b8';

                const localVideo = document.getElementById('localVideo');
                const localProfilePlaceholder = document.getElementById('localProfilePlaceholder');

                if (isCameraOff) {
                    localVideo.style.display = 'none';
                    localProfilePlaceholder.style.display = 'flex';
                } else {
                    localVideo.style.display = 'block';
                    localProfilePlaceholder.style.display = 'none';
                }
            }
        }
    });

    document.addEventListener('click', (e) => {
        if (e.target.closest('#screenShareBtn')) {
            if (isScreenSharing) {
                stopScreenShare();
            } else {
                startScreenShare();
            }
        }
    });
    } // End of window.callClickEventListenersAdded check

    async function startScreenShare() {
        try {
            console.log('Starting screen share...');

            screenStream = await navigator.mediaDevices.getDisplayMedia({
                video: {
                    mediaSource: 'screen',
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                },
                audio: true
            });

            const screenShareBtn = document.getElementById('screenShareBtn');
            screenShareBtn.classList.add('active');
            screenShareBtn.title = 'Stop Screen Share';
            isScreenSharing = true;

            if (peerConnection && localStream) {
                const videoTrack = screenStream.getVideoTracks()[0];
                const audioTrack = screenStream.getAudioTracks()[0];

                const sender = peerConnection.getSenders().find(s => s.track && s.track.kind === 'video');
                if (sender) {
                    await sender.replaceTrack(videoTrack);
                }

                if (audioTrack) {
                    const audioSender = peerConnection.getSenders().find(s => s.track && s.track.kind === 'audio');
                    if (audioSender) {
                        await audioSender.replaceTrack(audioTrack);
                    }
                }
            }

            const localVideo = document.getElementById('localVideo');
            if (localVideo) {
                localVideo.srcObject = screenStream;
                await localVideo.play();
            }

            screenStream.getVideoTracks()[0].onended = () => stopScreenShare();
        } catch (error) {
            console.error('Error starting screen share:', error);
            alert('Failed to start screen sharing. Please check your browser permissions.');
        }
    }

    async function stopScreenShare() {
        try {
            if (screenStream) {
                screenStream.getTracks().forEach(track => track.stop());
                screenStream = null;
            }

            const screenShareBtn = document.getElementById('screenShareBtn');
            screenShareBtn.classList.remove('active');
            screenShareBtn.title = 'Share Screen';
            isScreenSharing = false;

            if (peerConnection && localStream) {
                const videoTrack = localStream.getVideoTracks()[0];
                const audioTrack = localStream.getAudioTracks()[0];

                const sender = peerConnection.getSenders().find(s => s.track && s.track.kind === 'video');
                if (sender && videoTrack) {
                    await sender.replaceTrack(videoTrack);
                }

                if (audioTrack) {
                    const audioSender = peerConnection.getSenders().find(s => s.track && s.track.kind === 'audio');
                    if (audioSender) {
                        await audioSender.replaceTrack(audioTrack);
                    }
                }
            }

            const localVideo = document.getElementById('localVideo');
            if (localVideo && localStream) {
                localVideo.srcObject = localStream;
                await localVideo.play();
            }
        } catch (error) {
            console.error('Error stopping screen share:', error);
        }
    }

    function showRemoteProfilePlaceholder() {
        const remoteVideo = document.getElementById('remoteVideo');
        const remoteProfilePlaceholder = document.getElementById('remoteProfilePlaceholder');
        if (remoteVideo && remoteProfilePlaceholder) {
            remoteVideo.style.display = 'none';
            remoteProfilePlaceholder.style.display = 'flex';
        }
    }

    function hideRemoteProfilePlaceholder() {
        const remoteVideo = document.getElementById('remoteVideo');
        const remoteProfilePlaceholder = document.getElementById('remoteProfilePlaceholder');
        if (remoteVideo && remoteProfilePlaceholder) {
            remoteVideo.style.display = 'block';
            remoteProfilePlaceholder.style.display = 'none';
        }
    }

    function checkRemoteVideoStream() {
        const remoteVideo = document.getElementById('remoteVideo');
        const remoteProfilePlaceholder = document.getElementById('remoteProfilePlaceholder');

        if (!remoteVideo || !remoteProfilePlaceholder) {
            console.log('Remote video or profile placeholder elements not found');
            return;
        }

        if (!remoteVideo.srcObject || remoteVideo.srcObject.getTracks().length === 0) {
            showRemoteProfilePlaceholder();
            return;
        }

        const videoTracks = remoteVideo.srcObject.getVideoTracks();
        if (videoTracks.length === 0 || !videoTracks[0].enabled) {
            showRemoteProfilePlaceholder();
        } else {
            hideRemoteProfilePlaceholder();
        }
    }

    function forceShowRemoteProfileAlways() {
        const remoteVideo = document.getElementById('remoteVideo');
        const remoteProfilePlaceholder = document.getElementById('remoteProfilePlaceholder');
        if (remoteVideo && remoteProfilePlaceholder) {
            remoteVideo.style.display = 'none';
            remoteProfilePlaceholder.style.display = 'flex';
        }
    }

    function forceShowRemoteProfile() {
        showRemoteProfilePlaceholder();
    }

    function reloadProfileImages() {
        console.log('Reloading profile images...');
        const localImg = document.querySelector('#localProfilePlaceholder .profile-image');
        const remoteImg = document.querySelector('#remoteProfilePlaceholder .profile-image');

        if (localImg) {
            const src = localImg.src;
            localImg.src = '';
            setTimeout(() => {
                localImg.src = src + '?t=' + Date.now();
            }, 100);
        }

        if (remoteImg) {
            const src = remoteImg.src;
            remoteImg.src = '';
            setTimeout(() => {
                remoteImg.src = src + '?t=' + Date.now();
            }, 100);
        }
    }

    function reloadLocalProfileImage() {
        console.log('Force reloading local profile image...');
        const localImg = document.querySelector('#localProfilePlaceholder .profile-image');
        if (localImg) {
            const src = localImg.src;
            localImg.src = '';
            setTimeout(() => {
                localImg.src = src + '?t=' + Date.now();
            }, 100);
        } else {
            console.log('No local profile image found to reload');
        }
    }

    function forceShowBothProfiles() {
        console.log('Force showing both profile placeholders...');
        const localVideo = document.getElementById('localVideo');
        const localProfilePlaceholder = document.getElementById('localProfilePlaceholder');
        if (localVideo && localProfilePlaceholder) {
            localVideo.style.display = 'none';
            localProfilePlaceholder.style.display = 'flex';
        }

        const remoteVideo = document.getElementById('remoteVideo');
        const remoteProfilePlaceholder = document.getElementById('remoteProfilePlaceholder');
        if (remoteVideo && remoteProfilePlaceholder) {
            remoteVideo.style.display = 'none';
            remoteProfilePlaceholder.style.display = 'flex';
        }
    }

    window.forceShowRemoteProfile = forceShowRemoteProfile;
    window.forceShowRemoteProfileAlways = forceShowRemoteProfileAlways;
    window.checkRemoteVideoStream = checkRemoteVideoStream;
    window.showRemoteProfilePlaceholder = showRemoteProfilePlaceholder;
    window.hideRemoteProfilePlaceholder = hideRemoteProfilePlaceholder;
    window.reloadProfileImages = reloadProfileImages;
    window.reloadLocalProfileImage = reloadLocalProfileImage;
    window.forceShowBothProfiles = forceShowBothProfiles;

    // Non-destructive version: never nullifies srcObject to avoid blank flash/flicker
    function ensureRemoteVideoPlaying() {
        if (getCurrentCallType() === 'video') {
            const globalRemoteMedia = document.getElementById('globalRemoteMedia');
            if (globalRemoteMedia && remoteStream && globalRemoteMedia.srcObject !== remoteStream) {
                globalRemoteMedia.srcObject = remoteStream;
                globalRemoteMedia.play().catch(() => {});
            }

            const remoteAudio = document.getElementById('remoteAudio');
            if (remoteAudio && remoteStream) {
                if (remoteAudio.srcObject !== remoteStream) {
                    remoteAudio.srcObject = remoteStream;
                }
                remoteAudio.muted = false;
                remoteAudio.volume = 1.0;
                remoteAudio.play().catch(() => {});
            }
        } else if (getCurrentCallType() === 'voice') {
            const remoteAudio = document.getElementById('remoteAudio');
            if (remoteAudio && remoteStream) {
                if (remoteAudio.srcObject !== remoteStream) {
                    remoteAudio.srcObject = remoteStream;
                }
                remoteAudio.muted = false;
                remoteAudio.volume = 1.0;
                remoteAudio.play().catch(() => {});
            }
            const globalRemoteMediaVoice = document.getElementById('globalRemoteMedia');
            if (globalRemoteMediaVoice && remoteStream) {
                if (globalRemoteMediaVoice.srcObject !== remoteStream) {
                    globalRemoteMediaVoice.srcObject = remoteStream;
                }
                globalRemoteMediaVoice.muted = false;
                globalRemoteMediaVoice.volume = 1.0;
                globalRemoteMediaVoice.play().catch(() => {});
            }
            return;
        }

        const remoteVideo = document.getElementById('remoteVideo');
        if (!remoteVideo) return;

        if (remoteStream) {
            // Only re-assign srcObject if it's not already set to the current stream
            if (remoteVideo.srcObject !== remoteStream) {
                remoteVideo.srcObject = remoteStream;
            }
            remoteVideo.play().catch(() => {});
            checkRemoteVideoStream();
        }
    }

    // Keep the old name as an alias for backward compatibility
    function forceRefreshRemoteVideo() {
        ensureRemoteVideoPlaying();
    }

    function checkWebRTCConnection() {
        if (!peerConnection) {
            return false;
        }

        return peerConnection.connectionState === 'connected' && peerConnection.iceConnectionState === 'connected';
    }

    async function retryWebRTCConnection() {
        // Debounce: prevent rapid successive retries
        const now = Date.now();
        if (now - lastRetryTime < RETRY_DEBOUNCE_MS) {
            console.log('Retry debounced, skipping...');
            return;
        }
        lastRetryTime = now;

        // Max retry limit to prevent infinite loops
        retryCount++;
        if (retryCount > MAX_RETRIES) {
            console.error('Max retries (' + MAX_RETRIES + ') reached. Stopping retry loop.');
            console.log('Call may require manual re-initiation.');
            return;
        }

        console.log('=== RETRYING WEBRTC CONNECTION (attempt ' + retryCount + '/' + MAX_RETRIES + ') ===');

        if (peerConnection) {
            try {
                peerConnection.ontrack = null;
                peerConnection.onicecandidate = null;
                peerConnection.onconnectionstatechange = null;
                peerConnection.oniceconnectionstatechange = null;
                peerConnection.close();
            } catch (e) {}
            peerConnection = null;
        }

        clearRetryTimers();

        retryTimeoutId = setTimeout(async () => {
            const roomId = resolveRoomId();
            if (!roomId) {
                console.error('retryWebRTCConnection: missing roomId');
                return;
            }
            console.log('Reinitializing WebRTC connection...');
            await initializeCall(roomId);
        }, 2000);
    }

    // Removed aggressive autoRetryConnection — the connection state handlers
    // and signaling timeout now handle retries with proper debouncing.

    function debugVideoElements() {
        const localVideo = document.getElementById('localVideo');
        const remoteVideo = document.getElementById('remoteVideo');

        console.log('Local video element:', localVideo);
        console.log('Remote video element:', remoteVideo);
        console.log('Local stream:', localStream);
        console.log('Remote stream:', remoteStream);
        console.log('Peer connection:', peerConnection);
    }

    function endCall() {
        console.log('Ending call...');

        clearRetryTimers();

        if (isScreenSharing) {
            stopScreenShare();
        }

        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
        }

        if (screenStream) {
            screenStream.getTracks().forEach(track => track.stop());
        }

        if (peerConnection) {
            try {
                peerConnection.ontrack = null;
                peerConnection.onicecandidate = null;
                peerConnection.onconnectionstatechange = null;
                peerConnection.oniceconnectionstatechange = null;
                peerConnection.close();
            } catch (e) {}
        }

        if (remoteStream) {
            remoteStream.getTracks().forEach(track => track.stop());
        }

        localStream = null;
        remoteStream = null;
        peerConnection = null;
        screenStream = null;
        isMuted = false;
        isCameraOff = false;
        isScreenSharing = false;
        pendingIceCandidates = [];
        retryCount = 0;
        lastRetryTime = 0;
        isCallAnswered = false;
        pendingOffer = null;
        pendingRoomIdForOffer = null;
        window.mentorHubWebRtcIsCaller = false;
        window.mentorHubWebRtcCallType = null;
        window.currentIncomingCall = null;
        window.currentActiveRoomId = null;

        if (window.audioStatsInterval) {
            clearInterval(window.audioStatsInterval);
            window.audioStatsInterval = null;
        }
        if (window.videoAudioStatsInterval) {
            clearInterval(window.videoAudioStatsInterval);
            window.videoAudioStatsInterval = null;
        }

        showEndCallNotification('You ended the call.');
    }

    function showEndCallNotification(message = 'The call has been ended.') {
        const notification = document.getElementById('endCallNotification');
        const messageElement = document.getElementById('endCallMessage');

        if (notification && messageElement) {
            messageElement.textContent = message;
            notification.style.display = 'block';
            setTimeout(() => hideEndCallNotification(), 3000);
        }
    }

    function hideEndCallNotification() {
        const notification = document.getElementById('endCallNotification');
        if (notification) {
            notification.style.display = 'none';
        }
    }

    async function handleOffer(offer, roomId = null) {
        roomId = resolveRoomId({ roomId }, roomId);
        console.log('=== HANDLING WEBRTC OFFER ===');
        console.log('Offer received:', offer);
        console.log('Resolved roomId for offer:', roomId);

        if (!roomId) {
            console.error('handleOffer: missing roomId');
            return;
        }

        window.currentActiveRoomId = roomId;

        const normalizedOffer = normalizeSessionDescription(offer, 'offer');
        console.log('Normalized offer:', normalizedOffer);

        if (!normalizedOffer || !normalizedOffer.sdp) {
            console.error('Invalid normalized offer');
            return;
        }

        if (peerConnection && peerConnection.signalingState === 'have-local-offer') {
            console.log('Ignoring offer: caller already has local offer (echo or race).');
            return;
        }

        if (!peerConnection) {
            console.log('Buffering offer: peer connection not ready yet');
            pendingOffer = offer;
            pendingRoomIdForOffer = roomId;
            return;
        }

        try {
            if (peerConnection.remoteDescription) {
                console.warn('Remote description already set, skipping duplicate offer');
                return;
            }

            await peerConnection.setRemoteDescription(new RTCSessionDescription(normalizedOffer));
            console.log('✅ Remote description set for offer');

            await flushPendingIceCandidates();

            const answer = await peerConnection.createAnswer({
                offerToReceiveAudio: true,
                offerToReceiveVideo: getCurrentCallType() === 'video'
            });

            await peerConnection.setLocalDescription(answer);
            console.log('✅ Local description set for answer');

            const globalSocket = getSocketClient();
            if (globalSocket) {
                globalSocket.emit('webrtc_answer', {
                    roomId: roomId,
                    answer: toPlainRTCSessionDescription(answer),
                    from: window.currentUserId,
                    sessionId: window.webrtcSessionId
                });
                console.log('✅ Answer sent to signaling server');
                setTimeout(() => ensureRemoteVideoPlaying(), 2000);
            }
        } catch (error) {
            console.error('❌ Error handling offer:', error);
        }
    }

    async function handleAnswer(answer, roomId = null) {
        roomId = resolveRoomId({ roomId }, roomId);
        console.log('=== HANDLING WEBRTC ANSWER ===');
        console.log('Answer received:', answer);
        console.log('Resolved roomId for answer:', roomId);

        if (!roomId) {
            console.error('handleAnswer: missing roomId');
            return;
        }

        window.currentActiveRoomId = roomId;

        const normalizedAnswer = normalizeSessionDescription(answer, 'answer');
        console.log('Normalized answer:', normalizedAnswer);

        if (!normalizedAnswer || !normalizedAnswer.sdp) {
            console.error('Invalid normalized answer');
            return;
        }

        if (!peerConnection) {
            console.error('❌ No peer connection available to handle answer');
            return;
        }

        if (peerConnection.signalingState === 'stable') {
            console.warn('⚠️ PeerConnection is already stable! Ignoring duplicate answer packet since WebRTC is already connected.');
            return;
        }

        try {
            await peerConnection.setRemoteDescription(new RTCSessionDescription(normalizedAnswer));
            console.log('✅ Remote description set for answer');

            await flushPendingIceCandidates();
            setTimeout(() => ensureRemoteVideoPlaying(), 1000);
        } catch (error) {
            console.error('❌ Error handling answer:', error);
        }
    }

    async function handleIceCandidate(candidate, roomId = null) {
        roomId = resolveRoomId({ roomId }, roomId);
        console.log('Handling ICE candidate:', candidate, 'roomId:', roomId);

        if (!candidate) {
            console.error('No ICE candidate received');
            return;
        }

        const plainCandidate = toPlainIceCandidate(candidate);

        if (!plainCandidate) {
            console.error('Invalid ICE candidate');
            return;
        }

        if (window.currentActiveRoomId && roomId && roomId !== window.currentActiveRoomId) {
            console.warn('Ignoring stale ICE candidate for room:', roomId, 'current room:', window.currentActiveRoomId);
            return;
        }

        if (!peerConnection) {
            console.warn('No peer connection yet, queueing ICE candidate');
            pendingIceCandidates.push(plainCandidate);
            return;
        }

        if (!peerConnection.remoteDescription) {
            console.warn('Remote description not set yet, queueing ICE candidate');
            pendingIceCandidates.push(plainCandidate);
            return;
        }

        try {
            await peerConnection.addIceCandidate(new RTCIceCandidate(plainCandidate));
            console.log('ICE candidate added successfully');
        } catch (error) {
            console.error('Error adding ICE candidate:', error);
        }
    }

    function sendIceCandidate(candidate) {
        const globalSocket = getSocketClient();
        const roomId = resolveRoomId();

        if (!roomId) {
            console.error('sendIceCandidate: missing roomId');
            return;
        }

        if (globalSocket) {
            globalSocket.emit('webrtc_ice_candidate', {
                roomId,
                candidate: toPlainIceCandidate(candidate),
                from: window.currentUserId,
                sessionId: window.webrtcSessionId
            });
        } else {
            console.error('Socket not connected');
        }
    }

    function sendCallToSocket(data) {
        data = normalizePayload(data);
        const globalSocket = getSocketClient();

        console.log('=== sendCallToSocket FUNCTION CALLED ===');
        console.log('Processed data:', data);
        console.log('window.currentUserType:', window.currentUserType);

        if (!data.roomId || !data.callType || !data.callerId || !data.receiverId) {
            console.error('Missing required call data:', data);
            return;
        }

        window.currentActiveRoomId = data.roomId;

        const callData = {
            roomId: data.roomId,
            callType: data.callType,
            callerId: data.callerId,
            callerName: data.callerName || 'Unknown Caller',
            receiverId: data.receiverId,
            callerType: window.currentUserType || 'tutor',
            receiverType: data.receiverType || 'student'
        };

        const emitCall = () => {
            const sock = getSocketClient();
            if (!sock || !sock.connected) {
                return false;
            }
            sock.emit('call_initiated', callData);
            console.log('Call notification sent to Socket.IO successfully');
            return true;
        };

        const socketReady = globalSocket && globalSocket.connected;
        if (!socketReady) {
            console.warn('Socket not connected yet. Will retry to emit call.');
            if (window.chatSocket && !window.chatSocket.isConnected) {
                try { window.chatSocket.connect(); } catch (e) {}
            }
            let retries = 20;
            const interval = setInterval(() => {
                if (emitCall() || --retries <= 0) {
                    clearInterval(interval);
                    if (retries <= 0) {
                        console.error('Failed to send call: socket never connected.');
                    }
                }
            }, 250);
        } else {
            emitCall();
        }
    }

    function sendCallAnsweredToSocket(data) {
        data = normalizePayload(data);
        const globalSocket = getSocketClient();
        const roomId = resolveRoomId(data);

        if (globalSocket && roomId) {
            globalSocket.emit('call_answered', {
                roomId,
                answeredId: data.answeredId ?? data.receiverId,
                answeredType: data.answeredType ?? data.receiverType ?? window.currentUserType
            });
        }
    }

    function sendCallDeclinedToSocket(data) {
        data = normalizePayload(data);
        const globalSocket = getSocketClient();
        const roomId = resolveRoomId(data);

        if (globalSocket && roomId) {
            globalSocket.emit('call_declined', {
                roomId,
                declinedId: data.declinedId ?? data.receiverId,
                declinedType: data.declinedType ?? data.receiverType ?? window.currentUserType
            });
        }
    }

    function sendCallEndedToSocket(data) {
        data = normalizePayload(data);
        const globalSocket = getSocketClient();
        const roomId = resolveRoomId(data);

        if (globalSocket && roomId) {
            globalSocket.emit('call_ended', {
                roomId,
                endedBy: data.endedBy
            });
        }
    }
    </script>
    </div>
</div>