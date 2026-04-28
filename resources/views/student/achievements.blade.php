<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/MentorHub.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/MentorHub.png') }}">
    <title>Achievements - MentorHub</title>
    <link rel="stylesheet" href="{{ asset('style/dashboard.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @include('layouts.footer-modals')
    <style>
        body {
            background: linear-gradient(rgba(255,255,255,0.85), rgba(255,255,255,0.85)), url('../images/Uc-background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        
        .settings-container {
            max-width: 1200px;
            margin: 100px auto 2rem;
            padding: 0 1rem;
        }
        
        .settings-header {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .settings-title {
            font-size: 2rem;
            color: #2d7dd2;
            margin-bottom: 0.5rem;
        }
        
        .gamification-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            text-align: center;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2d7dd2;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .level-card {
            background: linear-gradient(135deg, #2d7dd2, #4a3dd9);
            color: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(45, 125, 210, 0.3);
        }
        
        .level-number {
            font-size: 4rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .level-progress {
            background: rgba(255,255,255,0.3);
            border-radius: 10px;
            height: 20px;
            margin-top: 1rem;
            overflow: hidden;
        }
        
        .level-progress-bar {
            background: white;
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        
        .level-progress-text {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: white;
            text-align: center;
        }
        
        .achievements-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .section-title {
            font-size: 1.5rem;
            color: #2d7dd2;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .achievements-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
        }
        
        .achievement-card {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .achievement-card.unlocked {
            border-color: #28a745;
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(40, 167, 69, 0.05));
        }
        
        .achievement-card.locked {
            opacity: 0.6;
        }
        
        .achievement-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #2d7dd2;
        }
        
        .achievement-card.unlocked .achievement-icon {
            color: #28a745;
        }
        
        .achievement-name {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .achievement-description {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 1rem;
        }
        
        .achievement-progress {
            background: #e0e0e0;
            border-radius: 10px;
            height: 8px;
            margin-bottom: 0.5rem;
            overflow: hidden;
        }
        
        .achievement-progress-bar {
            background: #2d7dd2;
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        
        .achievement-card.unlocked .achievement-progress-bar {
            background: #28a745;
        }
        
        .achievement-progress-text {
            font-size: 0.8rem;
            color: #666;
        }
        
        .achievement-points {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ffd700;
            color: #333;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .unlocked-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #28a745;
            color: white;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: bold;
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
            cursor: pointer;
        }
        
        .currency-icon {
            font-size: 1.2rem;
            margin-right: 0.5rem;
            color: #ffd700;
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
            line-height: 1;
        }
        
        .currency-label {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }
        
        .rewards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .reward-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .reward-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        }
        
        .reward-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }
        
        .reward-card.booking-reward::before {
            background: linear-gradient(90deg, #28a745, #20c997);
        }
        
        .reward-card.withdrawal-reward::before {
            background: linear-gradient(90deg, #ff9800, #ff5722);
        }
        
        .reward-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .reward-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        
        .reward-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .reward-next {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .reward-next i {
            color: #ffd700;
        }
        
        .requirements-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .requirements-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .next-level-badge {
            background: linear-gradient(135deg, #ffd700, #ff9800);
            color: #333;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            animation: shimmer 2s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { box-shadow: 0 0 5px rgba(255, 215, 0, 0.3); }
            50% { box-shadow: 0 0 15px rgba(255, 215, 0, 0.6); }
        }
        
        .req-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .req-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #ddd;
            transition: all 0.3s ease;
        }
        
        .req-item.completed {
            border-left-color: #28a745;
            background: linear-gradient(135deg, rgba(40,167,69,0.05), rgba(40,167,69,0.02));
        }
        
        .req-item.pending {
            border-left-color: #ffc107;
            background: linear-gradient(135deg, rgba(255,193,7,0.05), rgba(255,193,7,0.02));
        }
        
        .req-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }
        
        .req-item.completed .req-icon {
            background: rgba(40,167,69,0.15);
            color: #28a745;
        }
        
        .req-item.pending .req-icon {
            background: rgba(255,193,7,0.15);
            color: #ff9800;
        }
        
        .req-details {
            flex: 1;
        }
        
        .req-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }
        
        .req-status {
            font-size: 0.85rem;
            color: #666;
        }
        
        .req-status strong {
            color: #2d7dd2;
        }
        
        .roadmap-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .roadmap-timeline {
            display: flex;
            gap: 0;
            overflow-x: auto;
            padding: 1rem 0;
            scrollbar-width: thin;
        }
        
        .roadmap-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 120px;
            position: relative;
            flex-shrink: 0;
        }
        
        .roadmap-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 20px;
            left: 60px;
            width: calc(100% - 20px);
            height: 3px;
            background: #e0e0e0;
            z-index: 0;
        }
        
        .roadmap-item.unlocked:not(:last-child)::after {
            background: linear-gradient(90deg, #28a745, #20c997);
        }
        
        .roadmap-node {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
            z-index: 1;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .roadmap-item.unlocked .roadmap-node {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            box-shadow: 0 2px 8px rgba(40,167,69,0.3);
        }
        
        .roadmap-item.current .roadmap-node {
            background: linear-gradient(135deg, #2d7dd2, #4a3dd9);
            color: white;
            box-shadow: 0 0 0 4px rgba(45,125,210,0.2), 0 2px 8px rgba(45,125,210,0.3);
            animation: pulse-node 2s infinite;
        }
        
        @keyframes pulse-node {
            0%, 100% { box-shadow: 0 0 0 4px rgba(45,125,210,0.2), 0 2px 8px rgba(45,125,210,0.3); }
            50% { box-shadow: 0 0 0 8px rgba(45,125,210,0.1), 0 4px 12px rgba(45,125,210,0.4); }
        }
        
        .roadmap-item.locked .roadmap-node {
            background: #e0e0e0;
            color: #999;
        }
        
        .roadmap-info {
            text-align: center;
            font-size: 0.75rem;
            color: #666;
            line-height: 1.4;
        }
        
        .roadmap-info .roadmap-reward {
            font-weight: 600;
            color: #28a745;
        }
        
        .roadmap-info .roadmap-fee {
            font-weight: 600;
            color: #ff9800;
        }
        
        .roadmap-info .roadmap-req {
            color: #999;
            font-size: 0.7rem;
        }
        
        @media (max-width: 1200px) {
            .achievements-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 900px) {
            .achievements-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .gamification-stats {
                grid-template-columns: 1fr;
            }
            
            .achievements-grid {
                grid-template-columns: 1fr;
            }
            
            .rewards-grid {
                grid-template-columns: 1fr;
            }
            
            .req-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="navbar">
            <a href="{{ route('student.dashboard') }}" class="logo">
                <img src="{{asset('images/MentorHub.png')}}" alt="MentorHub Logo" class="logo-img">
            </a>
            <button class="menu-toggle" id="menu-toggle">☰</button>
            <nav class="nav-links" id="nav-links">
                <a href="{{ route('student.dashboard') }}">Dashboard</a>
                <a href="{{route('student.book-session')}}">Book Session</a>
                <a href="{{route('student.my-sessions')}}">Activities</a>
                <a href="{{route('student.schedule')}}">Schedule</a>
            </nav>
            <div class="header-right-section">
                <div class="currency-display">
                    <div class="currency-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="currency-info">
                        <div class="currency-amount" id="currency-amount">₱0.00</div>
                        <div class="currency-label">Balance</div>
                    </div>
                </div>
                <div class="profile-dropdown-container" style="position: relative;">
                    <div class="profile-icon" id="profile-icon">
                        @if($student->profile_picture)
                            <img src="{{ route('student.profile.picture') }}?v={{ time() }}" alt="Profile Picture" class="profile-icon-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center; background-color: #f5f5f5; color: #666; font-weight: bold; font-size: 1.2rem; border-radius: 50%;">{{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}</div>
                        @else
                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                        @endif
                    </div>
                    <div class="dropdown-menu" id="dropdown-menu">
                        <a href="{{ route('student.profile.edit') }}">My Profile</a>
                        <a href="{{ route('student.settings') }}">Achievements</a>
                        <a href="{{ route('student.report-problem') }}">Report a Problem</a>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" method="POST" action="{{ route('student.logout') }}" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <div class="settings-container">
        <div class="settings-header">
            <h1 class="settings-title"><i class="fas fa-trophy"></i> Achievements</h1>
            <p>Track your progress, unlock achievements, and level up!</p>
        </div>
        
        <!-- Gamification Stats -->
        <div class="gamification-stats">
            <div class="stat-card">
                <div class="stat-value">{{ $totalPoints }}</div>
                <div class="stat-label">Total Points</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $unlockedCount }}</div>
                <div class="stat-label">Achievements Unlocked</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ count($userAchievements) }}</div>
                <div class="stat-label">Total Achievements</div>
            </div>
        </div>
        
        <!-- Level Card -->
        <div class="level-card">
            <div class="level-number">Level {{ $level }}</div>
            <p style="margin-bottom: 1.5rem;">Keep learning and completing quests to level up!</p>
            
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <!-- Points Progress -->
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.95rem;">
                        <span style="font-weight: bold;"><i class="fas fa-star" style="color: #ffd700; margin-right: 5px;"></i> Points Progress</span>
                        <span>{{ $pointsForNextLevel > 0 ? $pointsForNextLevel . ' more needed' : 'Completed!' }}</span>
                    </div>
                    <div class="level-progress" style="margin-top: 0; background: rgba(0,0,0,0.2);">
                        @php
                            $pointsProgress = $nextLevelPointsReq > 0 ? min(100, ($totalPoints / $nextLevelPointsReq) * 100) : 100;
                        @endphp
                        <div class="level-progress-bar" style="width: {{ $pointsProgress }}%; {{ $pointsForNextLevel == 0 ? 'background: #28a745;' : 'background: #ffd700;' }}"></div>
                    </div>
                    <div class="level-progress-text" style="text-align: left; margin-top: 0.5rem;">{{ $totalPoints }} / {{ $nextLevelPointsReq }} pts</div>
                </div>

                <!-- Quests Progress -->
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.95rem;">
                        <span style="font-weight: bold;"><i class="fas fa-tasks" style="color: #17a2b8; margin-right: 5px;"></i> Quests (Activities) Progress</span>
                        <span>{{ $questsForNextLevel > 0 ? $questsForNextLevel . ' more needed' : 'Completed!' }}</span>
                    </div>
                    <div class="level-progress" style="margin-top: 0; background: rgba(0,0,0,0.2);">
                        @php
                            $questsProgress = $nextLevelQuestsReq > 0 ? min(100, ($completedQuests / $nextLevelQuestsReq) * 100) : 100;
                        @endphp
                        <div class="level-progress-bar" style="width: {{ $questsProgress }}%; {{ $questsForNextLevel == 0 ? 'background: #28a745;' : 'background: #17a2b8;' }}"></div>
                    </div>
                    <div class="level-progress-text" style="text-align: left; margin-top: 0.5rem;">{{ $completedQuests }} / {{ $nextLevelQuestsReq }} quests completed</div>
                </div>
            </div>
        </div>
        
        <div class="requirements-card">
            <div class="requirements-header">
                <h2 class="section-title" style="margin-bottom: 0;">
                    <i class="fas fa-clipboard-list"></i>
                    Level Up Requirements
                </h2>
                @if($pointsForNextLevel > 0 || $questsForNextLevel > 0)
                    <div class="next-level-badge">
                        <i class="fas fa-arrow-up"></i>
                        Level {{ $level + 1 }} Next
                    </div>
                @else
                    <div class="next-level-badge" style="background: linear-gradient(135deg, #28a745, #20c997); color: white;">
                        <i class="fas fa-check-circle"></i>
                        Requirements Met!
                    </div>
                @endif
            </div>
            
            <div class="req-grid">
                <div class="req-item {{ $pointsForNextLevel == 0 ? 'completed' : 'pending' }}">
                    <div class="req-icon">
                        <i class="fas {{ $pointsForNextLevel == 0 ? 'fa-check-circle' : 'fa-star' }}"></i>
                    </div>
                    <div class="req-details">
                        <div class="req-title">Achievement Points</div>
                        <div class="req-status">
                            @if($pointsForNextLevel == 0)
                                <strong>✓ Completed!</strong> — {{ $totalPoints }}/{{ $nextLevelPointsReq }} pts
                            @else
                                <strong>{{ $totalPoints }}/{{ $nextLevelPointsReq }} pts</strong> — {{ $pointsForNextLevel }} more needed
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="req-item {{ $questsForNextLevel == 0 ? 'completed' : 'pending' }}">
                    <div class="req-icon">
                        <i class="fas {{ $questsForNextLevel == 0 ? 'fa-check-circle' : 'fa-tasks' }}"></i>
                    </div>
                    <div class="req-details">
                        <div class="req-title">Completed Quests (Activities)</div>
                        <div class="req-status">
                            @if($questsForNextLevel == 0)
                                <strong>✓ Completed!</strong> — {{ $completedQuests }}/{{ $nextLevelQuestsReq }} quests
                            @else
                                <strong>{{ $completedQuests }}/{{ $nextLevelQuestsReq }} quests</strong> — {{ $questsForNextLevel }} more needed
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 1.5rem; padding: 1rem; background: linear-gradient(135deg, rgba(45,125,210,0.05), rgba(74,61,217,0.05)); border-radius: 10px; border: 1px dashed rgba(45,125,210,0.3);">
                <div style="font-weight: 600; color: #2d7dd2; margin-bottom: 0.5rem;">
                    <i class="fas fa-lightbulb" style="color: #ffd700;"></i> How to Level Up
                </div>
                <div style="font-size: 0.9rem; color: #555; line-height: 1.6;">
                    You need <strong>both</strong> requirements to reach the next level:
                    <ul style="margin: 0.5rem 0 0 1.5rem;">
                        <li>Earn <strong>{{ $nextLevelPointsReq }} points</strong> by unlocking achievements</li>
                        <li>Complete <strong>{{ $nextLevelQuestsReq }} quests</strong> (submit activities from your tutor)</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="rewards-grid">
            <div class="reward-card booking-reward">
                <div class="reward-icon">🎫</div>
                <div class="reward-value" style="color: #28a745;">{{ $bookingDiscount }}% OFF</div>
                <div class="reward-label">Session Booking Discount</div>
                <p style="font-size: 0.85rem; color: #555; margin-bottom: 1rem; line-height: 1.5;">
                    Every session you book automatically gets a <strong>{{ $bookingDiscount }}%</strong> discount applied to the tutor's rate.
                </p>
                @if($level < 50)
                    <div class="reward-next">
                        <i class="fas fa-arrow-circle-up"></i>
                        <span>Next level: <strong>{{ $nextBookingDiscount }}%</strong> discount</span>
                    </div>
                @else
                    <div class="reward-next" style="background: #d4edda; color: #155724;">
                        <i class="fas fa-crown" style="color: #ffd700;"></i>
                        <span>Maximum discount reached!</span>
                    </div>
                @endif
            </div>
            
            <div class="reward-card withdrawal-reward">
                <div class="reward-icon">💰</div>
                <div class="reward-value" style="color: #ff9800;">{{ $currentWithdrawalFee }}% Fee</div>
                <div class="reward-label">Withdrawal Processing Fee</div>
                <p style="font-size: 0.85rem; color: #555; margin-bottom: 1rem; line-height: 1.5;">
                    @if($withdrawalFeeReduction > 0)
                        Reduced from <strong style="text-decoration: line-through;">10%</strong> to <strong>{{ $currentWithdrawalFee }}%</strong> — you save {{ $withdrawalFeeReduction }}% on every cash out!
                    @else
                        Standard 10% processing fee. Level up to reduce this!
                    @endif
                </p>
                @if($currentWithdrawalFee > 2)
                    <div class="reward-next">
                        <i class="fas fa-arrow-circle-up"></i>
                        <span>Next level: <strong>{{ $nextWithdrawalFee }}%</strong> fee</span>
                    </div>
                @else
                    <div class="reward-next" style="background: #d4edda; color: #155724;">
                        <i class="fas fa-crown" style="color: #ffd700;"></i>
                        <span>Minimum fee reached!</span>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="roadmap-section">
            <h2 class="section-title">
                <i class="fas fa-road"></i>
                Level Progression Roadmap
            </h2>
            <div class="roadmap-timeline">
                @foreach($levelMilestones as $milestone)
                    <div class="roadmap-item {{ $milestone['is_current'] ? 'current' : ($milestone['is_unlocked'] ? 'unlocked' : 'locked') }}">
                        <div class="roadmap-node">
                            @if($milestone['is_current'])
                                <i class="fas fa-user" style="font-size: 0.8rem;"></i>
                            @elseif($milestone['is_unlocked'])
                                <i class="fas fa-check" style="font-size: 0.8rem;"></i>
                            @else
                                {{ $milestone['level'] }}
                            @endif
                        </div>
                        <div class="roadmap-info">
                            <div style="font-weight: 600; margin-bottom: 2px;">Lv.{{ $milestone['level'] }}</div>
                            <div class="roadmap-reward">{{ $milestone['booking_discount'] }}% off</div>
                            <div class="roadmap-fee">{{ $milestone['withdrawal_fee'] }}% fee</div>
                            @if(!$milestone['is_unlocked'])
                                <div class="roadmap-req">{{ $milestone['points_required'] }}pts · {{ $milestone['quests_required'] }}q</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        <div class="achievements-section">
            <h2 class="section-title">
                <i class="fas fa-trophy"></i>
                Achievements
            </h2>
            
            <div class="achievements-grid">
                @foreach($userAchievements as $item)
                    @php
                        $achievement = $item['achievement'];
                        $userAchievement = $item['user_achievement'];
                        $isUnlocked = $userAchievement->is_unlocked ?? false;
                    @endphp
                    <div class="achievement-card {{ $isUnlocked ? 'unlocked' : 'locked' }}">
                        @if($isUnlocked)
                            <div class="unlocked-badge"><i class="fas fa-check"></i> Unlocked</div>
                        @endif
                        <div class="achievement-points">+{{ $achievement->points }} pts</div>
                        <div class="achievement-icon">
                            <i class="{{ $achievement->icon ?? 'fas fa-star' }}"></i>
                        </div>
                        <div class="achievement-name">{{ $achievement->name }}</div>
                        <div class="achievement-description">{{ $achievement->description }}</div>
                        @if(!$isUnlocked)
                            <div class="achievement-progress">
                                <div class="achievement-progress-bar" style="width: {{ $userAchievement->progress ?? 0 }}%"></div>
                            </div>
                            <div class="achievement-progress-text">{{ $userAchievement->progress ?? 0 }}% Complete</div>
                        @else
                            <div style="color: #28a745; font-weight: bold; margin-top: 0.5rem;">
                                <i class="fas fa-check-circle"></i> Completed!
                            </div>
                        @endif
                    </div>
                @endforeach
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
    
    @include('layouts.footer-js')
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Profile dropdown
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
            
            // Mobile menu toggle
            const menuToggle = document.getElementById('menu-toggle');
            const navLinks = document.getElementById('nav-links');
            
            if (menuToggle && navLinks) {
                menuToggle.addEventListener('click', function() {
                    navLinks.classList.toggle('active');
                });
            }
            
            // Currency display functionality
            initializeCurrencyDisplay();
            loadCurrencyData();
        });
        
        function initializeCurrencyDisplay() {
            const currencyDisplay = document.querySelector('.currency-display');
            if (currencyDisplay) {
                currencyDisplay.addEventListener('click', function() {
                    window.location.href = "{{ route('student.wallet') }}";
                });
            }
        }
        
        // Load currency data from API
        function loadCurrencyData() {
            fetch('{{ route("student.wallet.balance") }}')
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
        }
    </script>
</body>
</html>

