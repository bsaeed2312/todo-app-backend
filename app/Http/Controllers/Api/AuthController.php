<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::login($user);
        event(new Registered($user));
        return response()->json([
            'status' => 'success',
            'message' => 'Verification Email Has Been Sent, Please Verify To Continue.',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'Bearer',
            ],
        ]);
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);
        if (!$request->hasValidSignature() || !hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
            return redirect(env('FRONT_END_URL') . '/email/verify/not-valid-url');
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            return redirect(env('FRONT_END_URL') . '/email/verify/success');
        } else {
            return redirect(env('FRONT_END_URL') . '/email/verify/already');
        }
    }

    public function verificationEmailResend(User $user)
    {
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Already verified']);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification email sent']);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'Bearer',
            ],
        ]);

    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }
}
