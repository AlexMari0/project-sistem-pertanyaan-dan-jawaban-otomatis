@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-5">
        <!-- Hero Section -->
        <div class="hero-section mb-4 px-3 px-md-4">
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h1 class="display-5 fw-bold text-gradient mb-2">Course</h1>
                </div>
                @if (auth()->check() && auth()->user()->role === 'teacher')
                    <div class="d-flex gap-2">
                        <a href="{{ route('courses.create') }}" class="btn btn-primary btn-lg rounded-pill px-4 shadow-hover">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Kursus
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Alert Section - Menyesuaikan dengan hero-section -->
        @if (session('success'))
            <div id="success-alert" class="alert alert-success alert-modern shadow-sm p-4 rounded-3 mx-3 mx-md-4 mb-4"
                role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-3 fs-3 text-success"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold fs-5">Berhasil!</div>
                        <div class="fs-6">{{ session('success') }}</div>
                    </div>
                    <button type="button" class="btn-close ms-3" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <!-- Controls Section -->
        <div class="controls-section mb-5">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="stats-card">
                        <i class="bi bi-collection text-primary fs-4 me-2"></i>
                        <span class="text-muted">Total Kursus:</span>
                        <span class="fw-bold text-primary fs-5">{{ $courses->count() }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <form method="GET" action="{{ route('courses.index') }}" class="sort-form">
                        <div class="row g-2">
                            <div class="col-auto">
                                <label for="sort_by" class="form-label text-muted small mb-1">Urutkan berdasarkan:</label>
                                <select name="sort_by" id="sort_by" class="form-select form-select-modern"
                                    style="width: 170px;" onchange="this.form.submit()">
                                    <option value="title" {{ request('sort_by') == 'title' ? 'selected' : '' }}>Nama Kursus
                                    </option>
                                    <option value="last_accessed"
                                        {{ request('sort_by') == 'last_accessed' ? 'selected' : '' }}>Terakhir Diakses
                                    </option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <label for="sort_order" class="form-label text-muted small mb-1">Urutan:</label>
                                <select name="sort_order" id="sort_order" class="form-select form-select-modern"
                                    style="width: 80px;" onchange="this.form.submit()">
                                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>A-Z
                                    </option>
                                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Z-A
                                    </option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Courses Grid -->
        <div class="courses-grid">
            @php $user = auth()->user(); @endphp
            @forelse ($courses as $course)
                <div class="course-card-wrapper">
                    <div class="course-card">
                        <div class="course-card-header">
                            <div class="course-icon">
                                <i class="bi bi-journal-bookmark"></i>
                            </div>
                            @if ($user && $user->role === 'teacher')
                                <div class="course-badge">
                                    <span class="badge bg-success">Instruktur</span>
                                </div>
                            @elseif ($user && $user->courses->contains($course->id))
                                <div class="course-badge">
                                    <span class="badge bg-primary">Terdaftar</span>
                                </div>
                            @endif
                        </div>

                        <div class="course-card-body">
                            <h3 class="course-title">{{ $course->title }}</h3>
                            <p class="course-description">{{ Str::limit($course->description, 120) }}</p>

                            <div class="course-meta">
                                <div class="meta-item">
                                    <i class="bi bi-people"></i>
                                    <span>{{ $course->students->count() }} Siswa</span>
                                </div>
                                <div class="meta-item">
                                    <i class="bi bi-calendar3"></i>
                                    <span>{{ $course->created_at->format('M Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="course-card-footer">
                            @if ($user && $user->role !== 'teacher')
                                @if ($user->courses->contains($course->id))
                                    <a href="{{ route('courses.show', $course->id) }}" class="btn btn-primary btn-access">
                                        <i class="bi bi-play-circle me-2"></i>Lanjutkan Belajar
                                    </a>
                                @else
                                    <button class="btn btn-outline-primary btn-join" data-bs-toggle="modal"
                                        data-bs-target="#courseModal{{ $course->id }}">
                                        <i class="bi bi-plus-circle me-2"></i>Gabung Kursus
                                    </button>
                                @endif
                            @elseif ($user && $user->role === 'teacher')
                                <a href="{{ route('courses.show', $course->id) }}" class="btn btn-primary btn-access">
                                    <i class="bi bi-gear me-2"></i>Kelola Kursus
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Enhanced Modal -->
                @if ($user && $user->role !== 'teacher' && !$user->courses->contains($course->id))
                    <div class="modal fade" id="courseModal{{ $course->id }}" tabindex="-1"
                        aria-labelledby="modalLabel{{ $course->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content modal-modern">
                                <form action="{{ route('courses.join') }}" method="POST" class="needs-validation"
                                    novalidate>
                                    @csrf
                                    <input type="hidden" name="course_id" value="{{ $course->id }}">

                                    <div class="modal-header">
                                        <div class="modal-icon">
                                            <i class="bi bi-door-open"></i>
                                        </div>
                                        <div>
                                            <h5 class="modal-title" id="modalLabel{{ $course->id }}">Gabung ke Kursus
                                            </h5>
                                            <p class="modal-subtitle">{{ $course->title }}</p>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Tutup"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="course-info">
                                            <p class="course-description">{{ $course->description }}</p>
                                        </div>

                                        <div class="form-group">
                                            <label for="password{{ $course->id }}" class="form-label">
                                                <i class="bi bi-key me-2"></i>Password Kursus
                                            </label>
                                            <div class="input-group">
                                                <input type="password" name="password"
                                                    class="form-control form-control-modern"
                                                    id="password{{ $course->id }}"
                                                    placeholder="Masukkan password kursus" required>
                                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback">
                                                Password kursus harus diisi.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="bi bi-x-circle me-2"></i>Batal
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle me-2"></i>Gabung Sekarang
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="bi bi-collection"></i>
                    </div>
                    <h3>Belum Ada Kursus</h3>
                    <p>Saat ini belum ada kursus yang tersedia. Silakan cek kembali nanti.</p>
                    @if (auth()->check() && auth()->user()->role === 'teacher')
                        <a href="{{ route('courses.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Buat Kursus Pertama
                        </a>
                    @endif
                </div>
            @endforelse
        </div>
    </div>

    <!-- Enhanced Styles -->
    @push('styles')
        <style>
            :root {
                --primary-color: #1CB2A2;
                --primary-dark: #179a8a;
                --secondary-color: #6c757d;
                --success-color: #28a745;
                --warning-color: #ffc107;
                --danger-color: #dc3545;
                --light-color: #f8f9fa;
                --dark-color: #212529;
                --border-radius: 12px;
                --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                --box-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }

            /* Typography */
            .text-gradient {
                background: linear-gradient(135deg, var(--primary-color), #20c997);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            /* Hero Section */
            .hero-section {
                background: linear-gradient(135deg, rgba(24, 160, 140, 0.2), rgba(20, 180, 140, 0.2));
                border-radius: var(--border-radius);
                padding: 2rem;
                margin-bottom: 2rem;
            }

            /* Alert Modern */
            .alert-modern {
                border: none;
                border-radius: var(--border-radius);
                border-left: 4px solid var(--success-color);
                background: linear-gradient(135deg, rgba(40, 167, 69, 0.05), rgba(40, 167, 69, 0.1));
            }

            /* Controls Section */
            .controls-section {
                background: white;
                border-radius: var(--border-radius);
                padding: 1.5rem;
                box-shadow: var(--box-shadow);
                margin-bottom: 2rem;
            }

            .stats-card {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 1rem;
                background: linear-gradient(135deg, rgba(28, 178, 162, 0.05), rgba(32, 201, 151, 0.05));
                border-radius: var(--border-radius);
                border: 1px solid rgba(28, 178, 162, 0.1);
            }

            .form-select-modern {
                border-radius: 8px;
                border: 1px solid #e0e0e0;
                padding: 0.5rem 1rem;
                transition: all 0.3s ease;
            }

            .form-select-modern:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 0.2rem rgba(28, 178, 162, 0.25);
            }

            /* Courses Grid */
            .courses-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
                gap: 2rem;
            }

            .course-card-wrapper {
                height: 100%;
            }

            .course-card {
                background: white;
                border-radius: var(--border-radius);
                box-shadow: var(--box-shadow);
                transition: all 0.3s ease;
                height: 100%;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                position: relative;
            }

            .course-card:hover {
                transform: translateY(-4px);
                box-shadow: var(--box-shadow-lg);
            }

            .course-card-header {
                padding: 1.5rem 1.5rem 0;
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
            }

            .course-icon {
                width: 48px;
                height: 48px;
                background: linear-gradient(135deg, var(--primary-color), #20c997);
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.5rem;
            }

            .course-badge .badge {
                font-size: 0.75rem;
                padding: 0.5rem 1rem;
                border-radius: 20px;
            }

            .course-card-body {
                padding: 1.5rem;
                flex-grow: 1;
            }

            .course-title {
                font-size: 1.25rem;
                font-weight: 600;
                margin-bottom: 0.75rem;
                color: var(--dark-color);
                line-height: 1.3;
            }

            .course-description {
                color: var(--secondary-color);
                line-height: 1.6;
                margin-bottom: 1.5rem;
            }

            .course-meta {
                display: flex;
                gap: 1rem;
                margin-bottom: 1rem;
            }

            .meta-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: var(--secondary-color);
                font-size: 0.875rem;
            }

            .meta-item i {
                color: var(--primary-color);
            }

            .course-card-footer {
                padding: 0 1.5rem 1.5rem;
            }

            .btn-access,
            .btn-join {
                width: 100%;
                padding: 0.75rem 1.5rem;
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .btn-access {
                background: var(--primary-color);
                border-color: var(--primary-color);
            }

            .btn-access:hover {
                background: var(--primary-dark);
                border-color: var(--primary-dark);
                transform: translateY(-1px);
            }

            .btn-join {
                border-color: var(--primary-color);
                color: var(--primary-color);
            }

            .btn-join:hover {
                background: var(--primary-color);
                border-color: var(--primary-color);
                color: white;
                transform: translateY(-1px);
            }

            /* Enhanced Modal */
            .modal-modern .modal-content {
                border: none;
                border-radius: var(--border-radius);
                box-shadow: var(--box-shadow-lg);
                overflow: hidden;
            }

            .modal-modern .modal-header {
                background: linear-gradient(135deg, rgba(28, 178, 162, 0.05), rgba(32, 201, 151, 0.05));
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
                padding: 1.5rem;
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .modal-icon {
                width: 48px;
                height: 48px;
                background: var(--primary-color);
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.5rem;
                flex-shrink: 0;
            }

            .modal-title {
                font-size: 1.25rem;
                font-weight: 600;
                margin-bottom: 0.25rem;
                color: var(--dark-color);
            }

            .modal-subtitle {
                color: var(--secondary-color);
                margin: 0;
                font-size: 0.875rem;
            }

            .modal-modern .modal-body {
                padding: 1.5rem;
            }

            .course-info {
                background: var(--light-color);
                border-radius: 8px;
                padding: 1rem;
                margin-bottom: 1.5rem;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .form-control-modern {
                border-radius: 8px;
                border: 1px solid #e0e0e0;
                padding: 0.75rem 1rem;
                transition: all 0.3s ease;
            }

            .form-control-modern:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 0.2rem rgba(28, 178, 162, 0.25);
            }

            .toggle-password {
                border-left: none;
                border-radius: 0 8px 8px 0;
            }

            .modal-modern .modal-footer {
                background: var(--light-color);
                border-top: 1px solid rgba(0, 0, 0, 0.05);
                padding: 1.5rem;
                gap: 1rem;
            }

            .modal-modern .modal-footer .btn {
                padding: 0.75rem 1.5rem;
                border-radius: 8px;
                font-weight: 500;
            }

            /* Empty State */
            .empty-state {
                grid-column: 1 / -1;
                text-align: center;
                padding: 4rem 2rem;
                background: white;
                border-radius: var(--border-radius);
                box-shadow: var(--box-shadow);
            }

            .empty-icon {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, rgba(28, 178, 162, 0.1), rgba(32, 201, 151, 0.1));
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1.5rem;
                font-size: 2rem;
                color: var(--primary-color);
            }

            .empty-state h3 {
                color: var(--dark-color);
                margin-bottom: 0.75rem;
            }

            .empty-state p {
                color: var(--secondary-color);
                margin-bottom: 1.5rem;
            }

            /* Shadow Hover Effect */
            .shadow-hover {
                transition: all 0.3s ease;
            }

            .shadow-hover:hover {
                transform: translateY(-2px);
                box-shadow: var(--box-shadow-lg);
            }

            /* Responsive */
            @media (max-width: 768px) {
                .courses-grid {
                    grid-template-columns: 1fr;
                    gap: 1.5rem;
                }

                .hero-section {
                    padding: 1.5rem;
                }

                .controls-section {
                    padding: 1rem;
                }

                .sort-form .row {
                    flex-direction: column;
                }

                .sort-form .col-auto {
                    width: 100%;
                    margin-bottom: 1rem;
                }

                .stats-card {
                    justify-content: center;
                    text-align: center;
                }
            }

            /* Accessibility */
            @media (prefers-reduced-motion: reduce) {
                * {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                }
            }

            /* Focus states */
            .btn:focus,
            .form-control:focus,
            .form-select:focus {
                outline: 2px solid var(--primary-color);
                outline-offset: 2px;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Auto-hide success alert
                const successAlert = document.getElementById('success-alert');
                if (successAlert) {
                    setTimeout(() => {
                        successAlert.classList.add('fade');
                        setTimeout(() => {
                            successAlert.remove();
                        }, 300);
                    }, 5000);
                }

                // Toggle password visibility
                document.querySelectorAll('.toggle-password').forEach(button => {
                    button.addEventListener('click', function() {
                        const input = this.parentElement.querySelector('input');
                        const icon = this.querySelector('i');

                        if (input.type === 'password') {
                            input.type = 'text';
                            icon.classList.remove('bi-eye');
                            icon.classList.add('bi-eye-slash');
                        } else {
                            input.type = 'password';
                            icon.classList.remove('bi-eye-slash');
                            icon.classList.add('bi-eye');
                        }
                    });
                });

                // Form validation
                document.querySelectorAll('.needs-validation').forEach(form => {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    });
                });

                // Smooth scrolling for better UX
                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', function(e) {
                        e.preventDefault();
                        const target = document.querySelector(this.getAttribute('href'));
                        if (target) {
                            target.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    });
                });

                // Add loading state to buttons
                document.querySelectorAll('form').forEach(form => {
                    form.addEventListener('submit', function() {
                        const submitBtn = form.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML =
                                '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
