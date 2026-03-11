<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $sortBy = $request->input('sort_by', 'title');
        $sortOrder = $request->input('sort_order', 'asc');

        $query = Course::query();

        if ($sortBy === 'title') {
            $query->orderBy('title', $sortOrder);
            $courses = $query->with('students')->get();
        } elseif ($sortBy === 'last_accessed') {
            $courses = $query->with('students')->get();

            if ($user && $user->role !== 'teacher') {
                // Ambil data join user
                $joinedCourses = $user->courses()->withPivot('last_accessed_at')->get()->keyBy('id');

                $courses->transform(function ($course) use ($joinedCourses) {
                    if (isset($joinedCourses[$course->id])) {
                        $course->last_accessed_at = $joinedCourses[$course->id]->pivot->last_accessed_at;
                    } else {
                        $course->last_accessed_at = null;
                    }
                    return $course;
                });

                $courses = $courses->sortBy(function ($course) {
                    return $course->last_accessed_at ?? now()->subYears(10);
                });

                if ($sortOrder === 'desc') {
                    $courses = $courses->reverse();
                }
            } else {
                $courses = $courses->sortBy('title');
                if ($sortOrder === 'desc') {
                    $courses = $courses->reverse();
                }
            }
        } else {
            $courses = $query->get();
        }

        return view('courses.index', compact('courses'));
    }

    public function joinCourse(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'password' => $user->role !== 'teacher' ? 'required' : 'nullable',
        ]);

        $course = Course::findOrFail($request->course_id);

        if ($user->courses->contains($course->id)) {
            return back()->with('success', 'Anda sudah tergabung di course ini.');
        }

        if ($user->role !== 'teacher' && $course->join_password !== $request->password) {
            return back()->withErrors(['password' => 'Password salah']);
        }

        $user->courses()->attach($course->id);

        return redirect()->route('courses.show', $course->id)
            ->with('success', 'Berhasil bergabung ke course!');
    }

    public function showJoinForm($courseId)
    {
        $course = Course::findOrFail($courseId);
        return view('courses.join', compact('course'));
    }

    public function show($id)
    {
        $course = Course::with(['students', 'teacher'])->findOrFail($id);

        if (auth()->check() && auth()->user()->role !== 'teacher') {
            auth()->user()->courses()->updateExistingPivot($id, [
                'last_accessed_at' => now(),
            ]);
        }

        return view('courses.show', compact('course'));
    }

    public function create()
    {
        return view('courses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'join_password' => 'required|string|min:4',
        ]);

        $validated['teacher_id'] = auth()->id();

        Course::create($validated);

        return redirect()->route('courses.index')->with('success', 'Course berhasil dibuat!');
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);

        // Cek apakah user yang login adalah teacher dan pemilik course
        if (auth()->user()->role !== 'teacher' || $course->teacher_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Course berhasil dihapus.');
    }
}