<?php

declare(strict_types=1);

namespace Varsite\Orders\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Varsite\Orders\Enums\OrderStatus;

/**
 * Zamówienie — korzeń agregatu i właściciel faktu „co komu sprzedaliśmy".
 *
 * Moduł nie zna katalogu, magazynu ani cennika: wszystko, czego potrzebuje,
 * utrwala w chwili złożenia jako własne fakty. Dzięki temu zamówienie sprzed
 * roku jest czytelne nawet po usunięciu produktu z oferty i po odinstalowaniu
 * modułu katalogu.
 *
 * Dane kupującego również są zapisem historycznym, nie odwołaniem do przyszłego
 * modułu klientów: adres wysyłki z marca to adres z marca, nie bieżący.
 */
final class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'number', 'status', 'currency', 'total_amount',
        'customer_name', 'customer_email', 'shipping_address', 'note',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'total_amount' => 'integer',
        'placed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(static function (self $order): void {
            $order->number ??= self::nextNumber();
            $order->currency = strtoupper((string) ($order->currency ?: 'PLN'));
        });
    }

    /** @return HasMany<OrderLine, self> */
    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    /** @param Builder<self> $query */
    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('status', [
            OrderStatus::Placed->value,
            OrderStatus::Paid->value,
        ]);
    }

    /** Suma pozycji — wyliczana z zapisów historycznych, nigdy z cennika. */
    public function recalculateTotal(): void
    {
        $this->total_amount = (int) $this->lines()->sum('line_amount');
        $this->save();
    }

    private static function nextNumber(): string
    {
        $prefix = now()->format('Y/m');
        $sequence = self::query()->where('number', 'like', $prefix.'/%')->count() + 1;

        return sprintf('%s/%04d', $prefix, $sequence).'/'.Str::upper(Str::random(3));
    }
}
