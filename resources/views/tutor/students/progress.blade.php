<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/MentorHub.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/MentorHub.png') }}">
    <title>{{ $student->first_name }} {{ $student->last_name }} - Progress | MentorHub</title>
    <link rel="stylesheet" href="{{asset('style/dashboard.css')}}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
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
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1);
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

        .progress-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
            width: 100%;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #2d7dd2;
            text-decoration: none;
            margin-bottom: 1.5rem;
            font-weight: 500;
            transition: color 0.3s;
        }

        .back-button:hover {
            color: #1e5bb8;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2.5rem;
            color: #2d7dd2;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .student-info-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .student-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            flex-shrink: 0;
        }

        .student-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .student-details h2 {
            margin: 0 0 0.5rem 0;
            color: #2c3e50;
            font-size: 1.8rem;
        }

        .student-details p {
            margin: 0;
            color: #7f8c8d;
            font-size: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
            align-self: start;
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #2d7dd2;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .activities-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .section-title {
            font-size: 1.5rem;
            color: #2d7dd2;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .activities-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .activity-item {
            padding: 1rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }

        .activity-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .activity-info h4 {
            margin: 0 0 0.5rem 0;
            color: #2c3e50;
            font-size: 1.1rem;
        }

        .activity-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }

        .activity-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-submitted {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-graded {
            background-color: #d4edda;
            color: #155724;
        }

        .activity-score {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2d7dd2;
        }

        .no-activities {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .no-activities i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .ai-analytics-container {
            background: white;
            border-radius: 16px;
            padding: 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out;
            position: relative;
            grid-column: 2 / -1;
            color: #333;
            border: 1px solid #eef2f6;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            max-height: 2000px;
            /* Default expanded state */
        }

        .ai-analytics-container.collapsed {
            max-height: 192px;
            /* Matches stat-card height more accurately */
        }

        .ai-analytics-container.collapsed .ai-analytics-body {
            opacity: 0.3;
            pointer-events: none;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 5px rgba(74, 144, 226, 0.2);
            }

            50% {
                box-shadow: 0 0 15px rgba(74, 144, 226, 0.4);
            }
        }

        .ai-analytics-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            cursor: pointer;
        }

        .ai-header-main {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .ai-expand-icon {
            color: white;
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .collapsed .ai-expand-icon {
            transform: rotate(-180deg);
        }

        .ai-analytics-header .ai-brain-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: white;
            animation: pulse-glow 3s infinite;
            flex-shrink: 0;
        }

        .ai-analytics-header .ai-header-text h3 {
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0 0 0.2rem 0;
            letter-spacing: -0.5px;
        }

        .ai-analytics-header .ai-header-text p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.85rem;
            margin: 0;
        }

        .ai-analytics-body {
            padding: 1.5rem;
            transition: opacity 0.3s ease;
        }

        .ai-summary-text {
            color: #4b5563;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            padding: 1.2rem;
            background: #f8fafc;
            border-radius: 12px;
            border-left: 4px solid #6366f1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .ai-behavior-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 0.5rem;
        }

        .ai-behavior-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #6366f1;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .ai-performance-summary-card {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
            border: 1px solid #e2e8f0;
        }

        .perf-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.95rem;
            color: #334155;
        }

        .ai-suggestions-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .ai-suggestion-card {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 1.2rem;
            border: 1px solid #e2e8f0;
        }

        .suggestion-label {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .suggestion-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
        }

        .ai-section-block {
            margin-bottom: 1.2rem;
        }

        .ai-section-block h4 {
            color: #1e293b;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .ai-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.35rem 0.8rem;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 500;
            margin: 0.2rem;
            transition: transform 0.2s;
        }

        .ai-tag:hover {
            transform: scale(1.05);
        }

        .ai-tag.strength {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .ai-tag.weakness {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .ai-wrong-answer-item .wa-answers {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .ai-wrong-answer-item .wa-student {
            color: #c62828;
            font-size: 0.78rem;
        }

        .ai-wrong-answer-item .wa-correct {
            color: #2e7d32;
            font-size: 0.78rem;
        }

        .ai-recommendation-item {
            background: white;
            border-radius: 12px;
            padding: 1.2rem;
            margin-bottom: 1rem;
            color: #333;
            font-size: 0.95rem;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
            border-left: 6px solid #ef4444;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .ai-recommendation-item .rec-header {
            font-weight: 700;
            color: #111;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .ai-recommendation-item .rec-action {
            background: #f3f4f6;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #555;
        }

        .ai-recommendation-item .rec-icon {
            display: none;
        }

        .ai-grade-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .ai-grade-badge.excellent {
            background: #10b981;
            color: white;
            border: none;
        }

        .ai-grade-badge.good {
            background: #3b82f6;
            color: white;
            border: none;
        }

        .ai-grade-badge.average {
            background: #f59e0b;
            color: white;
            border: none;
        }

        .ai-grade-badge.below_average {
            background: #ef4444;
            color: white;
            border: none;
        }

        .ai-grade-badge.needs_improvement {
            background: #ef4444;
            color: white;
            border: none;
        }

        .ai-grade-badge.pending {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
        }

        .ai-pending-state {
            text-align: center;
            padding: 2rem 1.5rem;
        }

        .ai-pending-state i {
            font-size: 2.5rem;
            color: #bbb;
            margin-bottom: 0.8rem;
        }

        .ai-pending-state p {
            color: #888;
            font-size: 0.9rem;
            line-height: 1.6;
            max-width: 400px;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }

            .student-info-card {
                flex-direction: column;
                text-align: center;
            }

            .activity-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .ai-analytics-container {
                grid-column: 1 / -1;
            }

            .ai-analytics-body {
                padding: 1rem;
            }

            .ai-metrics-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .ai-wrong-answer-item .wa-answers {
                flex-direction: column;
                gap: 0.3rem;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header>
        <div class="navbar">
            <a href="#" class="logo">
                <img src="{{asset('images/MentorHub.png')}}" alt="MentorHub Logo" class="logo-img">
            </a>
            <button class="menu-toggle" id="menu-toggle">☰</button>
            <nav class="nav-links" id="nav-links">
                <a href="{{ route('tutor.dashboard') }}">Dashboard</a>
                <a href="{{ route('tutor.bookings.index') }}">My Bookings</a>
                <a href="{{ route('tutor.students') }}" class="active">Students</a>
                <a href="{{ route('tutor.schedule') }}">Schedule</a>
            </nav>
            <div class="header-right-section">
                <!-- Currency Display -->
                <div class="currency-display">
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
                        @if($tutor->profile_picture)
                            <img src="{{ asset('storage/' . $tutor->profile_picture) }}?{{ time() }}" alt="Profile Picture"
                                class="profile-icon-img">
                        @else
                            {{ strtoupper(substr($tutor->first_name, 0, 1) . substr($tutor->last_name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="dropdown-menu" id="dropdown-menu">
                        <a href="{{ route('tutor.profile.edit') }}">My Profile</a>
                        <a href="{{ route('tutor.settings') }}">Achievements</a>
                        <a href="{{ route('tutor.report-problem') }}">Report a Problem</a>
                        <a href="#"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" method="POST" action="{{ route('tutor.logout') }}"
                            style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main style="margin-top: 80px;">
        <div class="progress-container">
            <a href="{{ route('tutor.students') }}" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Students
            </a>

            <div class="page-header">
                <h1 class="page-title">Student Progress</h1>
            </div>

            <!-- Student Info Card -->
            <div class="student-info-card">
                <div class="student-avatar">
                    @if($student->profile_picture)
                        <img src="{{ asset('storage/' . $student->profile_picture) }}" alt="{{ $student->first_name }}">
                    @else
                        {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                    @endif
                </div>
                <div class="student-details">
                    <h2>{{ $student->first_name }} {{ $student->last_name }}</h2>
                    <p>{{ $student->email }}</p>
                </div>
            </div>

            <!-- Statistics Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-value">{{ $totalActivities }}</div>
                    <div class="stat-label">Total Activities</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value">{{ $submittedActivities }}</div>
                    <div class="stat-label">Submitted</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-value">{{ $gradedCount }}</div>
                    <div class="stat-label">Graded</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-value">{{ number_format($averageScore, 1) }}%</div>
                    <div class="stat-label">Average Score</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-value">{{ $sessionsCount }}</div>
                    <div class="stat-label">Total Sessions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="stat-value">{{ $completedSessions }}</div>
                    <div class="stat-label">Completed Sessions</div>
                </div>
                <!-- AI Performance Analytics Container (inside stats-grid) -->
                @if(isset($aiAnalytics))
                    <div class="ai-analytics-container collapsed" id="ai-analytics">
                        <div class="ai-analytics-header" id="ai-header-toggle">
                            <div class="ai-header-main">
                                <div class="ai-brain-icon">
                                    <i class="fas fa-brain"></i>
                                </div>
                                <div class="ai-header-text">
                                    <h3>AI-Powered Learning Recommendations</h3>
                                    <p>Personalized insights based on student performance and learning patterns</p>
                                </div>
                            </div>
                            <div class="ai-expand-icon">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        <div class="ai-analytics-body">
                            @if($aiAnalytics['total_questions'] > 0)
                                <!-- AI Summary -->
                                <div class="ai-summary-text">
                                    <div style="display: flex; align-items: flex-start; gap: 0.8rem;">
                                        <i class="fas fa-robot"
                                            style="color: #4a90e2; font-size: 1.2rem; margin-top: 0.2rem;"></i>
                                        <div>
                                            <div style="font-weight: 600; color: #1e293b; margin-bottom: 0.4rem;">Performance
                                                Summary</div>
                                            {{ $aiAnalytics['ai_insight']['summary'] }}
                                        </div>
                                    </div>

                                    @if(isset($behavioralAnalysis))
                                        <div class="ai-behavior-card">
                                            <div class="ai-behavior-title">
                                                <i class="fas fa-fingerprint"></i> Behavioral Analysis
                                            </div>
                                            <div style="color: #475569; font-size: 0.9rem; line-height: 1.5;">
                                                {{ $behavioralAnalysis }}
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Top Performance Card -->
                                <div class="ai-performance-summary-card">
                                    <div class="perf-row">
                                        <span>Learning Pace:</span>
                                        <span class="ai-grade-badge {{ $aiAnalytics['ai_insight']['performance_level'] }}">
                                            {{ $aiAnalytics['ai_insight']['grade_assessment'] }}
                                        </span>
                                    </div>
                                    <div class="perf-row">
                                        <span>Average Score:</span>
                                        <span>{{ number_format($averageScore, 1) }}%</span>
                                    </div>
                                    <div class="perf-row">
                                        <span>Performance Trend:</span>
                                        <span style="color: #f59e0b;">
                                            @if(isset($learningAnalysis['performance_trend']))
                                                @if($learningAnalysis['performance_trend'] == 'improving')
                                                    <i class="fas fa-arrow-up"></i> Improving
                                                @elseif($learningAnalysis['performance_trend'] == 'declining')
                                                    <i class="fas fa-arrow-down"></i> Declining
                                                @else
                                                    <i class="fas fa-minus"></i> Stable
                                                @endif
                                            @else
                                                <i class="fas fa-minus"></i> Insufficient Data
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <!-- Side-by-side Suggestion Cards -->
                                <div class="ai-suggestions-grid">
                                    <div class="ai-suggestion-card">
                                        <div class="suggestion-label">Suggested Difficulty</div>
                                        <div class="suggestion-value">{{ $suggestedDifficulty ?? 'Foundational' }}</div>
                                    </div>
                                    <div class="ai-suggestion-card">
                                        <div class="suggestion-label">Suggested Frequency</div>
                                        <div class="suggestion-value">{{ $suggestedFrequency ?? 'Weekly' }}</div>
                                    </div>
                                </div>

                                <!-- Strengths -->
                                @if(!empty($aiAnalytics['ai_insight']['strengths']))
                                    <div class="ai-section-block">
                                        <h4><i class="fas fa-check-circle" style="color: #2e7d32;"></i> Strengths</h4>
                                        <div>
                                            @foreach($aiAnalytics['ai_insight']['strengths'] as $strength)
                                                <span class="ai-tag strength"><i class="fas fa-star"></i> {{ $strength }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Weak Spots -->
                                @if(!empty($aiAnalytics['ai_insight']['weaknesses']))
                                    <div class="ai-section-block">
                                        <h4><i class="fas fa-exclamation-triangle" style="color: #e53935;"></i> Weak Spots</h4>
                                        <div>
                                            @foreach($aiAnalytics['ai_insight']['weaknesses'] as $weakness)
                                                <span class="ai-tag weakness"><i class="fas fa-times-circle"></i> {{ $weakness }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Learning Recommendations -->
                                @if(!empty($aiAnalytics['ai_insight']['recommendations']))
                                    <div class="ai-section-block">
                                        <h4><i class="fas fa-lightbulb"></i> Strategic Teaching Plan</h4>
                                        @foreach($aiAnalytics['ai_insight']['recommendations'] as $index => $rec)
                                            <div class="ai-recommendation-item">
                                                <div class="rec-header">
                                                    @if($index == 0)
                                                        <i class="fas fa-hand-paper" style="color: #ef4444;"></i> Needs Immediate Attention
                                                    @else
                                                        <i class="fas fa-bullseye" style="color: #6366f1;"></i> Additional Support Required
                                                    @endif
                                                </div>
                                                <div class="rec-content">
                                                    {{ $rec }}
                                                </div>
                                                <div class="rec-action">
                                                    <strong>Action:</strong>
                                                    {{ $index == 0 ? 'Schedule extra sessions and review basics thoroughly' : 'Increase support sessions and simplify content structure' }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <!-- Pending State -->
                                <div class="ai-pending-state">
                                    <i class="fas fa-chart-pie"></i>
                                    <p>{{ $aiAnalytics['ai_insight']['summary'] }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Activities Section -->
            <div class="activities-section">
                <h2 class="section-title">
                    <i class="fas fa-list"></i> Activities
                </h2>

                @if($activities->count() > 0)
                    <div class="activities-list">
                        @foreach($activities as $activity)
                            @php
                                $submission = $activity->studentSubmission($student->id);
                                $status = $submission ? $submission->status : 'pending';
                                $score = $submission && $submission->status === 'graded' ? $submission->score : null;
                            @endphp
                            <div class="activity-item">
                                <div class="activity-info">
                                    <h4>{{ $activity->title }}</h4>
                                    <p>
                                        <i class="fas fa-calendar"></i>
                                        Due: {{ $activity->due_date ? $activity->due_date->format('M d, Y') : 'No due date' }}
                                        @if($activity->total_points)
                                            | <i class="fas fa-star"></i> {{ $activity->total_points }} points
                                        @endif
                                    </p>
                                    <span class="activity-status status-{{ $status }}">
                                        @if($status === 'pending')
                                            <i class="fas fa-clock"></i> Pending
                                        @elseif($status === 'submitted')
                                            <i class="fas fa-paper-plane"></i> Submitted
                                        @elseif($status === 'graded')
                                            <i class="fas fa-check-circle"></i> Graded
                                        @endif
                                    </span>
                                </div>
                                @if($score !== null)
                                    <div class="activity-score">
                                        {{ $score }}/{{ $activity->total_points }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="no-activities">
                        <i class="fas fa-inbox"></i>
                        <h3>No Activities Yet</h3>
                        <p>This student hasn't been assigned any activities yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </main>

    @include('layouts.footer-modals')

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
        document.addEventListener('DOMContentLoaded', function () {
            // Mobile menu toggle
            const menuToggle = document.getElementById('menu-toggle');
            const navLinks = document.getElementById('nav-links');

            if (menuToggle && navLinks) {
                menuToggle.addEventListener('click', function () {
                    navLinks.classList.toggle('active');
                });
            }

            // Profile dropdown functionality
            const profileIcon = document.getElementById('profile-icon');
            const dropdownMenu = document.getElementById('dropdown-menu');

            if (profileIcon && dropdownMenu) {
                profileIcon.addEventListener('click', function (e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('active');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function (e) {
                    if (!profileIcon.contains(e.target)) {
                        dropdownMenu.classList.remove('active');
                    }
                });
            }

            // Initialize currency display
            loadCurrencyData();

            // AI Analytics Toggle
            const aiHeader = document.getElementById('ai-header-toggle');
            const aiContainer = document.getElementById('ai-analytics');
            const expandIcon = document.querySelector('.ai-expand-icon i');

            if (aiHeader && aiContainer) {
                aiHeader.addEventListener('click', function () {
                    aiContainer.classList.toggle('collapsed');
                    if (aiContainer.classList.contains('collapsed')) {
                        expandIcon.classList.replace('fa-chevron-up', 'fa-chevron-down');
                    } else {
                        expandIcon.classList.replace('fa-chevron-down', 'fa-chevron-up');
                    }
                });
            }
        });

        function loadCurrencyData() {
            fetch('{{ route('tutor.wallet.balance') }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(data => {
                    const currencyAmount = document.getElementById('currency-amount');
                    if (currencyAmount && data.balance !== undefined) {
                        currencyAmount.textContent = '₱' + parseFloat(data.balance).toFixed(2);
                    }
                })
                .catch(error => {
                    console.error('Error loading currency data:', error);
                });
        }
    </script>
    @include('layouts.footer-js')
</body>

</html>