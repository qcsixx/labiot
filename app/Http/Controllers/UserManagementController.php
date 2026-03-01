<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'user')->get();
        return view('admin.pengelolaan-user', compact('users'));
    }

    public function destroy(User $user)
    {
        if ($user->role === 'admin') {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun admin');
        }
        
        $user->delete();
        return redirect()->back()->with('success', 'User berhasil dihapus');
    }

    public function suspend(User $user)
    {
        if ($user->role === 'admin') {
            return redirect()->back()->with('error', 'Tidak dapat menangguhkan akun admin');
        }
        
        $user->update(['status' => 'suspended']);
        return redirect()->back()->with('success', 'User berhasil ditangguhkan');
    }

    public function activate(User $user)
    {
        $user->update(['status' => 'active']);
        return redirect()->back()->with('success', 'User berhasil diaktifkan');
    }
} 