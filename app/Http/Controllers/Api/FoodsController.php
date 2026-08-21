<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\FoodLibrary;
use Illuminate\Http\Request;

/**
 * کتابخانه غذاها — هم‌ارز FoodsController نسخه NestJS.
 */
class FoodsController extends Controller
{
    public function index(Request $request): array
    {
        return FoodLibrary::search($request->query('q'));
    }
}
