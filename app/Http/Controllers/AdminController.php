<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
            'posts' => \App\Models\Post::count(),
            'categories' => \App\Models\Category::count(),
            'comments' => \App\Models\Comment::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function user()
    {
        $users = User::latest()->get();

        return view('admin.user', compact('users'));
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:super_admin,admin,editor,moderator,user'],
            'is_active' => ['required', 'boolean'],
        ]);

        $currentUser = auth()->user();

        if (! $currentUser->canManage($user)) {
            return redirect()->route('user')->with('error', "Siz bu foydalanuvchining rolini o'zgartira olmaysiz.");
        }

        if ($user->id === $currentUser->id) {
            return redirect()->route('user')->with('error', "O'zingizning rolni o'zgartira olmaysiz.");
        }

        $user->update($validated);

        return redirect()->route('user')
            ->with('success', 'Foydalanuvchi yangilandi.')
            ->with('toast_type', 'warning');
    }

    public function destroyUser(User $user)
    {
        $currentUser = auth()->user();

        if ($user->id === $currentUser->id) {
            return redirect()->route('user')->with('error', "O'zingizni o'chira olmaysiz.");
        }

        if (! $currentUser->canManage($user)) {
            return redirect()->route('user')->with('error', "Siz bu foydalanuvchini o'chira olmaysiz.");
        }

        $user->delete();

        return redirect()->route('user')
            ->with('error', "Foydalanuvchi o'chirildi.")
            ->with('toast_type', 'error');
    }

    public function notification()
    {
        return view('admin.notification');
    }
}
