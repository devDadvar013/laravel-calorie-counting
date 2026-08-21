<?php

namespace Database\Seeders;

use App\Models\Entry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * اعتبارنامه پیش‌فرض — با فیلدهای از پیش پر شده صفحه لاگین فرانت‌اند هماهنگ است.
 *
 * در اولین اجرا، اگر کاربر پیش‌فرض وجود نداشته باشد آن را می‌سازد و چند وعده
 * نمونه برای امروزش اضافه می‌کند — همان رفتار SeedService نسخه NestJS.
 */
class DatabaseSeeder extends Seeder
{
    public const DEMO_EMAIL = 'demo@calorie.app';

    public const DEMO_PASSWORD = 'demo1234';

    public const DEMO_NAME = 'کاربر نمونه';

    /** چند غذای نمونه برای کاربر پیش‌فرض تا داشبورد خالی نباشد */
    private const SAMPLE_ENTRIES = [
        ['name' => 'تخم‌مرغ آب‌پز', 'calories' => 155, 'protein' => 13, 'carbs' => 1.1, 'fat' => 11],
        ['name' => 'نان سنگک', 'calories' => 260, 'protein' => 8, 'carbs' => 55, 'fat' => 1],
        ['name' => 'برنج پخته', 'calories' => 130, 'protein' => 2.7, 'carbs' => 28, 'fat' => 0.3],
        ['name' => 'سینه مرغ کبابی', 'calories' => 165, 'protein' => 31, 'carbs' => 0, 'fat' => 3.6],
        ['name' => 'سیب', 'calories' => 52, 'protein' => 0.3, 'carbs' => 14, 'fat' => 0.2],
    ];

    public function run(): void
    {
        $existing = User::where('email', self::DEMO_EMAIL)->first();
        if ($existing) {
            return;
        }

        $user = User::create([
            'email' => self::DEMO_EMAIL,
            'name' => self::DEMO_NAME,
            'password' => self::DEMO_PASSWORD,
            'goal' => 2000,
        ]);

        foreach (self::SAMPLE_ENTRIES as $entry) {
            Entry::create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                ...$entry,
                'date' => date('Y-m-d'),
            ]);
        }

        $this->command?->info(
            'کاربر پیش‌فرض ساخته شد: '.self::DEMO_EMAIL
            .' (با '.count(self::SAMPLE_ENTRIES).' وعده نمونه)',
        );
    }
}
