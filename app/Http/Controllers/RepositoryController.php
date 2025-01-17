<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class RepositoryController extends Controller
{
    public function index()
    {
        $dataTypes = array_filter(glob(public_path('repository/*')), 'is_dir');
        $data = [];
        foreach ($dataTypes as $typePath) {
            $type = basename($typePath);
            $studyPrograms = array_filter(glob("$typePath/*"), 'is_dir');
            $data[$type] = $studyPrograms;
        }
        return view('home.index', compact('data'));
    }

    public function file_manager($type, $program)
    {
        // Gather data for the navbar
        $dataTypes = array_filter(glob(public_path('repository/*')), 'is_dir');
        $data = [];
        foreach ($dataTypes as $typePath) {
            $dataType = basename($typePath);
            $studyPrograms = array_filter(glob("$typePath/*"), 'is_dir');
            $data[$dataType] = $studyPrograms;
        }

        // List files and folders for the selected study program
        $path = public_path("repository/$type/$program");
        $folders = array_filter(glob("$path/*"), 'is_dir');
        $files = array_filter(glob("$path/*"), 'is_file');

        $folders = array_map(fn($folder) => basename($folder), $folders);
        $files = array_map(fn($file) => [
            'name' => basename($file),
            'url' => asset("repository/$type/$program/" . basename($file)),
            'size' => File::size($file),
            'modified' => date('l, d F Y', File::lastModified($file)),
        ], $files);

        // Pass $data to the view
        return view('file_manager.index', compact('type', 'program', 'folders', 'files', 'data'));
    }
    
    public function upload_file(Request $request, $type, $program)
    {
        $request->validate(['file' => 'required|file']);
    
        $path = public_path("repository/$type/$program");
        $originalName = $request->file('file')->getClientOriginalName();
        $fileName = $originalName;
    
        // Ensure unique file name
        $counter = 1;
        while (file_exists("$path/$fileName")) {
            $fileName = pathinfo($originalName, PATHINFO_FILENAME) . " $counter." . $request->file('file')->getClientOriginalExtension();
            $counter++;
        }
    
        $request->file('file')->move($path, $fileName);
    
        return redirect()->back()->with('success', 'File uploaded successfully.');
    }
    
    public function add_folder(Request $request, $type, $program)
    {
        $request->validate(['folder_name' => 'required|string']);
    
        $path = public_path("repository/$type/$program");
        $folderName = $request->folder_name;
    
        // Ensure unique folder name
        $counter = 1;
        $uniqueFolderName = $folderName;
        while (file_exists("$path/$uniqueFolderName")) {
            $uniqueFolderName = "$folderName $counter";
            $counter++;
        }
    
        File::makeDirectory("$path/$uniqueFolderName", 0755, true);
    
        return redirect()->back()->with('success', 'Folder created successfully.');
    }
    
}