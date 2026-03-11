<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\ReadingMaterial;
use App\Models\Quiz;
use App\Models\User;

class DashboardController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    public function dashboard()
    {
        $studentCount = User::where('role', 'student')->count();
        $readingmaterialsCount = ReadingMaterial::all()->count();
        $quizCount = Quiz::all()->count();
        $studentsByYear = User::where('role', 'student')
        ->selectRaw('YEAR(created_at) as year, COUNT(*) as count')
        ->groupBy('year')
        ->orderBy('year')
        ->get();

        return view('dashboard', compact('studentCount', 'readingmaterialsCount', 'quizCount', 'studentsByYear'));
    }
}