<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

    public function file_manager(Request $request, $type, $program)
    {
        // Gather data for the navbar
        $dataTypes = array_filter(glob(public_path('repository/*')), 'is_dir');
        $data = [];
        foreach ($dataTypes as $typePath) {
            $dataType = basename($typePath);
            $studyPrograms = array_filter(glob("$typePath/*"), 'is_dir');
            $data[$dataType] = array_map('basename', $studyPrograms); // Ensure only folder names
        }

        // Ensure correct values for $type and $program
        $type = basename($type);
        $program = basename($program);

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

        // Implement search functionality
        $searchQuery = $request->input('search');
        if ($searchQuery) {
            $folders = array_filter($folders, function ($folder) use ($searchQuery) {
                return strpos(strtolower($folder), strtolower($searchQuery)) !== false;
            });

            $files = array_filter($files, function ($file) use ($searchQuery) {
                return strpos(strtolower($file['name']), strtolower($searchQuery)) !== false;
            });
        }

        return view('file_manager.index', compact('type', 'program', 'folders', 'files', 'data'));
    }


    public function upload_file(Request $request, $type, $program)
    {
        $request->validate(['files' => 'required', 'files.*' => 'file']); // Validate each file

        $path = public_path("repository/$type/$program");

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

    public function delete_folder($type, $program, $folder)
    {
        $path = public_path("repository/$type/$program/$folder");
        if (File::exists($path)) {
            File::deleteDirectory($path);
            return redirect()->back()->with('success', 'Folder deleted successfully.');
        }
        return redirect()->back()->with('error', 'Folder not found.');
    }

    public function download_file(Request $request, $type, $program, $file)
    {
        // Get the password from the database
        $password = DB::table('password_file_downloads')->value('download_password');

        // Check if a password is provided in the request
        if (!Hash::check($request->password, $password)) {
            return redirect()->back()->with('error', 'Incorrect password. Please try again.');
        }

        // If the password is correct, proceed with the download
        $path = public_path("repository/$type/$program/$file");
        if (File::exists($path)) {
            return response()->download($path);
        }

        return redirect()->back()->with('error', 'File not found.');
    }

    public function delete_file($type, $program, $file)
    {
        $path = public_path("repository/$type/$program/$file");
        if (File::exists($path)) {
            File::delete($path);
            return redirect()->back()->with('success', 'File deleted successfully.');
        }
        return redirect()->back()->with('error', 'File not found.');
    }

    public function compress_folder($type, $program, $folder)
    {
        $folderPath = public_path("repository/$type/$program/$folder");

        // Check if the folder is empty
        $files = File::allFiles($folderPath);  // Get all files in the folder
        $subfolders = File::directories($folderPath);  // Get all subfolders

        // If no files and no subfolders are found, the folder is empty
        if (count($files) === 0 && count($subfolders) === 0) {
            return redirect()->back()->with('error', 'Cannot zip an empty folder.');
        }

        // If the folder has files or subfolders, proceed with zipping
        $zipFileName = "$folder.zip";
        $zipFilePath = public_path("repository/$type/$program/$zipFileName");

        // Check if the zip file already exists and append a number if necessary
        $counter = 1;
        while (File::exists($zipFilePath)) {
            $zipFileName = "$folder ($counter).zip";
            $zipFilePath = public_path("repository/$type/$program/$zipFileName");
            $counter++;
        }

        if (File::exists($folderPath)) {
            $zip = new \ZipArchive();
            // Try to open the zip file for writing
            $zipOpened = $zip->open($zipFilePath, \ZipArchive::CREATE);

            if ($zipOpened === true) {
                $this->addFolderToZip($folderPath, $zip);
                $zip->close();

                return redirect()->back()->with('success', "Folder '$folder' compressed to zip.");
            } else {
                // If opening the zip failed, show an error
                return redirect()->back()->with('error', 'Failed to create zip file.');
            }
        }

        return redirect()->back()->with('error', 'Folder not found.');
    }

    private function addFolderToZip($folderPath, $zip, $parentFolder = '')
    {
        $files = File::files($folderPath);

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $zip->addFile($filePath, $parentFolder . basename($filePath));
        }

        $subfolders = File::directories($folderPath);
        foreach ($subfolders as $folder) {
            $this->addFolderToZip($folder, $zip, $parentFolder . basename($folder) . '/');
        }
    }

    public function compress_file($type, $program, $file)
    {
        $filePath = public_path("repository/$type/$program/$file");
        $zipFileName = "$file.zip";
        $zipFilePath = public_path("repository/$type/$program/$zipFileName");

        // Check if the zip file already exists and append a number if necessary
        $counter = 1;
        while (File::exists($zipFilePath)) {
            $zipFileName = "$file ($counter).zip";
            $zipFilePath = public_path("repository/$type/$program/$zipFileName");
            $counter++;
        }

        if (File::exists($filePath)) {
            $zip = new \ZipArchive();
            if ($zip->open($zipFilePath, \ZipArchive::CREATE) === true) {
                $zip->addFile($filePath, $file);
                $zip->close();

                return redirect()->back()->with('success', "File '$file' compressed to zip.");
            }
        }
        return redirect()->back()->with('error', 'File not found.');
    }

    public function extract_zip($type, $program, $file)
    {
        $zipPath = public_path("repository/$type/$program/$file");
        $extractPath = public_path("repository/$type/$program/" . pathinfo($file, PATHINFO_FILENAME));

        if (!File::exists($zipPath)) {
            return redirect()->back()->with('error', 'ZIP file not found.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) === true) {
            // Ensure unique extraction folder name
            $counter = 1;
            $uniqueExtractPath = $extractPath;
            while (File::exists($uniqueExtractPath)) {
                $uniqueExtractPath = $extractPath . " ($counter)";
                $counter++;
            }

            $zip->extractTo($uniqueExtractPath);
            $zip->close();

            return redirect()->back()->with('success', 'ZIP file extracted successfully.');
        }

        return redirect()->back()->with('error', 'Failed to extract ZIP file.');
    }
}