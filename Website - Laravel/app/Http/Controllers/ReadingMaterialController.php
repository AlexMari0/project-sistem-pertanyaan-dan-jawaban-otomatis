<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\ReadingMaterial;
use App\Models\ReadingProgress;
use App\Models\Course;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReadingMaterialController extends Controller
{
    /**
     * Store a newly created reading material.
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'reading_time' => 'nullable|integer|min:1|max:120',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png'
        ]);

        // Check if user is authorized to add materials to this course
        $course = Course::findOrFail($request->course_id);
        if (Auth::user()->role !== 'teacher' || $course->teacher_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Handle file uploads
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('reading-materials', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType()
                ];
            }
        }

        ReadingMaterial::create([
            'course_id' => $request->course_id,
            'title' => $request->title,
            'content' => $request->content,
            'reading_time' => $request->reading_time ?? 5,
            'attachments' => !empty($attachments) ? json_encode($attachments) : null,
            'created_by' => Auth::id()
        ]);

        return redirect()->back()->with('success', 'Materi bacaan berhasil ditambahkan!');
    }

    /**
     * Remove the specified reading material.
     */
    public function destroy($id)
    {
        $material = ReadingMaterial::findOrFail($id);

        // Check authorization
        if (Auth::user()->role !== 'teacher' || $material->course->teacher_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Delete attachments from storage
        if ($material->attachments) {
            $attachments = json_decode($material->attachments, true);
            foreach ($attachments as $attachment) {
                Storage::disk('public')->delete($attachment['path']);
            }
        }

        $courseId = $material->course_id; // simpan sebelum delete
        $material->delete();

        return redirect()->route('courses.show', $courseId)
            ->with('success', 'Materi bacaan berhasil dihapus!');
    }

    /**
     * Mark reading material as read by student.
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            // Validasi user harus login dan role student
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus login terlebih dahulu.'
                ], 401);
            }

            if (Auth::user()->role !== 'student') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya siswa yang dapat menandai materi sebagai sudah dibaca.'
                ], 403);
            }

            // Cari reading material berdasarkan ID
            $readingMaterial = ReadingMaterial::find($id);

            if (!$readingMaterial) {
                return response()->json([
                    'success' => false,
                    'message' => 'Materi bacaan tidak ditemukan.'
                ], 404);
            }

            $userId = Auth::id();

            // Cek apakah sudah ada progress untuk user dan material ini
            $existingProgress = ReadingProgress::where('user_id', $userId)
                ->where('reading_material_id', $id)
                ->first();

            if ($existingProgress) {
                // Update existing progress
                $existingProgress->update([
                    'is_completed' => true,
                    'completed_at' => now()
                ]);

                Log::info("Reading progress updated for user {$userId} and material {$id}");
            } else {
                // Buat progress baru
                ReadingProgress::create([
                    'user_id' => $userId,
                    'reading_material_id' => $id,
                    'is_completed' => true,
                    'completed_at' => now()
                ]);

                Log::info("New reading progress created for user {$userId} and material {$id}");
            }

            return response()->json([
                'success' => true,
                'message' => 'Materi berhasil ditandai sebagai sudah dibaca!'
            ]);
        } catch (Exception $e) {
            Log::error('Error in markAsRead: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Show the specified reading material.
     */
    public function show(ReadingMaterial $material)
    {
        $previousMaterial = ReadingMaterial::where('course_id', $material->course_id)
            ->where('id', '<', $material->id)
            ->orderBy('id', 'desc')
            ->first();

        $nextMaterial = ReadingMaterial::where('course_id', $material->course_id)
            ->where('id', '>', $material->id)
            ->orderBy('id', 'asc')
            ->first();

        return view('reading-materials.show', compact('material', 'previousMaterial', 'nextMaterial'));
    }

    /**
     * Show the form for editing the specified reading material.
     */
    public function edit($id)
    {
        $material = ReadingMaterial::findOrFail($id);

        // Check authorization
        if (Auth::user()->role !== 'teacher' || $material->course->teacher_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        return view('reading-materials.edit', compact('material'));
    }

    /**
     * Update the specified reading material.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'reading_time' => 'nullable|integer|min:1|max:120',
            'new_attachments.*' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png|max:10240', // 10MB
            'keep_attachments' => 'nullable|array',
            'keep_attachments.*' => 'integer'
        ]);

        $material = ReadingMaterial::findOrFail($id);

        // Update basic fields
        $material->title = $request->title;
        $material->content = $request->content;
        $material->reading_time = $request->reading_time ?? 5;

        // Handle attachments
        $existingAttachments = $material->attachments ? json_decode($material->attachments, true) : [];
        $updatedAttachments = [];

        // Keep only selected existing attachments
        if ($request->has('keep_attachments')) {
            foreach ($request->keep_attachments as $keepIndex) {
                if (isset($existingAttachments[$keepIndex])) {
                    $updatedAttachments[] = $existingAttachments[$keepIndex];
                }
            }

            // Delete files that are not kept
            foreach ($existingAttachments as $index => $attachment) {
                if (!in_array($index, $request->keep_attachments)) {
                    // Delete file from storage
                    if (isset($attachment['path']) && Storage::disk('public')->exists($attachment['path'])) {
                        Storage::disk('public')->delete($attachment['path']);
                    }
                }
            }
        }

        // Handle new attachments
        if ($request->hasFile('new_attachments')) {
            foreach ($request->file('new_attachments') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('attachments', $fileName, 'public');

                $updatedAttachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $filePath,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ];
            }
        }

        // Update attachments in database
        $material->attachments = !empty($updatedAttachments) ? json_encode($updatedAttachments) : null;
        $material->save();

        return redirect()->route('reading-materials.show', $material->id)
            ->with('success', 'Materi berhasil diperbarui!');
    }

    // Optional: Method terpisah untuk menghapus lampiran via AJAX
    public function deleteAttachment(Request $request, $materialId, $attachmentIndex)
    {
        try {
            $material = ReadingMaterial::findOrFail($materialId);
            $attachments = $material->attachments ? json_decode($material->attachments, true) : [];

            if (!isset($attachments[$attachmentIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lampiran tidak ditemukan'
                ], 404);
            }

            // Delete file from storage
            $attachment = $attachments[$attachmentIndex];
            if (isset($attachment['path']) && Storage::disk('public')->exists($attachment['path'])) {
                Storage::disk('public')->delete($attachment['path']);
            }

            // Remove from array and reindex
            unset($attachments[$attachmentIndex]);
            $attachments = array_values($attachments); // Reindex array

            // Update database
            $material->attachments = !empty($attachments) ? json_encode($attachments) : null;
            $material->save();

            return response()->json([
                'success' => true,
                'message' => 'Lampiran berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Update course progress for user
     */
    private function updateCourseProgress($userId, $courseId)
    {
        try {
            // Hitung total materials dalam course
            $totalMaterials = ReadingMaterial::where('course_id', $courseId)->count();

            // Hitung materials yang sudah selesai dibaca
            $completedMaterials = ReadingProgress::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->where('is_completed', true)
                ->count();

            // Hitung persentase progress
            $progressPercentage = $totalMaterials > 0 ? round(($completedMaterials / $totalMaterials) * 100, 2) : 0;

            // Update atau create course progress
            CourseProgress::updateOrCreate(
                [
                    'user_id' => $userId,
                    'course_id' => $courseId
                ],
                [
                    'total_materials' => $totalMaterials,
                    'completed_materials' => $completedMaterials,
                    'progress_percentage' => $progressPercentage,
                    'last_accessed_at' => now()
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Error updating course progress: ' . $e->getMessage());
            // Tidak throw error karena ini tidak kritis
        }
    }
}
