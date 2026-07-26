<?php

declare(strict_types=1);

namespace Varsite\Orders\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Varsite\Orders\Enums\OrderStatus;
use Varsite\Orders\Models\Order;

/**
 * Zmiana stanu zamówienia — zdarzenie BIZNESOWE.
 *
 * Istnieje po to, żeby inne konteksty mogły ZAREAGOWAĆ: wysłać wiadomość,
 * wystawić dokument, zwolnić rezerwację. Nie służy do przepisywania danych
 * między modułami — nikt nie ma na jego podstawie kopiować stanu zamówienia
 * do siebie (N14).
 *
 * Rozpoznanie nadużycia: jeśli słuchacz zapisuje u siebie dane, które już
 * istnieją w zamówieniu, to synchronizacja, a nie reakcja biznesowa.
 */
final class OrderStatusChanged
{
    use Dispatchable;

    public function __construct(
        public readonly Order $order,
        public readonly OrderStatus $from,
        public readonly OrderStatus $to,
    ) {}
}
