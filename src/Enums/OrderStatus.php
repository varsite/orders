<?php

declare(strict_types=1);

namespace Varsite\Orders\Enums;

/**
 * Stan zamówienia — fakt należący WYŁĄCZNIE do modułu zamówień.
 *
 * Nie odzwierciedla stanu magazynu ani dostępności oferty. Zamówienie ma własny
 * cykl życia: to, że pozycja zniknęła z katalogu albo skończyła się w magazynie,
 * nie zmienia faktu, że zostało złożone i opłacone.
 */
enum OrderStatus: string
{
    case Draft = 'draft';
    case Placed = 'placed';
    case Paid = 'paid';
    case Fulfilled = 'fulfilled';
    case Cancelled = 'cancelled';

    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            self::Draft->value => 'Szkic',
            self::Placed->value => 'Złożone',
            self::Paid->value => 'Opłacone',
            self::Fulfilled->value => 'Zrealizowane',
            self::Cancelled->value => 'Anulowane',
        ];
    }

    /** @return array<string, array{tone: string, label: string}> */
    public static function tones(): array
    {
        return [
            self::Draft->value => ['tone' => 'muted', 'label' => 'Szkic'],
            self::Placed->value => ['tone' => 'warn', 'label' => 'Złożone'],
            self::Paid->value => ['tone' => 'ok', 'label' => 'Opłacone'],
            self::Fulfilled->value => ['tone' => 'ok', 'label' => 'Zrealizowane'],
            self::Cancelled->value => ['tone' => 'muted', 'label' => 'Anulowane'],
        ];
    }

    /** Czy przejście do wskazanego stanu jest dopuszczalne. */
    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Draft => in_array($target, [self::Placed, self::Cancelled], true),
            self::Placed => in_array($target, [self::Paid, self::Cancelled], true),
            self::Paid => in_array($target, [self::Fulfilled, self::Cancelled], true),
            self::Fulfilled, self::Cancelled => false,
        };
    }
}
