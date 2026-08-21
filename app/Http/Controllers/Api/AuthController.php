<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * احراز هویت — هم‌ارز AuthController نسخه NestJS + MongoDB.
 *
 * - register → 201 { token, user: { id, email, name } }
 * - login    → 200 { token, user: { id, email, name } }
 * - me       → 200 { id, email, name }
 */
class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
            'name' => ['nullable', 'string'],
        ], [
            'email.email' => 'ایمیل معتبر وارد کنید',
            'password.min' => 'رمز عبور باید حداقل ۶ کاراکتر باشد',
        ]);

        if (User::where('email', $data['email'])->exists()) {
            return response()->json(['message' => 'این ایمیل قبلاً ثبت شده است'], 409);
        }

        $user = User::create([
            'email' => $data['email'],
            'name' => $data['name'] ?? $this->prettyName($data['email']),
            'password' => $data['password'],
        ]);

        return response()->json($this->issue($user), 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'email.email' => 'ایمیل معتبر وارد کنید',
            'password.min' => 'رمز عبور باید حداقل ۶ کاراکتر باشد',
        ]);

        $user = User::where('email', $data['email'])->first();

        // همان پیام برای کاربر ناموجود و رمز اشتباه (جلوگیری از شناسایی ایمیل)
        if (! $user || ! password_verify($data['password'], $user->password)) {
            return response()->json(['message' => 'ایمیل یا رمز عبور اشتباه است'], 401);
        }

        return response()->json($this->issue($user));
    }

    public function me(Request $request): array
    {
        return $request->user()->only(['id', 'email', 'name']);
    }

    /**
     * صدور توکن + پروفایل عمومی — منقضی بعد از ۷ روز (مثل JWT نسخه NestJS).
     */
    private function issue(User $user): array
    {
        return [
            'token' => $user->createToken('api', ['*'], now()->addDays(7))->plainTextToken,
            'user' => $user->only(['id', 'email', 'name']),
        ];
    }

    /** ساخت نام خوانا از پیشوند ایمیل: mahdi.dadvar → Mahdi Dadvar */
    private function prettyName(string $email): string
    {
        $raw = preg_replace('/[._-]+/', ' ', explode('@', $email)[0]);

        return ucfirst($raw);
    }
}
