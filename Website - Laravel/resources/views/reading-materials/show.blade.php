@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Hero Section with Material Info -->
        <div class="hero-section position-relative overflow-hidden rounded-4 mb-5"
            style="background: linear-gradient(135deg, #6f42c1 0%, #9d50bb 100%); min-height: 200px;">
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
                            <div
                                class="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-2 me-3 d-inline-flex align-items-center">
                                <i class="bi bi-journal-text me-2"></i>
                                Materi Bacaan
                            </div>
                            <span class="badge bg-info bg-opacity-20 text-white rounded-pill px-3 py-2">
                                <i class="bi bi-clock me-1"></i>{{ $material->reading_time ?? 5 }} menit baca
                            </span>
                        </div>
                        <h1 class="display-5 fw-bold mb-3 text-white">{{ $material->title }}</h1>
                        <p class="lead mb-0 text-white-50">
                            <i class="bi bi-book me-2"></i>
                            Dari course: {{ $material->course->title }}
                        </p>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('courses.show', $material->course->id) }}"
                            class="btn btn-light btn-lg rounded-pill shadow-sm hover-lift">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Course
                        </a>
                        @if (auth()->check() && auth()->user()->role === 'teacher')
                            <button class="btn btn-warning btn-lg rounded-pill shadow-sm hover-lift" data-bs-toggle="modal"
                                data-bs-target="#editMaterialModal">
                                <i class="bi bi-pencil me-2"></i>Edit Materi
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Reading Progress Banner (for students) -->
        @if (auth()->check() && auth()->user()->role === 'student')
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-gradient-primary">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-white bg-opacity-25 p-3 me-3"
                                    style="width: 50px; height: 50px;">
                                    <i class="bi bi-clock-history text-white fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="text-white fw-bold mb-1">Mulai Membaca</h5>
                                    <p class="text-white-50 mb-0">Estimasi waktu: {{ $material->reading_time ?? 5 }} menit
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            @php
                                $isRead = false;
                                if (auth()->user()->readingProgress) {
                                    $isRead = \App\Models\ReadingProgress::where('user_id', auth()->id())
                                        ->where('reading_material_id', $material->id)
                                        ->where('is_completed', true)
                                        ->exists();
                                }
                            @endphp

                            @if ($isRead)
                                <button class="btn btn-outline-success btn-lg rounded-pill shadow-sm" disabled>
                                    <i class="bi bi-check-circle-fill me-2"></i>Sudah Selesai Dibaca
                                </button>
                            @else
                                <button class="btn btn-white btn-lg rounded-pill shadow-sm hover-lift mark-as-read"
                                    data-material-id="{{ $material->id }}">
                                    <i class="bi bi-check-circle me-2"></i>Tandai Selesai Dibaca
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Material Content -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 hover-card">
                    <div class="card-body p-5">
                        <div class="reading-content-detail">
                            {!! nl2br(e($material->content)) !!}
                        </div>
                    </div>
                </div>

                <!-- Attachments Section -->
                @if ($material->attachments)
                    <div class="card border-0 shadow-sm rounded-4 mb-4 hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-3">
                                    <i class="bi bi-paperclip text-warning"></i>
                                </div>
                                <h5 class="card-title fw-bold mb-0">Lampiran</h5>
                            </div>

                            <div class="row g-3">
                                @foreach (json_decode($material->attachments) as $attachment)
                                    <div class="col-md-6">
                                        <div class="card border-0 bg-light bg-opacity-50 rounded-3 hover-card-subtle">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                                                        <i class="bi bi-file-earmark text-primary"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 fw-semibold text-break">{{ $attachment->name }}</h6>
                                                        <small class="text-muted">
                                                            {{ strtoupper(pathinfo($attachment->name, PATHINFO_EXTENSION)) }}
                                                        </small>
                                                    </div>
                                                    <a href="{{ asset('storage/' . $attachment->path) }}"
                                                        class="btn btn-outline-primary btn-sm rounded-pill" target="_blank">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Navigation -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            @if ($previousMaterial)
                                <a href="{{ route('reading-materials.show', $previousMaterial->id) }}"
                                    class="btn btn-outline-purple rounded-pill hover-lift">
                                    <i class="bi bi-chevron-left me-2"></i>Materi Sebelumnya
                                </a>
                            @else
                                <div></div>
                            @endif

                            @if ($nextMaterial)
                                <a href="{{ route('reading-materials.show', $nextMaterial->id) }}"
                                    class="btn btn-purple rounded-pill hover-lift">
                                    Materi Selanjutnya<i class="bi bi-chevron-right ms-2"></i>
                                </a>
                            @else
                                <a href="{{ route('courses.show', $material->course->id) }}"
                                    class="btn btn-success rounded-pill hover-lift">
                                    Kembali ke Course<i class="bi bi-arrow-right ms-2"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Material Information -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 hover-card sticky-top" style="top: 100px;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="rounded-circle bg-info bg-opacity-10 p-2 me-3">
                                <i class="bi bi-info-circle-fill text-info"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-0">Informasi Materi</h5>
                        </div>

                        <div class="info-list">
                            <div class="info-item d-flex justify-content-between align-items-center py-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-hash text-muted me-2"></i>
                                    <span class="text-muted">ID Materi</span>
                                </div>
                                <span class="fw-semibold">#{{ $material->id }}</span>
                            </div>

                            <div class="info-item d-flex justify-content-between align-items-center py-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-clock text-muted me-2"></i>
                                    <span class="text-muted">Estimasi Baca</span>
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ $material->reading_time ?? 5 }}
                                    menit</span>
                            </div>

                            <div class="info-item d-flex justify-content-between align-items-center py-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-book text-muted me-2"></i>
                                    <span class="text-muted">Course</span>
                                </div>
                                <span class="fw-semibold">{{ $material->course->title }}</span>
                            </div>

                            <div class="info-item d-flex justify-content-between align-items-center py-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-paperclip text-muted me-2"></i>
                                    <span class="text-muted">Lampiran</span>
                                </div>
                                <span class="badge bg-warning rounded-pill">
                                    {{ $material->attachments ? count(json_decode($material->attachments)) : 0 }}
                                </span>
                            </div>

                            <div class="info-item d-flex justify-content-between align-items-center py-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar text-muted me-2"></i>
                                    <span class="text-muted">Dibuat</span>
                                </div>
                                <span class="fw-semibold">{{ $material->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Materials List -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 hover-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="rounded-circle bg-purple bg-opacity-10 p-2 me-3">
                                <i class="bi bi-list-ul text-purple"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-0">Materi Lainnya</h5>
                        </div>

                        <div class="materials-list">
                            @foreach ($material->course->reading_materials as $otherMaterial)
                                @php
                                    $isCurrent = $otherMaterial->id == $material->id;
                                @endphp
                                <div class="material-item mb-3">
                                    <a href="{{ route('reading-materials.show', $otherMaterial->id) }}"
                                        class="card border-0 {{ $isCurrent ? 'bg-purple text-white' : 'bg-light' }} rounded-3 text-decoration-none hover-card-subtle">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="rounded-circle {{ $isCurrent ? 'bg-white bg-opacity-25' : 'bg-purple bg-opacity-10' }} p-2 me-3">
                                                    <i
                                                        class="bi bi-journal-text {{ $isCurrent ? 'text-white' : 'text-purple' }}"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-semibold {{ $isCurrent ? 'text-white' : '' }}">
                                                        {{ Str::limit($otherMaterial->title, 30) }}
                                                    </h6>
                                                    <small class="{{ $isCurrent ? 'text-white-50' : 'text-muted' }}">
                                                        <i class="bi bi-clock me-1"></i>
                                                        {{ $otherMaterial->reading_time ?? 5 }} menit
                                                    </small>
                                                </div>
                                                @if ($isCurrent)
                                                    <div class="badge bg-white text-purple rounded-pill">
                                                        Saat ini
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
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
                                <button class="btn btn-warning btn-lg rounded-pill shadow-sm hover-lift"
                                    data-bs-toggle="modal" data-bs-target="#editMaterialModal">
                                    <i class="bi bi-pencil me-2"></i>Edit Materi
                                </button>

                                <form action="{{ route('reading-materials.destroy', $material->id) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="btn btn-outline-danger btn-lg rounded-pill w-100 hover-lift">
                                        <i class="bi bi-trash me-2"></i>Hapus Materi
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Edit Material Modal -->
    @if (auth()->check() && auth()->user()->role === 'teacher')
        <div class="modal fade" id="editMaterialModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-pencil me-2 text-warning"></i>Edit Materi Bacaan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('reading-materials.update', $material->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body p-4">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Judul Materi</label>
                                <input type="text" name="title" class="form-control form-control-lg rounded-3"
                                    value="{{ $material->title }}" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Estimasi Waktu Baca (menit)</label>
                                <input type="number" name="reading_time" class="form-control rounded-3"
                                    value="{{ $material->reading_time ?? 5 }}" min="1" max="120">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Konten Materi</label>
                                <textarea name="content" class="form-control rounded-3" rows="10" required>{{ $material->content }}</textarea>
                            </div>

                            <!-- Existing Attachments Section -->
                            @if ($material->attachments)
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Lampiran Saat Ini</label>
                                    <div class="border rounded-3 p-3 bg-light bg-opacity-50">
                                        @foreach (json_decode($material->attachments) as $index => $attachment)
                                            <div class="existing-attachment-item d-flex align-items-center justify-content-between p-2 mb-2 bg-white rounded-2 border"
                                                id="attachment-{{ $index }}">
                                                <div class="d-flex align-items-center flex-grow-1">
                                                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                                                        <i class="bi bi-file-earmark text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 fw-semibold">{{ $attachment->name }}</h6>
                                                        <small class="text-muted">
                                                            {{ strtoupper(pathinfo($attachment->name, PATHINFO_EXTENSION)) }}
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ asset('storage/' . $attachment->path) }}"
                                                        class="btn btn-outline-info btn-sm rounded-pill" target="_blank"
                                                        title="Lihat file">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button"
                                                        class="btn btn-outline-danger btn-sm rounded-pill delete-attachment-btn"
                                                        data-attachment-index="{{ $index }}"
                                                        title="Hapus lampiran">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <!-- Hidden input untuk menandai attachment yang akan dihapus -->
                                            <input type="hidden" name="keep_attachments[]" value="{{ $index }}"
                                                id="keep-attachment-{{ $index }}">
                                        @endforeach
                                    </div>
                                    <small class="text-muted">
                                        Klik tombol <i class="bi bi-trash text-danger"></i> untuk menghapus lampiran
                                    </small>
                                </div>
                            @endif

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Tambah Lampiran Baru (Opsional)</label>
                                <input type="file" name="new_attachments[]" class="form-control rounded-3" multiple
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
                            <button type="submit" class="btn btn-warning rounded-pill hover-lift">
                                <i class="bi bi-check-circle me-2"></i>Update Materi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- JavaScript for handling attachment deletion -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Handle delete attachment buttons
                document.querySelectorAll('.delete-attachment-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const attachmentIndex = this.dataset.attachmentIndex;
                        const attachmentItem = document.getElementById(`attachment-${attachmentIndex}`);
                        const keepInput = document.getElementById(`keep-attachment-${attachmentIndex}`);

                        // Confirm deletion
                        if (confirm('Apakah Anda yakin ingin menghapus lampiran ini?')) {
                            // Hide the attachment item with animation
                            attachmentItem.style.transition = 'all 0.3s ease';
                            attachmentItem.style.opacity = '0';
                            attachmentItem.style.transform = 'scale(0.9)';

                            setTimeout(() => {
                                attachmentItem.style.display = 'none';
                                // Remove the keep input so this attachment will be deleted
                                if (keepInput) {
                                    keepInput.remove();
                                }
                            }, 300);
                        }
                    });
                });
            });
        </script>

        <style>
            .existing-attachment-item {
                transition: all 0.3s ease;
            }

            .existing-attachment-item:hover {
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            .delete-attachment-btn:hover {
                transform: scale(1.1);
            }
        </style>
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

        .reading-content-detail {
            line-height: 1.9;
            font-size: 1.1rem;
            color: #333;
            text-align: justify;
        }

        .reading-content-detail p {
            margin-bottom: 1.5rem;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Purple theme */
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

        .btn-white {
            background-color: white;
            border-color: white;
            color: #6f42c1;
        }

        .btn-white:hover {
            background-color: #f8f9fa;
            border-color: #f8f9fa;
            color: #6f42c1;
        }

        .materials-list .material-item:last-child {
            margin-bottom: 0 !important;
        }

        /* Custom styling untuk tombol sudah selesai dibaca */
        .btn-success-completed {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: 2px solid #28a745;
            color: white;
            font-weight: 600;
            cursor: not-allowed;
            opacity: 1 !important;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
            transition: all 0.3s ease;
        }

        .btn-success-completed:hover {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-color: #28a745;
            color: white;
            transform: none;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }

        .btn-success-completed:disabled {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-color: #28a745;
            color: white;
            opacity: 1;
        }

        .btn-outline-success[disabled] {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: 2px solid #28a745;
            color: white !important;
            font-weight: 600;
            opacity: 1 !important;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
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

            .reading-content-detail {
                font-size: 1rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Pastikan CSRF token tersedia
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                console.error('CSRF token tidak ditemukan. Pastikan meta tag csrf-token ada di <head>');
                return;
            }

            // Handle mark as read functionality
            const markAsReadButton = document.querySelector('.mark-as-read');
            if (markAsReadButton) {
                markAsReadButton.addEventListener('click', function() {
                    const materialId = this.dataset.materialId;
                    const button = this;

                    // Disable button sementara untuk mencegah double click
                    button.disabled = true;
                    button.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Memproses...';

                    // Debug log
                    console.log('Mengirim request untuk material ID:', materialId);

                    fetch(`/reading-materials/${materialId}/mark-read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken.content,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                material_id: materialId
                            })
                        })
                        .then(response => {
                            console.log('Response status:', response.status);

                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }

                            return response.json();
                        })
                        .then(data => {
                            console.log('Response data:', data);

                            if (data.success) {
                                button.innerHTML =
                                    '<i class="bi bi-check-circle-fill me-2"></i>Sudah Selesai Dibaca';

                                button.classList.remove('btn-white', 'btn-outline-success',
                                    'hover-lift');

                                button.classList.add('btn-success-completed');
                                button.disabled = true;

                                // Show success notification
                                showSuccessNotification(
                                    'Materi berhasil ditandai sebagai sudah dibaca!');

                            } else {
                                throw new Error(data.message ||
                                    'Terjadi kesalahan yang tidak diketahui');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);

                            // Reset button state
                            button.disabled = false;
                            button.innerHTML =
                                '<i class="bi bi-check-circle me-2"></i>Tandai Selesai Dibaca';

                            // Show error notification
                            showErrorNotification(error.message ||
                                'Terjadi kesalahan saat menandai materi sebagai sudah dibaca.');
                        });
                });
            }

            // Handle delete attachment buttons with AJAX
            document.querySelectorAll('.delete-attachment-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const attachmentIndex = this.dataset.attachmentIndex;
                    const materialId = {{ $material->id }}; // Pastikan ini dari Blade template
                    const attachmentItem = document.getElementById(`attachment-${attachmentIndex}`);

                    // Confirm deletion
                    if (confirm(
                            'Apakah Anda yakin ingin menghapus lampiran ini? Perubahan akan langsung disimpan.'
                        )) {
                        // Disable button
                        this.disabled = true;
                        this.innerHTML = '<i class="bi bi-hourglass-split"></i>';

                        // Send AJAX request
                        fetch(`/reading-materials/${materialId}/attachments/${attachmentIndex}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken.content,
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Animate removal
                                    attachmentItem.style.transition = 'all 0.3s ease';
                                    attachmentItem.style.opacity = '0';
                                    attachmentItem.style.transform = 'scale(0.9)';

                                    setTimeout(() => {
                                        attachmentItem.remove();
                                        showSuccessNotification(
                                            'Lampiran berhasil dihapus!');
                                    }, 300);

                                } else {
                                    throw new Error(data.message || 'Gagal menghapus lampiran');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);

                                // Re-enable button
                                this.disabled = false;
                                this.innerHTML = '<i class="bi bi-trash"></i>';

                                showErrorNotification(error.message ||
                                    'Terjadi kesalahan saat menghapus lampiran');
                            });
                    }
                });
            });

            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const closeButton = alert.querySelector('.btn-close');
                    if (closeButton) {
                        closeButton.click();
                    }
                });
            }, 5000);
        });

        // Function untuk menampilkan notifikasi sukses
        function showSuccessNotification(message) {
            const alertHtml = `
            <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4 alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        ${message}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        `;

            insertNotification(alertHtml);
        }

        // Function untuk menampilkan notifikasi error
        function showErrorNotification(message) {
            const alertHtml = `
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4 alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-2">
                            <i class="bi bi-exclamation-circle-fill text-danger"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        ${message}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        `;

            insertNotification(alertHtml);
        }

        // Function untuk insert notifikasi ke halaman
        function insertNotification(alertHtml) {
            // Cek apakah ada modal (untuk attachment deletion)
            const modal = document.querySelector('#editMaterialModal .modal-body');
            if (modal) {
                modal.insertAdjacentHTML('afterbegin', alertHtml);
                return;
            }

            // Jika tidak ada modal, gunakan container utama (untuk mark as read)
            const container = document.querySelector('.container-fluid');
            if (container) {
                const heroSection = container.querySelector('.hero-section');
                if (heroSection) {
                    heroSection.insertAdjacentHTML('afterend', alertHtml);
                } else {
                    container.insertAdjacentHTML('afterbegin', alertHtml);
                }
            }
        }
    </script>
@endsection
