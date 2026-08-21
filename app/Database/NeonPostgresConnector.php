<?php

namespace App\Database;

use Illuminate\Database\Connectors\PostgresConnector;

/**
 * اتصال به PostgreSQL/Neon وقتی کلاینت libpq از SNI پشتیبانی نمی‌کند.
 *
 * Neon برای کلاینت‌های بدون SNI نیاز دارد endpoint id (قسمت اول دامنه) صریحاً
 * در options پاس داده شود؛ در غیر این صورت خطای «Endpoint ID is not specified» می‌گیریم.
 * این کانکتور فقط وقتی DB_NEON_ENDPOINT تنظیم شده باشد options را اضافه می‌کند.
 */
class NeonPostgresConnector extends PostgresConnector
{
    protected function getDsn(array $config)
    {
        $dsn = parent::getDsn($config);

        $endpoint = $config['neon_endpoint'] ?? null;
        if (is_string($endpoint) && $endpoint !== '') {
            $dsn .= ";options='endpoint={$endpoint}'";
        }

        return $dsn;
    }
}
