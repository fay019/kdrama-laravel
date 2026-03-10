<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::paginate(15);
        return view('admin.users.index', ['users' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', ['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'is_admin' => 'boolean',
        ]);

        $user->update($validated);
        return redirect()->route('admin.users.index')->with('success', __('admin.users.user_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', __('admin.users.user_deleted'));
    }

    /**
     * Reset user password and send via email
     */
    public function resetPassword(string $id)
    {
        $user = User::findOrFail($id);

        // Generate random password (12 chars: mixed case + numbers + symbols)
        $newPassword = Str::password(length: 12, symbols: true);

        // Update user password and mark as must change
        $user->update([
            'password' => Hash::make($newPassword),
            'password_must_change' => true,
        ]);

        // Send email with new password
        Mail::to($user->email)->send(new PasswordResetMail($user, $newPassword));

        return redirect()->route('admin.users.edit', $user->id)
            ->with('success', __('admin.users.user_password_reset', ['email' => $user->email]));
    }
}
