@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4" style="color: #1D3341">Detail Kuis: {{ $quiz->title }}</h2>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">{{ $quiz->title }}</h5>
                <p class="card-text">{{ $quiz->description }}</p>
            </div>
        </div>

        <h4>Pertanyaan dalam Kuis:</h4>
        @if ($quiz->questions->isEmpty())
            <p>Belum ada pertanyaan dalam kuis ini.</p>
        @else
            <ul class="list-group mb-3">
                @foreach ($quiz->questions as $question)
                    <li class="list-group-item">
                        <strong>Pertanyaan:</strong> {{ $question->text }}
                        @if ($question->options && $question->options->count())
                            <ul class="mt-2">
                                @foreach ($question->options as $option)
                                    <li>{{ $option->text }}
                                        @if ($option->is_correct)
                                            <span class="badge bg-success">Benar</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        <a href="{{ route('quizzes.index', $quiz->course_id) }}" class="btn btn-secondary">← Kembali ke Daftar Kuis</a>
    </div>
@endsection
