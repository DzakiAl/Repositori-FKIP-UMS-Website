<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PasswordFileDownload;

class UserController extends Controller
{
    public function updateUsername(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,name,' . auth()->id(),
        ]);

        $user = Auth::user();
        $user->name = $request->username;
        $user->save();

        return redirect()->back()->with('success', 'Username berhasil diperbarui!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        // Check if old password is correct
        if (!Hash::check($request->old_password, $user->password)) {
            return redirect()->back()->with('error', 'Password lama salah!');
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Password berhasil diperbarui!');
    }

    public function updateDownloadPassword(Request $request)
    {
        $request->validate([
            'old_password_file_folder' => 'required',
            'new_password_file_folder' => 'required|min:6',
            'confirm_password_file_folder' => 'required|same:new_password_file_folder',
        ]);

        // Fetch the first password record
        $passwordRecord = PasswordFileDownload::first();

        if (!$passwordRecord) {
            return back()->withErrors(['old_password_file_folder' => 'Tidak ada password yang tersimpan sebelumnya.']);
        }

        // Check if old password is correct
        if (!Hash::check($request->old_password_file_folder, $passwordRecord->download_password)) {
            return back()->withErrors(['old_password_file_folder' => 'Password lama salah']);
        }

        // Update the password
        $passwordRecord->update([
            'download_password' => Hash::make($request->new_password_file_folder),
        ]);

        return back()->with('success', 'Password file dan folder berhasil diperbarui.');
    }
}