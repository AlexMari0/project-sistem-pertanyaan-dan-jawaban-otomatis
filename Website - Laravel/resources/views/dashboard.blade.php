@extends('layouts.app')

@section('content')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #fcb045 0%, #fd1d1d 100%);
            --info-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --danger-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --card-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            --card-hover-shadow: 0 12px 40px rgba(31, 38, 135, 0.25);
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --border-radius: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .dashboard-header {
            background: var(--primary-gradient);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(50px, -50px);
        }

        .dashboard-header h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            position: relative;
            z-index: 2;
        }

        .dashboard-subtitle {
            opacity: 0.9;
            font-size: 1.1rem;
            margin-top: 0.5rem;
            position: relative;
            z-index: 2;
        }

        .stats-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border: none;
            overflow: hidden;
            position: relative;
            height: 100%;
        }

        .stats-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--card-hover-shadow);
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .stats-card.success::before {
            background: var(--success-gradient);
        }

        .stats-card.warning::before {
            background: var(--warning-gradient);
        }

        .stats-card.info::before {
            background: var(--info-gradient);
        }

        .stats-card-body {
            padding: 2rem;
            position: relative;
        }

        .stats-icon {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            opacity: 0.9;
        }

        .stats-icon.primary {
            background: var(--primary-gradient);
        }

        .stats-icon.success {
            background: var(--success-gradient);
        }

        .stats-icon.warning {
            background: var(--warning-gradient);
        }

        .stats-icon.info {
            background: var(--info-gradient);
        }

        .stats-title {
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .stats-number {
            color: var(--text-primary);
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stats-change {
            font-size: 0.85rem;
            font-weight: 500;
        }

        .stats-change.positive {
            color: #10b981;
        }

        .stats-change.negative {
            color: #ef4444;
        }

        .chart-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border: none;
            overflow: hidden;
            height: 100%;
        }

        .chart-card:hover {
            box-shadow: var(--card-hover-shadow);
        }

        .chart-card-header {
            padding: 1.5rem 1.5rem 0;
            border-bottom: none;
        }

        .chart-card-title {
            color: var(--text-primary);
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        .chart-card-body {
            padding: 1.5rem;
            position: relative;
        }

        .chart-container {
            position: relative;
            height: 350px;
        }

        .welcome-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            margin-top: 1rem;
        }

        .welcome-badge i {
            margin-right: 0.5rem;
        }

        .grid-margin {
            margin-bottom: 2rem;
        }

        .fade-in {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .fade-in:nth-child(1) {
            animation-delay: 0.1s;
        }

        .fade-in:nth-child(2) {
            animation-delay: 0.2s;
        }

        .fade-in:nth-child(3) {
            animation-delay: 0.3s;
        }

        .fade-in:nth-child(4) {
            animation-delay: 0.4s;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-left: 1rem;
            text-transform: capitalize;
        }

        .chart-legend {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .legend-item {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
            margin-right: 0.5rem;
        }

        @media (max-width: 768px) {
            .dashboard-header {
                padding: 1.5rem;
                text-align: center;
            }

            .dashboard-header h3 {
                font-size: 1.5rem;
            }

            .stats-card-body {
                padding: 1.5rem;
            }

            .stats-number {
                font-size: 2rem;
            }

            .chart-container {
                height: 300px;
            }
        }
    </style>

    <div class="container-fluid px-4">
        <!-- Enhanced Header -->
        <div class="dashboard-header fade-in">
            <h3>Welcome back, {{ Auth::user()->username }}!
                <span class="role-badge">{{ Auth::user()->role }}</span>
            </h3>
            <p class="dashboard-subtitle">
                @if (auth()->user()->role == 'teacher')
                    Manage your students and track their learning progress
                @else
                    Track your learning journey and achievements
                @endif
            </p>
            <div class="welcome-badge">
                <i class="fas fa-calendar-alt"></i>
                {{ now()->format('l, F j, Y') }}
            </div>
        </div>

        @if (auth()->user()->role == 'teacher')
            <!-- Teacher Stats Cards -->
            <div class="row">
                <div class="col-md-4 grid-margin fade-in">
                    <div class="stats-card success">
                        <div class="stats-card-body">
                            <div class="stats-icon success">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <p class="stats-title">Reading Materials</p>
                            <p class="stats-number" id="materialCount">{{ $readingmaterialsCount }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 grid-margin fade-in">
                    <div class="stats-card warning">
                        <div class="stats-card-body">
                            <div class="stats-icon warning">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <p class="stats-title">Total Quiz</p>
                            <p class="stats-number" id="questionCount">{{ $quizCount }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 grid-margin fade-in">
                    <div class="stats-card info">
                        <div class="stats-card-body">
                            <div class="stats-icon info">
                                <i class="fas fa-users"></i>
                            </div>
                            <p class="stats-title">Active Students</p>
                            <p class="stats-number" id="studentCount">{{ $studentCount }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Teacher Charts - Only Students by Year -->
            <div class="row">
                <div class="col-md-12 grid-margin fade-in">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h4 class="chart-card-title">
                                <i class="fas fa-chart-pie me-2"></i>
                                Students by Year
                            </h4>
                        </div>
                        <div class="chart-card-body">
                            <div class="chart-container">
                                <canvas id="chartStudentsPerYear"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (auth()->user()->role == 'student')
            <!-- No charts for students -->
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
        // Enhanced Chart Configuration
        Chart.register(ChartDataLabels);

        const chartColors = {
            primary: ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe'],
            success: ['#11998e', '#38ef7d'],
            gradient: {
                primary: function(ctx) {
                    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                    gradient.addColorStop(0, 'rgba(102, 126, 234, 0.8)');
                    gradient.addColorStop(1, 'rgba(118, 75, 162, 0.2)');
                    return gradient;
                }
            }
        };

        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed.toLocaleString();
                        }
                    }
                }
            }
        };

        // Animate numbers
        function animateNumber(element, target, duration = 2000) {
            let start = 0;
            const increment = target / (duration / 16);
            const timer = setInterval(() => {
                start += increment;
                if (start >= target) {
                    start = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(start).toLocaleString();
            }, 16);
        }

        // Animate stats on page load
        document.addEventListener('DOMContentLoaded', function() {
            @if (auth()->user()->role == 'teacher')
                const materialElement = document.getElementById('materialCount');
                const questionElement = document.getElementById('questionCount');
                const studentElement = document.getElementById('studentCount');

                if (materialElement) animateNumber(materialElement, {{ $readingmaterialsCount }});
                if (questionElement) animateNumber(questionElement, {{ $quizCount }});
                if (studentElement) animateNumber(studentElement, {{ $studentCount }});
            @endif
        });

        // Teacher Charts
        @if (auth()->user()->role == 'teacher')
            // Students Per Year Chart with Real Data
            const ctx1 = document.getElementById('chartStudentsPerYear').getContext('2d');
            new Chart(ctx1, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($studentsByYear->pluck('year')->toArray()) !!},
                    datasets: [{
                        data: {!! json_encode($studentsByYear->pluck('count')->toArray()) !!},
                        backgroundColor: chartColors.primary,
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    ...commonOptions,
                    cutout: '60%',
                    plugins: {
                        ...commonOptions.plugins,
                        title: {
                            display: true,
                            text: 'Distribution by Academic Year',
                            font: {
                                size: 14,
                                weight: '600'
                            },
                            padding: {
                                bottom: 20
                            }
                        },
                        datalabels: {
                            color: 'white',
                            font: {
                                weight: 'bold',
                                size: 12
                            },
                            formatter: (value, context) => {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return percentage + '%';
                            }
                        }
                    }
                }
            });
        @endif
    </script>
@endpush