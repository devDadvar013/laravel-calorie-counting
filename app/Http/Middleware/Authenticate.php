<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

/**
 * نسخه سفارشی middleware احراز هویت:
 * مسیرهای API هرگز به صفحه لاگین هدایت نمی‌شوند — همیشه 401 JSON می‌گیرند
 * (مثل نسخه NestJS که برای همه درخواست‌های بدون توکن 401 برمی‌گرداند).
 */
class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return null;
        }

        return route('login');
    }
}
