<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Support\Facades\Log;

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

    public function file_manager(Request $request, $type, $program, $subfolder = null)
    {
        // Gather data for the navbar
        $dataTypes = array_filter(glob(public_path('repository/*')), 'is_dir');
        $data = [];
        foreach ($dataTypes as $typePath) {
            $dataType = basename($typePath);
            $studyPrograms = array_filter(glob("$typePath/*"), 'is_dir');
            $data[$dataType] = array_map('basename', $studyPrograms);
        }

        // Ensure correct values for $type and $program
        $type = basename($type);
        $program = basename($program);

        // Build folder path dynamically
        $path = public_path("repository/$type/$program" . ($subfolder ? "/$subfolder" : ''));

        // Get folders & files
        $folders = array_filter(glob("$path/*"), 'is_dir');
        $files = array_filter(glob("$path/*"), 'is_file');

        // Format data
        $folders = array_map(fn($folder) => basename($folder), $folders);
        $files = array_map(fn($file) => [
            'name' => basename($file),
            'url' => asset("repository/$type/$program" . ($subfolder ? "/$subfolder" : '') . "/" . basename($file)), // Corrected URL generation
            'size' => File::size($file),
            'modified' => date('l, d F Y', File::lastModified($file)),
        ], $files);

        // Implement search
        $searchQuery = $request->input('search');
        if ($searchQuery) {
            $folders = array_filter($folders, fn($folder) => strpos(strtolower($folder), strtolower($searchQuery)) !== false);
            $files = array_filter($files, fn($file) => strpos(strtolower($file['name']), strtolower($searchQuery)) !== false);
        }

        // Breadcrumb navigation
        $breadcrumbs = explode('/', trim($subfolder, '/'));

        return view('file_manager.index', compact('type', 'program', 'folders', 'files', 'data', 'subfolder', 'breadcrumbs'));
    }

    public function upload_file(Request $request, $type, $program, $subfolder = null)
    {
        $request->validate(['files' => 'required', 'files.*' => 'file']);

        // Build the correct path dynamically, including subfolder if provided
        $path = public_path("repository/$type/$program" . ($subfolder ? "/$subfolder" : ''));

        // Ensure the directory exists
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $fileName = $originalName;

            // Ensure unique file name
            $counter = 1;
            while (file_exists("$path/$fileName")) {
                $fileName = pathinfo($originalName, PATHINFO_FILENAME) . " $counter." . $file->getClientOriginalExtension();
                $counter++;
            }

            $file->move($path, $fileName);
        }

        return redirect()->back()->with('success', 'Files uploaded successfully.');
    }

    public function add_folder(Request $request, $type, $program, $subfolder = null)
    {
        $request->validate(['folder_name' => 'required|string']);

        // Build the correct path dynamically, including subfolder if provided
        $path = public_path("repository/$type/$program" . ($subfolder ? "/$subfolder" : ''));

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

    public function delete_folder($type, $program, $folder, $subfolder = null)
    {
        $path = public_path("repository/$type/$program/$folder" . ($subfolder ? "/$subfolder" : ''));

        if (File::exists($path)) {
            File::deleteDirectory($path);
            return redirect()->back()->with('success', 'Folder deleted successfully.');
        }
        return redirect()->back()->with('error', 'Folder not found.');
    }

    public function download_file(Request $request, $type, $program, $file, $subfolder = null)
    {
        // Get the password from the database
        $password = DB::table('password_file_downloads')->value('download_password');

        // Check if a password is provided in the request
        if (!Hash::check($request->password, $password)) {
            return redirect()->back()->with('error', 'Incorrect password. Please try again.');
        }

        // If the password is correct, proceed with the download
        $path = public_path("repository/$type/$program/$file" . ($subfolder ? "/$subfolder" : ''));
        if (File::exists($path)) {
            return response()->download($path);
        }

        return redirect()->back()->with('error', 'File not found.');
    }

    public function delete_file($type, $program, $file, $subfolder = null)
    {
        $path = public_path("repository/$type/$program/$file" . ($subfolder ? "/$subfolder" : ''));

        if (File::exists($path)) {
            File::delete($path);
            return redirect()->back()->with('success', 'File deleted successfully.');
        }
        return redirect()->back()->with('error', 'File not found.');
    }

    public function open_file($type, $program, $file, $subfolder = null)
    {
        $path = public_path("repository/$type/$program/$file" . ($subfolder ? "/$subfolder" : ''));

        if (File::exists($path)) {
            // Get the file's mime type to determine how to display it
            $mimeType = mime_content_type($path);

            // For PDF files
            if ($mimeType == 'application/pdf') {
                return response()->file($path);
            }

            // For Word files (doc, docx)
            if ($mimeType == 'application/msword' || $mimeType == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                return $this->openWithGoogleDocsViewer($path);
            }

            // For Excel files (xls, xlsx)
            if ($mimeType == 'application/vnd.ms-excel' || $mimeType == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                return $this->openWithGoogleDocsViewer($path);
            }

            // For PowerPoint files (ppt, pptx)
            if ($mimeType == 'application/vnd.ms-powerpoint' || $mimeType == 'application/vnd.openxmlformats-officedocument.presentationml.presentation') {
                return $this->openWithGoogleDocsViewer($path);
            }

            // For image files (jpeg, png, gif, etc.)
            if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                return response()->file($path);
            }

            // Default case for other file types
            return response()->file($path);
        }

        return redirect()->back()->with('error', 'File not found.');
    }

    private function openWithGoogleDocsViewer($filePath)
    {
        // URL for Google Docs Viewer
        $url = 'https://docs.google.com/viewer?url=' . urlencode(asset('repository/' . $filePath));
        return redirect()->away($url);
    }

    public function download_folder(Request $request, $type, $program, $subfolder = null)
    {
        // Get the password from the database
        $password = DB::table('password_file_downloads')->value('download_password');

        // Validate the password
        if (!Hash::check($request->password, $password)) {
            return redirect()->back()->with('error', 'Incorrect password. Please try again.');
        }

        // Define folder path
        $folderPath = public_path("repository/$type/$program" . ($subfolder ? "/$subfolder" : ''));

        if (!File::exists($folderPath)) {
            return redirect()->back()->with('error', 'Folder not found.');
        }

        // DEBUG: Log all files before adding them to ZIP
        Log::info("Starting ZIP creation for folder: $folderPath");

        // Create a temporary ZIP file
        $zipFileName = basename($folderPath) . '.zip';
        $zipFilePath = storage_path("app/$zipFileName");

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            return redirect()->back()->with('error', 'Could not create ZIP file.');
        }

        // Create recursive directory iterator
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $fileCount = 0;
        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($folderPath) + 1);

            // DEBUG: Log each file that is being added
            Log::info("Adding to ZIP: $filePath as $relativePath");

            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($filePath, $relativePath);
                $fileCount++;
            }
        }

        $zip->close();

        // DEBUG: Log ZIP completion
        Log::info("ZIP file created successfully with $fileCount files: $zipFilePath");

        // Download the ZIP file
        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

    public function rename(Request $request, $type, $program, $subfolder = null)
    {
        $request->validate([
            'old_name' => 'required|string',
            'new_name' => 'required|string'
        ]);

        $basePath = public_path("repository/$type/$program" . ($subfolder ? "/$subfolder" : ''));
        $oldPath = "$basePath/{$request->old_name}";
        $newPath = "$basePath/{$request->new_name}";

        if (!File::exists($oldPath)) {
            return redirect()->back()->with('error', 'File or folder not found.');
        }

        if (File::exists($newPath)) {
            return redirect()->back()->with('error', 'A file or folder with this name already exists.');
        }

        File::move($oldPath, $newPath);

        return redirect()->back()->with('success', 'Renamed successfully.');
    }

    public function upload_folder(Request $request, $type, $program, $subfolder = null)
    {
        $basePath = public_path("repository/$type/$program" . ($subfolder ? "/$subfolder" : ''));

        if (!File::exists($basePath)) {
            File::makeDirectory($basePath, 0755, true);
        }

        if ($request->has('paths')) {
            // Get the top-level folder name from the first uploaded file
            $firstPath = explode('/', $request->paths[0])[0];
            $folderPath = $basePath . '/' . $firstPath;

            // Check if the folder already exists
            if (File::exists($folderPath)) {
                return response()->json(['error' => "Folder '$firstPath' already exists!"], 400);
            }

            foreach ($request->paths as $index => $relativePath) {
                $file = $request->file('files')[$index];

                // Extract folder structure
                $filePath = $basePath . '/' . $relativePath;
                $fileDir = dirname($filePath);

                // Create directory if it doesn't exist
                if (!File::exists($fileDir)) {
                    File::makeDirectory($fileDir, 0755, true, true);
                }

                // Save the file
                if ($file) {
                    $file->move($fileDir, basename($filePath));
                }
            }
        }

        return response()->json(['message' => 'Folder uploaded successfully.']);
    }

}