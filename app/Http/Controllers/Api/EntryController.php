<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * وعده‌های غذایی — هم‌ارز EntriesController نسخه NestJS.
 *
 * - GET    /api/entries?date=YYYY-MM-DD  → لیست وعده‌های کاربر در آن روز (پیش‌فرض امروز)
 * - POST   /api/entries                  → افزودن وعده (upsert با id کلاینت برای idempotency)
 * - DELETE /api/entries/{id}             → حذف یک وعده
 * - DELETE /api/entries?date=YYYY-MM-DD  → پاک‌کردن همه وعده‌های یک روز (یا همه)
 */
class EntryController extends Controller
{
    public function index(Request $request): \Illuminate\Database\Eloquent\Collection
    {
        $day = $request->query('date') ?? $this->today();

        return Entry::where('user_id', $request->user()->id)
            ->where('date', $day)
            ->orderBy('id')
            ->get();
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            // شناسه کلاینت — اگر فرستاده شود برای idempotency استفاده می‌شود
            'id' => ['nullable', 'string'],
            'name' => ['required', 'string'],
            'calories' => ['required', 'numeric', 'min:0'],
            'protein' => ['required', 'numeric', 'min:0'],
            'carbs' => ['required', 'numeric', 'min:0'],
            'fat' => ['required', 'numeric', 'min:0'],
            // کلید روز YYYY-MM-DD — اگر نباشد امروز لحاظ می‌شود
            'date' => ['nullable', 'date_format:Y-m-d'],
        ], [
            'name.required' => 'نام وعده الزامی است',
            'calories.min' => 'مقدار کالری نمی‌تواند منفی باشد',
            'protein.min' => 'مقدار پروتئین نمی‌تواند منفی باشد',
            'carbs.min' => 'مقدار کربوهیدرات نمی‌تواند منفی باشد',
            'fat.min' => 'مقدار چربی نمی‌تواند منفی باشد',
            'date.date_format' => 'قالب تاریخ باید YYYY-MM-DD باشد',
        ]);

        $id = $data['id'] ?? (string) Str::uuid();

        // هم‌ارز findOneAndUpdate + upsert در نسخه NestJS:
        // اگر همین id قبلاً برای این کاربر ثبت شده باشد، دوباره ساخته نمی‌شود
        $entry = Entry::firstOrCreate(
            ['id' => $id, 'user_id' => $request->user()->id],
            [
                'name' => $data['name'],
                'calories' => $data['calories'],
                'protein' => $data['protein'],
                'carbs' => $data['carbs'],
                'fat' => $data['fat'],
                'date' => $data['date'] ?? $this->today(),
            ],
        );

        return response()->json($entry, 201);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $deleted = Entry::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->delete();

        if ($deleted === 0) {
            return response()->json(['message' => 'وعده یافت نشد'], 404);
        }

        return response()->json(['deleted' => true]);
    }

    public function clear(Request $request): JsonResponse
    {
        $query = Entry::where('user_id', $request->user()->id);

        if ($request->query('date')) {
            $query->where('date', $request->query('date'));
        }

        return response()->json(['deleted' => $query->delete()]);
    }

    private function today(): string
    {
        return date('Y-m-d');
    }
}
