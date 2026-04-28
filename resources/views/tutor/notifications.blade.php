<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/MentorHub.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/MentorHub.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Notifications | MentorHub</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            line-height: 1.6;
            color: #333;
            background: 
                linear-gradient(rgba(255, 255, 255, 0.85), rgba(255, 255, 255, 0.85)),
                url('../images/Uc-background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header Styles */
        header {
            background: linear-gradient(135deg, #4a90e2, #5637d9);
            color: white;
            padding: 1rem 0;
            width: 100%;
            position: fixed;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            min-height: 60px;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 5%;
            max-width: 1200px;
            margin: 0 auto;
            flex-wrap: wrap;
            min-height: 60px;
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .logo-img {
            margin-right: 0.5rem;
            height: 50px;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .nav-links {
            display: flex;
            gap: 1rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            padding: 0.5rem 1rem;
            border-radius: 25px;
        }

        .nav-links a:hover, .nav-links a.active {
            background-color: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        main {
            flex: 1;
            padding: 0 1rem;
            margin-top: 80px;
            max-width: 1200px;
            width: 100%;
            align-self: center;
        }

        .profile-icon {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #4a90e2;
            color: white;
            font-weight: bold;
            cursor: pointer;
            z-index: 1000;
            transition: transform 0.2s cubic-bezier(0.4,0,0.2,1), box-shadow 0.2s cubic-bezier(0.4,0,0.2,1);
        }

        .profile-icon:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 16px rgba(74, 144, 226, 0.15);
        }

        .profile-icon-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            width: 180px;
            margin-top: 10px;
            z-index: 1001;
            overflow: hidden;
        }

        .dropdown-menu.active {
            display: block;
        }

        .dropdown-menu a {
            display: block;
            padding: 12px 15px;
            color: #333;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .dropdown-menu a:hover {
            background-color: #f5f5f5;
        }

        .header-right-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .currency-display {
            display: flex;
            align-items: center;
            background-color: rgba(255, 255, 255, 0.15);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .currency-display:hover {
            background-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .currency-icon {
            font-size: 1.2rem;
            margin-right: 0.5rem;
            color: #ffd700;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .currency-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .currency-amount {
            font-size: 1.1rem;
            font-weight: bold;
            color: white;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            line-height: 1;
        }

        .currency-label {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .notifications-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .notifications-header h2 {
            color: #333;
            margin: 0;
        }

        .mark-all-read {
            color: #4a90e2;
            text-decoration: none;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .mark-all-read:hover {
            background-color: #f0f7ff;
        }

        .notification-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            gap: 1rem;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }

        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .notification-card.unread {
            border-left: 4px solid #4a90e2;
            background-color: #f8fbff;
        }

        .notification-delete {
            background: transparent;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 0.5rem;
            transition: all 0.2s;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notification-delete:hover {
            background: #fee;
            color: #d32f2f;
            transform: scale(1.1);
        }

        .notification-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .notification-icon.booking {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .notification-icon.activity {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }

        .notification-icon.payment {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .notification-icon.message {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .notification-icon.admin {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .notification-icon.alert {
            background-color: #ffebee;
            color: #d32f2f;
        }

        .notification-icon.new-booking {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        /* "View Student Details" link inside booking cards */
        .view-student-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            margin-top: 0.6rem;
            padding: 0.35rem 0.9rem;
            background: linear-gradient(135deg, #4a90e2, #5637d9);
            color: white;
            border: none;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.25s ease;
            box-shadow: 0 2px 8px rgba(74, 144, 226, 0.3);
        }

        .view-student-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(74, 144, 226, 0.45);
            background: linear-gradient(135deg, #357abd, #4228b5);
        }

        /* ===== Student Details Modal ===== */
        .student-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .student-modal-overlay.active {
            display: flex;
            opacity: 1;
        }
        .student-modal {
            background: white;
            border-radius: 16px;
            width: 92%;
            max-width: 620px;
            max-height: 88vh;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0,0,0,0.25);
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }
        .student-modal-overlay.active .student-modal {
            transform: translateY(0);
        }
        .student-modal-header {
            background: linear-gradient(135deg, #4a90e2, #5637d9);
            padding: 1.4rem 1.8rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .student-modal-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(255,255,255,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
            border: 2px solid rgba(255,255,255,0.5);
        }
        .student-modal-header-info { flex: 1; }
        .student-modal-header-info h3 {
            margin: 0;
            color: white;
            font-size: 1.15rem;
            font-weight: 700;
        }
        .student-modal-header-info span {
            color: rgba(255,255,255,0.8);
            font-size: 0.85rem;
        }
        .student-modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.6rem;
            cursor: pointer;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.2s;
            flex-shrink: 0;
        }
        .student-modal-close:hover { background: rgba(255,255,255,0.2); }
        .student-modal-body {
            padding: 1.6rem 1.8rem;
            overflow-y: auto;
            max-height: calc(88vh - 110px);
        }
        .modal-section-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #999;
            margin: 1.2rem 0 0.7rem;
        }
        .modal-section-title:first-child { margin-top: 0; }
        .modal-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.8rem;
        }
        .modal-info-item {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 0.8rem 1rem;
            border: 1px solid #eef0f5;
        }
        .modal-info-item .label {
            font-size: 0.75rem;
            color: #999;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .modal-info-item .value {
            font-size: 0.95rem;
            font-weight: 600;
            color: #333;
            word-break: break-word;
        }
        .modal-info-item.full-width { grid-column: 1 / -1; }
        .interests-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-top: 0.3rem;
        }
        .interest-tag {
            background: linear-gradient(135deg, #e8f0ff, #f0e8ff);
            color: #5637d9;
            border-radius: 20px;
            padding: 0.2rem 0.7rem;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid rgba(86,55,217,0.15);
        }
        .session-status-badge {
            display: inline-block;
            padding: 0.2rem 0.7rem;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: capitalize;
        }
        .session-status-badge.pending  { background:#fff8e1; color:#f57f17; }
        .session-status-badge.accepted { background:#e8f5e9; color:#2e7d32; }
        .session-status-badge.rejected { background:#ffebee; color:#c62828; }
        .modal-loading {
            text-align: center;
            padding: 3rem;
            color: #999;
        }
        .modal-loading i { font-size: 2rem; margin-bottom: 0.8rem; display: block; }
        .modal-action-row {
            display: flex;
            gap: 0.8rem;
            margin-top: 1.4rem;
            padding-top: 1.2rem;
            border-top: 1px solid #eef0f5;
        }
        .modal-action-btn {
            flex: 1;
            padding: 0.7rem;
            border-radius: 10px;
            border: none;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }
        .modal-action-btn.primary {
            background: linear-gradient(135deg, #4a90e2, #5637d9);
            color: white;
        }
        .modal-action-btn.primary:hover { opacity: 0.9; transform: translateY(-1px); }
        @media (max-width: 500px) {
            .modal-info-grid { grid-template-columns: 1fr; }
            .modal-info-item.full-width { grid-column: auto; }
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .notification-message {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .notification-time {
            color: #999;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .no-notifications {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .no-notifications i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .no-notifications p {
            color: #666;
            font-size: 1.1rem;
        }

        /* Footer Styles */
        footer {
            background-color: #333;
            color: white;
            padding: 1.5rem 0;
            margin-top: auto;
            width: 100%;
        }

        .footer-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-links {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }

        .footer-links a {
            color: #ccc;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
            padding: 0.3rem 0;
        }

        .footer-links a:hover {
            color: white;
        }

        .copyright {
            font-size: 0.9rem;
            color: #aaa;
            text-align: center;
            width: 100%;
            margin-top: 0.5rem;
        }

        /* Footer Modal Styles */
        .footer-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .footer-modal.show {
            display: flex;
            opacity: 1;
        }

        .footer-modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        }

        .footer-modal.show .footer-modal-content {
            transform: translateY(0);
        }

        .footer-modal-header {
            background: linear-gradient(135deg, #4a90e2, #5637d9);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }

        .footer-modal-body {
            padding: 2rem;
            max-height: 60vh;
            overflow-y: auto;
            line-height: 1.6;
        }

        .footer-modal-body h3 {
            color: #2c3e50;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            font-size: 1.2rem;
        }

        .footer-modal-body p {
            margin-bottom: 1rem;
            color: #555;
        }

        .footer-modal-body ul {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
        }

        .footer-modal-body li {
            margin-bottom: 0.5rem;
            color: #555;
        }

        .footer-modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.3s ease;
        }

        .footer-modal-close:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .footer-modal-body a {
            color: #4a90e2;
            text-decoration: none;
            font-weight: 500;
        }

        .footer-modal-body a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="navbar">
            <a href="#" class="logo">
                <img src="{{asset('images/MentorHub.png')}}" alt="UCTutor Logo" class="logo-img">
            </a>
            <button class="menu-toggle" id="menu-toggle">☰</button>
            <nav class="nav-links" id="nav-links">
                <a href="{{route('tutor.dashboard')}}">Dashboard</a>
                <a href="{{route('tutor.bookings.index')}}">Bookings</a>
                <a href="{{route('tutor.my-sessions')}}">My Sessions</a>
                <a href="{{route('tutor.schedule')}}">Schedule</a>
            </nav>
            <div class="header-right-section">
                <!-- Currency Display -->
                <div class="currency-display" onclick="window.location.href='{{ route('tutor.wallet') }}'">
                    <div class="currency-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="currency-info">
                        <div class="currency-amount" id="currency-amount">₱0.00</div>
                        <div class="currency-label">Balance</div>
                    </div>
                </div>
                
                <!-- Profile Dropdown -->
                <div class="profile-dropdown-container" style="position: relative;">
                    <div class="profile-icon" id="profile-icon">
                        @auth('tutor')
                            @php
                                $tutor = Auth::guard('tutor')->user();
                            @endphp
                            @if($tutor->profile_picture)
                                <img src="{{ route('tutor.profile.picture') }}?v={{ time() }}" alt="Profile Picture" class="profile-icon-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center; background-color: #f5f5f5; color: #666; font-weight: bold; font-size: 1.2rem; border-radius: 50%;">{{ strtoupper(substr($tutor->first_name, 0, 1) . substr($tutor->last_name, 0, 1)) }}</div>
                            @else
                                {{ strtoupper(substr($tutor->first_name, 0, 1) . substr($tutor->last_name, 0, 1)) }}
                            @endif
                        @endauth
                    </div>
                    @auth('tutor')
                    <div class="dropdown-menu" id="dropdown-menu">
                        <a href="{{ route('tutor.profile.edit') }}">My Profile</a>
                        <a href="{{ route('tutor.settings') }}">Achievements</a>
                        <a href="{{ route('tutor.report-problem') }}">Report a Problem</a>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" method="POST" action="{{ route('tutor.logout') }}" style="display: none;">
                            @csrf
                        </form>
                    </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main>
        <div class="notifications-container">
            <div class="notifications-header">
                <h2><i class="fas fa-bell"></i> Notifications</h2>
                <a href="#" class="mark-all-read" onclick="markAllAsRead(); return false;">
                    <i class="fas fa-check-double"></i> Mark all as read
                </a>
            </div>

            <div id="notifications-list">
                @forelse($notifications as $notification)
                    @php
                        $isBooking = in_array($notification->type, ['new_booking', 'booking_request']);
                        $iconClass = match($notification->type) {
                            'new_booking', 'booking_request' => 'new-booking',
                            'activity_submitted'             => 'booking',
                            'activity_posted'                => 'activity',
                            'payment_received'               => 'payment',
                            'admin_message'                  => 'admin',
                            default                          => 'alert',
                        };
                    @endphp
                    <div class="notification-card {{ !$notification->is_read ? 'unread' : '' }}" data-id="{{ $notification->id }}">
                        <div class="notification-icon {{ $iconClass }}">
                            @if($notification->type === 'new_booking' || $notification->type === 'booking_request')
                                <i class="fas fa-user-graduate"></i>
                            @elseif($notification->type === 'problem_report_response')
                                <i class="fas fa-exclamation-circle"></i>
                            @elseif($notification->type === 'assignment_submitted')
                                <i class="fas fa-tasks"></i>
                            @elseif($notification->type === 'activity_submitted')
                                <i class="fas fa-paper-plane"></i>
                            @elseif($notification->type === 'activity_posted')
                                <i class="fas fa-tasks"></i>
                            @elseif($notification->type === 'payment_received')
                                <i class="fas fa-money-bill-wave"></i>
                            @elseif($notification->type === 'achievement_unlocked')
                                <i class="fas fa-trophy" style="color: #FFD700;"></i>
                            @elseif($notification->type === 'achievement_progress')
                                <i class="fas fa-chart-line" style="color: #4a90e2;"></i>
                            @elseif($notification->type === 'new_message')
                                <i class="fas fa-envelope"></i>
                            @elseif($notification->type === 'admin_message')
                                <i class="fas fa-user-shield"></i>
                            @elseif($notification->type === 'new_review')
                                <i class="fas fa-star"></i>
                            @else
                                <i class="fas fa-bell"></i>
                            @endif
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">{{ $notification->title }}</div>
                            <div class="notification-message">{{ $notification->message }}</div>
                            @if($isBooking && $notification->related_id)
                                <button class="view-student-btn"
                                        onclick="openStudentModal({{ $notification->id }})"
                                        title="View student booking details">
                                    <i class="fas fa-user-circle"></i> View Student Details
                                </button>
                            @endif
                            <div class="notification-time">
                                <i class="fas fa-clock"></i> {{ $notification->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <button class="notification-delete" onclick="deleteNotification({{ $notification->id }})" title="Delete notification">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @empty
                    <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
                        <i class="fas fa-bell-slash" style="font-size: 4rem; color: #ddd; margin-bottom: 1rem;"></i>
                        <p style="color: #666; font-size: 1.1rem;">No notifications yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </main>
    
    @include('layouts.footer-modals')

    <!-- ===== Student Details Modal ===== -->
    <div class="student-modal-overlay" id="studentModalOverlay" onclick="closeStudentModalOnBackdrop(event)">
        <div class="student-modal" id="studentModal">
            <div class="student-modal-header">
                <div class="student-modal-avatar" id="modalAvatar">?</div>
                <div class="student-modal-header-info">
                    <h3 id="modalStudentName">Loading&hellip;</h3>
                    <span id="modalStudentId"></span>
                </div>
                <button class="student-modal-close" onclick="closeStudentModal()" title="Close">&times;</button>
            </div>
            <div class="student-modal-body" id="studentModalBody">
                <div class="modal-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    Fetching booking details&hellip;
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-links">
                <a href="#" id="footer-privacy-link">Privacy Policy</a>
                <a href="#" id="footer-terms-link">Terms of Service</a>
                <a href="#" id="footer-faq-link">FAQ</a>
                <a href="#" id="footer-contact-link">Contact</a>
            </div>
            <div class="copyright">
                &copy; 2025 MentorHub. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menu-toggle');
            const navLinks = document.getElementById('nav-links');
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    navLinks.classList.toggle('active');
                });
            }
            
            // Profile dropdown functionality
            const profileIcon = document.getElementById('profile-icon');
            const dropdownMenu = document.getElementById('dropdown-menu');
            
            if (profileIcon && dropdownMenu) {
                profileIcon.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('active');
                });

                document.addEventListener('click', function(e) {
                    if (!profileIcon.contains(e.target)) {
                        dropdownMenu.classList.remove('active');
                    }
                });
            }

            // Load currency data
            fetch('{{ route("tutor.wallet.balance") }}')
                .then(response => response.json())
                .then(data => {
                    const currencyAmount = document.getElementById('currency-amount');
                    if (currencyAmount) {
                        currencyAmount.textContent = '₱' + parseFloat(data.balance).toFixed(2);
                    }
                })
                .catch(error => {
                    console.error('Error loading wallet balance:', error);
                });
        });

        function markAllAsRead() {
            document.querySelectorAll('.notification-card.unread').forEach(card => {
                card.classList.remove('unread');
            });
            fetch('{{ route("tutor.notifications.mark-all-read") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
            });
        }

        /* ===== Student Booking Modal ===== */
        function openStudentModal(notificationId) {
            const overlay = document.getElementById('studentModalOverlay');
            const body    = document.getElementById('studentModalBody');
            const avatar  = document.getElementById('modalAvatar');
            const nameEl  = document.getElementById('modalStudentName');
            const idEl    = document.getElementById('modalStudentId');

            // Reset
            avatar.textContent  = '?';
            nameEl.textContent  = 'Loading…';
            idEl.textContent    = '';
            body.innerHTML = `<div class="modal-loading"><i class="fas fa-spinner fa-spin"></i>Fetching booking details…</div>`;
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';

            fetch(`/tutor/notifications/${notificationId}/booking-details`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.error) { body.innerHTML = `<p style="color:#c62828;padding:2rem">${data.error}</p>`; return; }
                const s = data.session;
                const st = data.student;

                avatar.textContent = st.avatar_initials;
                nameEl.textContent = st.name;
                idEl.textContent   = st.student_id ?? '';

                // Build interests tags
                let interestHTML = '<em style="color:#bbb;font-size:0.85rem">None specified</em>';
                if (st.subjects_interest) {
                    const tags = st.subjects_interest.split(/[,;\n\r]+/).map(t => t.trim()).filter(Boolean);
                    if (tags.length) {
                        interestHTML = `<div class="interests-tags">${tags.map(t =>
                            `<span class="interest-tag"><i class="fas fa-book-open" style="margin-right:3px"></i>${t}</span>`
                        ).join('')}</div>`;
                    }
                }

                const statusBadge = `<span class="session-status-badge ${s.status}">${s.status}</span>`;

                body.innerHTML = `
                    <p class="modal-section-title"><i class="fas fa-user" style="margin-right:4px"></i>Student Information</p>
                    <div class="modal-info-grid">
                        <div class="modal-info-item">
                            <div class="label"><i class="fas fa-envelope"></i> Email</div>
                            <div class="value">${st.email ?? '—'}</div>
                        </div>
                        <div class="modal-info-item">
                            <div class="label"><i class="fas fa-phone"></i> Phone</div>
                            <div class="value">${st.phone ?? '—'}</div>
                        </div>
                        <div class="modal-info-item">
                            <div class="label"><i class="fas fa-graduation-cap"></i> Course</div>
                            <div class="value">${st.course ?? '—'}</div>
                        </div>
                        <div class="modal-info-item">
                            <div class="label"><i class="fas fa-layer-group"></i> Year Level</div>
                            <div class="value">${st.year_level ?? '—'}</div>
                        </div>
                        <div class="modal-info-item full-width">
                            <div class="label"><i class="fas fa-star"></i> Subjects of Interest</div>
                            <div class="value" style="margin-top:0.3rem">${interestHTML}</div>
                        </div>
                    </div>

                    <p class="modal-section-title" style="margin-top:1.4rem"><i class="fas fa-calendar-check" style="margin-right:4px"></i>Booking Details</p>
                    <div class="modal-info-grid">
                        <div class="modal-info-item">
                            <div class="label"><i class="fas fa-calendar"></i> Date</div>
                            <div class="value">${s.date}</div>
                        </div>
                        <div class="modal-info-item">
                            <div class="label"><i class="fas fa-clock"></i> Time</div>
                            <div class="value">${s.start_time} – ${s.end_time}</div>
                        </div>
                        <div class="modal-info-item">
                            <div class="label"><i class="fas fa-laptop"></i> Session Type</div>
                            <div class="value">${s.session_type ? s.session_type.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()) : '—'}</div>
                        </div>
                        <div class="modal-info-item">
                            <div class="label"><i class="fas fa-tag"></i> Booking Type</div>
                            <div class="value">${s.booking_type ? s.booking_type.replace(/\b\w/g,c=>c.toUpperCase()) : '—'}</div>
                        </div>
                        <div class="modal-info-item">
                            <div class="label"><i class="fas fa-peso-sign"></i> Rate</div>
                            <div class="value">₱${s.rate}</div>
                        </div>
                        <div class="modal-info-item">
                            <div class="label"><i class="fas fa-info-circle"></i> Status</div>
                            <div class="value">${statusBadge}</div>
                        </div>
                        ${s.notes ? `
                        <div class="modal-info-item full-width">
                            <div class="label"><i class="fas fa-sticky-note"></i> Notes</div>
                            <div class="value">${s.notes}</div>
                        </div>` : ''}
                    </div>

                    <div class="modal-action-row">
                        <a href="{{ route('tutor.bookings.index') }}" class="modal-action-btn primary">
                            <i class="fas fa-calendar-check"></i> Manage Bookings
                        </a>
                    </div>
                `;
            })
            .catch(() => {
                body.innerHTML = `<p style="color:#c62828;padding:2rem">Failed to load booking details. Please try again.</p>`;
            });
        }

        function closeStudentModal() {
            document.getElementById('studentModalOverlay').classList.remove('active');
            document.body.style.overflow = '';
        }

        function closeStudentModalOnBackdrop(e) {
            if (e.target === document.getElementById('studentModalOverlay')) closeStudentModal();
        }

        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeStudentModal(); });

        function deleteNotification(id) {
            if (!confirm('Are you sure you want to delete this notification?')) {
                return;
            }

            fetch(`/tutor/notifications/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = document.querySelector(`[data-id="${id}"]`);
                    if (card) {
                        card.style.transition = 'opacity 0.3s';
                        card.style.opacity = '0';
                        setTimeout(() => {
                            card.remove();
                            
                            // Check if no more notifications
                            const notificationsList = document.getElementById('notifications-list');
                            if (!notificationsList.querySelector('.notification-card')) {
                                notificationsList.innerHTML = `
                                    <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
                                        <i class="fas fa-bell-slash" style="font-size: 4rem; color: #ddd; margin-bottom: 1rem;"></i>
                                        <p style="color: #666; font-size: 1.1rem;">No notifications yet</p>
                                    </div>
                                `;
                            }
                        }, 300);
                    }
                } else {
                    alert('Failed to delete notification');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the notification');
            });
        }
    </script>
    @include('layouts.footer-js')
</body>
</html>

