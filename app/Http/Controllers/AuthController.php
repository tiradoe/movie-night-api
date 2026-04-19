<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\PasswordResetRequest;
use App\Http\Requests\PasswordResetWithTokenRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        Password::sendResetLink(['email' => $user->email]);
        $this->processAcceptedInvitations($user);

        return response()->json($user, 201);
    }

    private function processAcceptedInvitations(User $user)
    {
        $invitations = Invitation::query()
            ->where('status', 'accepted_login_pending')
            ->where('email', $user->email)
            ->get();

        $viewerRole = Role::query()->where('name', 'VIEWER')->value('id');

        foreach ($invitations as $invitation) {
            $user->sharedLists()->attach(
                $invitation->movie_list_id,
                ['role_id' => $viewerRole]
            );
            $invitation->update(['status' => 'accepted']);
            $invitation->delete();
        }
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        Password::sendResetLink(['email' => $request->email]);

        return response()->json(['message' => 'Password reset link sent!']);
    }

    public function resetPassword(PasswordResetRequest $request)
    {
        $user = Auth::user();
        $validatedData = $request->validated();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        try {
            $user->forceFill(['password' => $validatedData['password']])->save();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Password reset failed.'], 400);
        }

        return response()->json(['message' => 'Password reset successful.']);
    }

    public function resetPasswordWithToken(PasswordResetWithTokenRequest $request)
    {
        $updatedUser = null;

        $status = Password::reset($request->validated(), function (User $user, string $password) use (&$updatedUser) {
            $user->forceFill(['password' => $password])->save();
            $updatedUser = $user;
        });

        if ($status === Password::PASSWORD_RESET && $updatedUser) {
            Auth::login($updatedUser);

            return response()->json(['message' => 'Password reset successfully.']);
        } elseif ($status === Password::INVALID_TOKEN) {
            return response()->json(['message' => 'Token expired'], 401);
        }

        return response()->json(['message' => 'Unable to reset password'], 400);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->validated())) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $request->session()->regenerate();
        $user = Auth::user();
        $this->processAcceptedInvitations($user);

        return response()->json($user);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        return response()->json(['message' => 'Logged out.']);
    }
}
