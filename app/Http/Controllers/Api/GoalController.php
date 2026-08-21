<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * هدف کالری روزانه — هم‌ارز GoalController نسخه NestJS.
 *
 * - GET /api/goal → { goal }
 * - PUT /api/goal → { goal }
 */
class GoalController extends Controller
{
    public function show(Request $request): array
    {
        return ['goal' => $request->user()->goal];
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'goal' => ['required', 'integer', 'min:0'],
        ], [
            'goal.integer' => 'هدف باید یک عدد صحیح باشد',
            'goal.min' => 'هدف نمی‌تواند منفی باشد',
        ]);

        // همان رفتار NestJS: max(0, round(goal))
        $value = max(0, (int) round($data['goal']));

        $request->user()->update(['goal' => $value]);

        return response()->json(['goal' => $value]);
    }
}
