# شمارنده کالری — بک‌اند Laravel

بازنویسی بک‌اند **NestJS + MongoDB** اپلیکیشن کالری‌شمار با **Laravel 12 + PostgreSQL (Neon)**.
همان endpoint ها، همان ساختار پاسخ‌ها و همان پیام‌های فارسی — بدون تغییر در فرانت‌اند (Angular).

## پیش‌نیازها

- PHP 8.2+ (با اکستنشن `pdo_pgsql`)
- Composer
- یک پروژه PostgreSQL روی [Neon](https://console.neon.tech/) (سرورلس و رایگان)

## نصب و اجرا

```bash
# ۱. نصب وابستگی‌ها
composer install

# ۲. تنظیم کانکشن Neon در .env — از داشبورد Neon کانکشن‌استرینگ را کپی کنید
#    و در DB_URL بگذارید (فقط همین یک خط کافی است):
#    DB_URL=postgresql://USER:PASSWORD@HOST.neon.tech/DBNAME?sslmode=require

# ۳. مهاجرت‌ها و داده‌های نمونه (کاربر پیش‌فرض demo@calorie.app / demo1234)
php artisan migrate --seed

# ۴. اجرای سرور
php artisan serve
# → http://localhost:8000/api
```

> **نکته Neon + libpq قدیمی:** اگر هنگام اتصال خطای
> `Endpoint ID is not specified` گرفتید، یعنی libpq شما از SNI پشتیبانی نمی‌کند؛
> در `.env` بنویسید:
> `DB_NEON_ENDPOINT=ep-xxxxxxx` (قسمت اول دامنه هاست).

## اجرای تست‌ها

تست‌ها با SQLite درون‌حافظه اجرا می‌شوند و به MySQL نیازی ندارند:

```bash
php artisan test
```

## Endpoint ها

| متد | مسیر | محافظت‌شده | توضیح |
|-----|------|-----------|-------|
| POST | `/api/auth/register` | — | ثبت‌نام → `{ token, user }` |
| POST | `/api/auth/login` | — | ورود → `{ token, user }` |
| GET | `/api/auth/me` | ✅ | پروفایل کاربر جاری |
| GET | `/api/foods?q=` | — | کتابخانه غذاهای ایرانی (جستجوی بدون فاصله) |
| GET | `/api/entries?date=YYYY-MM-DD` | ✅ | وعده‌های یک روز (پیش‌فرض امروز) |
| POST | `/api/entries` | ✅ | افزودن وعده (upsert با `id` کلاینت) |
| DELETE | `/api/entries/{id}` | ✅ | حذف یک وعده |
| DELETE | `/api/entries?date=` | ✅ | پاک‌کردن وعده‌های یک روز |
| GET | `/api/goal` | ✅ | هدف کالری روزانه |
| PUT | `/api/goal` | ✅ | تغییر هدف کالری |

احراز هویت با توکن‌های **Sanctum** (هدر `Authorization: Bearer <token>`) — سازگار با
اینترسپتور فرانت‌اند Angular که برای نسخه NestJS نوشته شده است. توکن‌ها بعد از ۷ روز منقضی می‌شوند.

## تفاوت‌ها با نسخه NestJS

| مورد | NestJS (اصلی) | Laravel (این نسخه) |
|------|---------------|--------------------|
| دیتابیس | MongoDB Atlas | PostgreSQL (Neon) |
| توکن | JWT دست‌ساز (۷ روز) | Sanctum personal access token (۷ روز) |
| شناسه کاربران | UUID رشته‌ای در MongoDB | ستون `uuid` در MySQL |
| شناسه وعده‌ها | تولید سمت کلاینت | همان (`string` primary key) |

ساختار پاسخ‌ها و منطق (upsert وعده‌ها، هدف پیش‌فرض ۲۰۰۰، کاربر نمونه با ۵ وعده امروز) بدون تغییر حفظ شده است.

## اتصال به فرانت‌اند Angular

فرانت‌اند فعلی به `http://localhost:3000` اشاره می‌کند. برای استفاده از این بک‌اند:

```bash
php artisan serve --port=3000
```
