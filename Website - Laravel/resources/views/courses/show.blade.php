@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Hero Section with Gradient Background -->
        <div class="hero-section position-relative overflow-hidden rounded-4 mb-5"
            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 200px;">
            <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10"
                style="background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
                <defs>
                    <pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse">
                        <circle cx="25" cy="25" r="1" fill="white" opacity="0.1" />
                        <circle cx="75" cy="25" r="1" fill="white" opacity="0.1" />
                        <circle cx="50" cy="50" r="1" fill="white" opacity="0.1" />
                        <circle cx="25" cy="75" r="1" fill="white" opacity="0.1" />
                        <circle cx="75" cy="75" r="1" fill="white" opacity="0.1" />
                    </pattern>
                </defs>
            </div>

            <div class="position-relative p-5 text-white">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-3">
                            <div class="badge bg-white bg-opacity-20 text-primary rounded-pill px-3 py-2 me-3">
                                <i class="bi bi-book-fill me-1"></i>Course
                            </div>
                            <span class="badge bg-success bg-opacity-20 text-white rounded-pill px-3 py-2">
                                <i class="bi bi-people-fill me-1"></i>{{ $course->students->count() }} Peserta
                            </span>
                        </div>
                        <h1 class="display-5 fw-bold mb-3 text-white">{{ $course->title }}</h1>
                        <p class="lead mb-0 text-white-50">
                            <i class="bi bi-person-fill me-2"></i>
                            Diajar oleh {{ $course->teacher ? $course->teacher->username : 'Belum ada guru' }}
                        </p>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('courses.index') }}"
                            class="btn btn-light btn-lg rounded-pill shadow-sm hover-lift">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </a>
                        @if ($course->quizzes->count() > 0)
                            <a href="{{ route('quizzes.index', $course->id) }}"
                                class="btn btn-warning btn-lg rounded-pill shadow-sm hover-lift">
                                <i class="bi bi-list-ul me-2"></i>Lihat Kuis ({{ $course->quizzes->count() }})
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Message with Enhanced Design -->
        @if (session('success'))
            <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-2 me-3">
                            <i class="bi bi-gear-fill text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Course Description -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 hover-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                                <i class="bi bi-file-text-fill text-primary"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-0">Deskripsi Course</h5>
                        </div>
                        <p class="card-text text-muted lh-lg">{{ $course->description }}</p>
                    </div>
                </div>

                <!-- Reading Materials Section -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 hover-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-purple bg-opacity-10 p-2 me-3">
                                    <i class="bi bi-journal-text text-purple"></i>
                                </div>
                                <h5 class="card-title fw-bold mb-0">Materi Bacaan</h5>
                            </div>
                            @if (auth()->check() && auth()->user()->role === 'teacher')
                                <button class="btn btn-outline-purple rounded-pill hover-lift" data-bs-toggle="modal"
                                    data-bs-target="#addReadingModal">
                                    <i class="bi bi-plus-circle me-2"></i>Tambah Materi
                                </button>
                            @endif
                        </div>

                        <!-- Reading Materials List -->
                        @if (isset($course->reading_materials) && $course->reading_materials->count() > 0)
                            <div class="row g-3">
                                @foreach ($course->reading_materials as $index => $material)
                                    <div class="col-md-6 col-lg-4">
                                        <div
                                            class="card reading-material-card h-100 border-0 shadow-sm rounded-3 hover-card-subtle position-relative">
                                            <!-- Clickable overlay -->
                                            <a href="{{ route('reading-materials.show', $material->id) }}"
                                                class="stretched-link text-decoration-none"></a>

                                            <div class="card-body p-4">
                                                <div class="d-flex align-items-start mb-3">
                                                    <div
                                                        class="rounded-circle bg-purple bg-opacity-20 p-2 me-3 flex-shrink-0">
                                                        <i class="bi bi-book text-purple"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="card-title fw-semibold mb-2 line-clamp-2">
                                                            {{ $material->title }}</h6>
                                                        <small class="text-muted">
                                                            <i
                                                                class="bi bi-clock me-1"></i>{{ $material->reading_time ?? '5' }}
                                                            menit baca
                                                        </small>
                                                    </div>
                                                </div>

                                                <!-- Preview content -->
                                                <p class="card-text text-muted small line-clamp-3 mb-3">
                                                    {{ Str::limit(strip_tags($material->content), 100) }}
                                                </p>

                                                <!-- Attachments indicator -->
                                                @if ($material->attachments)
                                                    <div class="mb-3">
                                                        <span class="badge bg-light text-dark border">
                                                            <i class="bi bi-paperclip me-1"></i>
                                                            {{ count(json_decode($material->attachments)) }} Lampiran
                                                        </span>
                                                    </div>
                                                @endif

                                                <!-- Bottom actions -->
                                                <div class="d-flex justify-content-between align-items-center position-relative"
                                                    style="min-height: 40px;">
                                                    {{-- Tombol Baca Materi --}}
                                                    <a href="{{ route('reading-materials.show', $material->id) }}"
                                                        class="btn btn-outline-purple btn-sm rounded-pill d-inline-flex align-items-center px-3 py-1"
                                                        style="min-width: 120px;">
                                                        <i class="bi bi-eye me-1"></i> Baca Materi
                                                    </a>

                                                    {{-- Tombol Aksi untuk Teacher --}}
                                                    @if (auth()->check() && auth()->user()->role === 'teacher')
                                                        <div class="dropdown position-absolute"
                                                            style="right: 0; top: 50%; transform: translateY(-50%); z-index: 10;">
                                                            <button
                                                                class="btn btn-outline-secondary btn-sm d-inline-flex justify-content-center align-items-center rounded-circle p-0"
                                                                type="button" data-bs-toggle="dropdown"
                                                                aria-expanded="false" style="width: 32px; height: 32px;"
                                                                onclick="event.stopPropagation();">
                                                                <i class="bi bi-three-dots-vertical"></i>
                                                            </button>

                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <form
                                                                        action="{{ route('reading-materials.destroy', $material->id) }}"
                                                                        method="POST"
                                                                        onsubmit="event.stopPropagation(); return confirm('Hapus materi ini?')">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit"
                                                                            class="dropdown-item text-danger">
                                                                            <i class="bi bi-trash me-2"></i>Hapus
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="rounded-circle bg-purple bg-opacity-10 mx-auto mb-3 d-flex justify-content-center align-items-center"
                                    style="width: 80px; height: 80px;">
                                    <i class="bi bi-journal-text text-purple" style="font-size: 2rem;"></i>
                                </div>
                                <h6 class="fw-semibold mb-2">Belum Ada Materi Bacaan</h6>
                                <p class="text-muted mb-0">
                                    @if (auth()->check() && auth()->user()->role === 'teacher')
                                        Klik tombol "Tambah Materi" untuk menambahkan materi bacaan pertama.
                                    @else
                                        Materi bacaan akan ditambahkan oleh guru pengajar.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Course Participants -->
                @if ($course->students->isNotEmpty())
                    <div class="card border-0 shadow-sm rounded-4 mb-4 hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-info bg-opacity-10 p-2 me-3">
                                        <i class="bi bi-people-fill text-info"></i>
                                    </div>
                                    <h5 class="card-title fw-bold mb-0">Peserta Course</h5>
                                </div>
                                <span class="badge bg-info bg-opacity-20 text-light rounded-pill px-3 py-2">
                                    {{ $course->students->count() }} Peserta
                                </span>
                            </div>

                            <div class="row g-3">
                                @foreach ($course->students as $student)
                                    <div class="col-md-6">
                                        <div
                                            class="card h-100 w-50 w-md-50 w-lg-25 border-0 bg-light bg-opacity-50 rounded-3 hover-card-subtle">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-1 fw-semibold">{{ $student->username }}</h6>
                                                        <small class="text-muted text-capitalize">
                                                            <i
                                                                class="bi bi-shield-fill-check me-1"></i>{{ $student->role }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Course Information -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 hover-card sticky-top" style="top: 100px;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-3">
                                <i class="bi bi-info-circle-fill text-warning"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-0">Informasi Course</h5>
                        </div>

                        <div class="info-list">
                            <div class="info-item d-flex justify-content-between align-items-center py-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-hash text-muted me-2"></i>
                                    <span class="text-muted">ID Course</span>
                                </div>
                                <span class="fw-semibold">#{{ $course->id }}</span>
                            </div>

                            <div class="info-item d-flex justify-content-between align-items-center py-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-badge text-muted me-2"></i>
                                    <span class="text-muted">Guru Pengajar</span>
                                </div>
                                <span class="fw-semibold">{{ $course->teacher ? $course->teacher->username : '-' }}</span>
                            </div>

                            <div class="info-item d-flex justify-content-between align-items-center py-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-journal-text text-muted me-2"></i>
                                    <span class="text-muted">Materi Bacaan</span>
                                </div>
                                <span
                                    class="badge bg-purple rounded-pill">{{ isset($course->reading_materials) ? $course->reading_materials->count() : 0 }}</span>
                            </div>

                            <div class="info-item d-flex justify-content-between align-items-center py-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-people text-muted me-2"></i>
                                    <span class="text-muted">Total Peserta</span>
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ $course->students->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Teacher Actions -->
                @if (auth()->check() && auth()->user()->role === 'teacher')
                    <div class="card border-0 shadow-sm rounded-4 mb-4 hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="rounded-circle bg-success bg-opacity-10 p-2 me-3">
                                    <i class="bi bi-gear-fill text-success"></i>
                                </div>
                                <h5 class="card-title fw-bold mb-0">Aksi Guru</h5>
                            </div>

                            <div class="d-grid gap-3">
                                <a href="{{ route('quizzes.create', $course->id) }}"
                                    class="btn btn-success btn-lg rounded-pill shadow-sm hover-lift">
                                    <i class="bi bi-plus-circle me-2"></i>Tambah Kuis Baru
                                </a>

                                <form action="{{ route('courses.destroy', $course->id) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus course ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="btn btn-outline-danger btn-lg rounded-pill w-100 hover-lift">
                                        <i class="bi bi-trash me-2"></i>Hapus Course
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Add Reading Material Modal -->
    @if (auth()->check() && auth()->user()->role === 'teacher')
        <div class="modal fade" id="addReadingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-journal-plus me-2 text-purple"></i>Tambah Materi Bacaan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('reading-materials.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="course_id" value="{{ $course->id }}">
                        <div class="modal-body p-4">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Judul Materi</label>
                                <input type="text" name="title" class="form-control form-control-lg rounded-3"
                                    placeholder="Masukkan judul materi bacaan" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Estimasi Waktu Baca (menit)</label>
                                <input type="number" name="reading_time" class="form-control rounded-3" placeholder="5"
                                    min="1" max="120" value="5">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Konten Materi</label>
                                <textarea name="content" class="form-control rounded-3" rows="10"
                                    placeholder="Tulis konten materi bacaan di sini..." required></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Lampiran (Opsional)</label>
                                <input type="file" name="attachments[]" class="form-control rounded-3" multiple
                                    accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png">
                                <small class="text-muted">
                                    Format yang didukung: PDF, DOC, DOCX, PPT, PPTX, JPG, PNG (Max: 10MB per file)
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">
                                Batal
                            </button>
                            <button type="submit" class="btn btn-purple rounded-pill hover-lift">
                                <i class="bi bi-plus-circle me-2"></i>Tambah Materi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Enhanced Styling -->
    <style>
        .hover-lift {
            transition: all 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .hover-card {
            transition: all 0.3s ease;
        }

        .hover-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
        }

        .hover-card-subtle {
            transition: all 0.2s ease;
        }

        .hover-card-subtle:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08) !important;
        }

        .hero-section {
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        .info-list .info-item:last-child {
            border-bottom: none !important;
        }

        .badge {
            font-size: 0.85em;
            font-weight: 500;
        }

        .card {
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
        }

        .btn {
            font-weight: 500;
            border-width: 2px;
        }

        .btn-outline-danger:hover {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        /* Purple theme for reading materials */
        .text-purple {
            color: #6f42c1 !important;
        }

        .bg-purple {
            background-color: #6f42c1 !important;
        }

        .btn-purple {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: white;
        }

        .btn-purple:hover {
            background-color: #5a359a;
            border-color: #5a359a;
            color: white;
        }

        .btn-outline-purple {
            border-color: #6f42c1;
            color: #6f42c1;
        }

        .btn-outline-purple:hover {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: white;
        }

        .bg-purple.bg-opacity-10 {
            background-color: rgba(111, 66, 193, 0.1) !important;
        }

        .bg-purple.bg-opacity-20 {
            background-color: rgba(111, 66, 193, 0.2) !important;
        }

        .badge.bg-purple {
            background-color: #6f42c1 !important;
        }

        /* Reading Material Card Styles */
        .reading-material-card {
            cursor: pointer;
            border: 2px solid transparent !important;
        }

        .reading-material-card:hover {
            border-color: #6f42c1 !important;
            transform: translateY(-5px);
        }

        .reading-material-card .stretched-link {
            z-index: 1;
        }

        .reading-material-card .dropdown {
            z-index: 10;
        }

        /* Text truncation utilities */
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

        /* Reading content styling */
        .reading-content {
            line-height: 1.8;
            font-size: 1.05rem;
            color: #333;
        }

        .accordion-button:not(.collapsed) {
            background-color: rgba(111, 66, 193, 0.1);
            border-color: rgba(111, 66, 193, 0.2);
        }

        .accordion-button:focus {
            box-shadow: 0 0 0 0.25rem rgba(111, 66, 193, 0.25);
        }

        @media (max-width: 768px) {
            .hero-section {
                min-height: 150px;
            }

            .hero-section .p-5 {
                padding: 2rem !important;
            }

            .display-5 {
                font-size: 1.8rem;
            }

            .sticky-top {
                position: relative !important;
                top: auto !important;
            }

            .col-lg-4 {
                margin-top: 0;
            }
        }
    </style>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
@endsection
