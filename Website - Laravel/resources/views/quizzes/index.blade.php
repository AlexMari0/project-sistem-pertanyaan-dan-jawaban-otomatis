@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <!-- Header Section with Breadcrumb -->
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb bg-transparent p-0 m-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('courses.index') }}" class="text-decoration-none text-muted">
                                <i class="fas fa-home fa-sm me-1"></i>Courses
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('courses.show', $course->id) }}" class="text-decoration-none text-muted">
                                {{ Str::limit($course->title, 30) }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active text-primary" aria-current="page">Kuis</li>
                    </ol>
                </nav>

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h1 class="display-6 fw-bold text-dark mb-2">
                            <i class="fas fa-quiz text-primary me-2"></i>
                            Daftar Kuis
                        </h1>
                        <p class="text-muted mb-0 fs-5">
                            <span class="fw-medium">Course:</span> {{ $course->title }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Alert -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Main Content -->
        <div class="row">
            <div class="col-12">
                @if ($quizzes->isEmpty())
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <div class="bg-light rounded-circle mx-auto mb-3"
                                style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-quiz text-muted" style="font-size: 2.5rem;"></i>
                            </div>
                            <h3 class="text-muted mb-2">Belum Ada Kuis</h3>
                            <p class="text-muted mb-4">
                                Belum ada kuis yang dibuat untuk course ini.<br>
                                Mulai buat kuis pertama untuk melengkapi pembelajaran.
                            </p>

                            @can('create-quiz')
                                <a href="{{ route('quizzes.create', $course->id) }}" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus me-2"></i>Buat Kuis Pertama
                                </a>
                            @endcan
                        </div>
                    </div>
                @else
                    <!-- Quiz Grid -->
                    <div class="row g-4 mb-4">
                        @foreach ($quizzes as $index => $quiz)
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="card h-100 border-0 shadow-sm hover-shadow-lg transition-all">
                                    <!-- Quiz Number Badge -->
                                    <div class="position-relative">
                                        <!-- Quiz Status Badge -->
                                        <div class="position-absolute top-0 end-0 m-3">
                                            @if ($quiz->is_active ?? true)
                                                <span class="badge bg-success rounded-pill px-2 py-1">
                                                    <i class="fas fa-check fa-xs me-1"></i>Aktif
                                                </span>
                                            @else
                                                <span class="badge bg-secondary rounded-pill px-2 py-1">
                                                    <i class="fas fa-pause fa-xs me-1"></i>Draft
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="card-body pt-5">
                                        <!-- Quiz Title -->
                                        <h5 class="card-title fw-bold text-dark mb-3 line-clamp-2">
                                            {{ $quiz->title }}
                                        </h5>

                                        <!-- Quiz Description -->
                                        <p class="text-muted mb-3 line-clamp-3" style="min-height: 4.5rem;">
                                            {{ $quiz->description ?: 'Tidak ada deskripsi untuk kuis ini.' }}
                                        </p>

                                        <!-- Quiz Metadata -->
                                        <div class="row g-2 mb-4 small text-muted">
                                            @if (isset($quiz->questions_count))
                                                <div class="col-6">
                                                    <i class="fas fa-question-circle me-1"></i>
                                                    {{ $quiz->questions_count ?? 0 }} Soal
                                                </div>
                                            @endif

                                            @if (isset($quiz->time_limit))
                                                <div class="col-6">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $quiz->time_limit ?? 'Tidak terbatas' }}
                                                </div>
                                            @endif

                                            @if (isset($quiz->attempts_count))
                                                <div class="col-6">
                                                    <i class="fas fa-users me-1"></i>
                                                    {{ $quiz->attempts_count ?? 0 }} Peserta
                                                </div>
                                            @endif

                                            <div class="col-6">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ $quiz->created_at ? $quiz->created_at->format('d M Y') : 'Tidak diketahui' }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card Actions -->
                                    <div class="card-footer bg-transparent border-0 pt-0">
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('quizzes.show', [$course->id, $quiz->id]) }}"
                                                class="btn btn-primary flex-fill">
                                                <i class="fas fa-eye me-1"></i>
                                                Lihat Detail
                                            </a>

                                            @can('edit-quiz')
                                                <a href="{{ route('quizzes.edit', [$course->id, $quiz->id]) }}"
                                                    class="btn btn-outline-secondary" title="Edit Kuis">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Bottom Actions -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between align-items-center">
                    <a href="{{ route('courses.show', $course->id) }}"
                        class="btn btn-outline-secondary btn-lg order-2 order-sm-1">
                        <i class="fas fa-arrow-left me-2"></i>
                        Kembali ke Course
                    </a>

                    @if (!$quizzes->isEmpty())
                        @can('create-quiz')
                            <a href="{{ route('quizzes.create', $course->id) }}"
                                class="btn btn-primary btn-lg order-1 order-sm-2">
                                <i class="fas fa-plus me-2"></i>
                                Tambah Kuis Baru
                            </a>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Custom Styles */
        .hover-shadow-lg {
            transition: all 0.3s ease;
        }

        .hover-shadow-lg:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .transition-all {
            transition: all 0.3s ease;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: "›";
            font-weight: bold;
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .display-6 {
                font-size: 1.75rem;
            }

            .col-xl-4 {
                min-height: auto;
            }
        }

        /* Loading animation for future use */
        .quiz-card-loading {
            background: linear-gradient(90deg, #f8f9fa 25%, #e9ecef 50%, #f8f9fa 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }
    </style>
@endsection