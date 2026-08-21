<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * یک وعده ثبت‌شده برای یک روز خاص.
 *
 * شناسه (id) از سمت کلاینت تولید می‌شود تا افزودن خوش‌بینانه بدون تغییر id
 * همگام شود — دقیقاً مانند نسخه NestJS + MongoDB.
 */
class Entry extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'name',
        'calories',
        'protein',
        'carbs',
        'fat',
        'date',
    ];

    /**
     * فیلدهای داخلی — هم‌ارز toJSON در نسخه NestJS که userId را حذف می‌کرد.
     *
     * @var list<string>
     */
    protected $hidden = [
        'user_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'calories' => 'integer',
            'protein' => 'float',
            'carbs' => 'float',
            'fat' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
