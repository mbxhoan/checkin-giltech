<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsersRequest;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Request $request, User $user): View
    {
        return view('users.show', [
            'user' => $user,
            'posts_count' => $user->posts()->count(),
            'comments_count' => $user->comments()->count(),
            'likes_count' => $user->likes()->count(),
            'posts' => $user->posts()->withCount('likes', 'comments')->latest()->limit(5)->get(),
            'comments' => $user->comments()->with('post.author')->latest()->limit(5)->get()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(): View
    {
        $user = auth()->user();

        $this->authorize('update', $user);

        return view('users.edit', [
            'user' => $user,
            'roles' => Role::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UsersRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $this->authorize('update', $user);
        $user->update($request->validated());
        return redirect()->route('users.edit')->withSuccess(__('users.updated'));
    }

    public function verify(string $prefix, string $verify_token)
    {
        $user = User::where('username', $prefix)->first();

        if ($user && empty($user->email_verified_at)) {
            if ($verify_token == $user->verify_token) {
                $user->update([
                    'email_verified_at' => now(),
                    // 'verify_token'      => null,
                ]);

                if (!in_array($user->status, [
                    User::STATUS_ACTIVE
                ])) {
                    $user->update([
                        'status' => User::STATUS_ACTIVE
                    ]);
                }

                $user->company->update([
                    'status' => Company::STATUS_ACTIVE,
                ]);

                return view('auth.verified');
            }
        }

        return view('auth.not-found');
    }
}
