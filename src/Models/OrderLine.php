<?php

declare(strict_types=1);

namespace Varsite\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pozycja zamówienia — ZAPIS HISTORYCZNY, nie odwołanie do katalogu.
 *
 * Nazwa, SKU i cena są utrwalone w chwili złożenia zamówienia i **nigdy się nie
 * zmieniają**, choćby produkt został przemianowany, przeceniony albo usunięty.
 * To nie jest kopia cudzego faktu, lecz WŁASNY fakt modułu zamówień:
 * „sprzedano to, pod tą nazwą, za tyle, wtedy" (N14, wyjątek historyczny).
 *
 * Test rozpoznawczy: kopia zmienia się razem ze źródłem, zapis historyczny nigdy.
 *
 * Czego tu świadomie NIE MA:
 *  - aktualnej ceny pozycji  → należy do modułu cen,
 *  - aktualnej dostępności   → należy do magazynu,
 *  - klucza obcego do katalogu → moduł zamówień działa bez katalogu.
 */
final class OrderLine extends Model
{
    protected $table = 'orders_lines';

    protected $fillable = ['order_id', 'sku', 'name', 'unit_amount', 'quantity', 'line_amount'];

    protected $casts = [
        'order_id' => 'integer',
        'unit_amount' => 'integer',
        'quantity' => 'integer',
        'line_amount' => 'integer',
    ];

    /** @return BelongsTo<Order, self> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
