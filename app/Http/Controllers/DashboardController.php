<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function dashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('show_login_form')->withErrors(['message' => 'You must be logged in to access the dashboard']);
        }
    
        $dataTypes = array_filter(glob(public_path('repository/*')), 'is_dir');
        $data = [];
        foreach ($dataTypes as $typePath) {
            $type = basename($typePath);
            $studyPrograms = array_filter(glob("$typePath/*"), 'is_dir');
            $data[$type] = $studyPrograms;
        }
    
        return view('dashboard.index', compact('data'));
    }
}
