<?php

declare(strict_types=1);

namespace Varsite\Orders;

use Varsite\Platform\Contracts\PlatformModule;

final class OrdersModule implements PlatformModule
{
    public function key(): string
    {
        return 'orders';
    }

    public function label(): string
    {
        return 'Zamówienia';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    /** @return array<int, string> */
    public function permissions(): array
    {
        // Brak orders.delete i orders.create: dokumentów handlowych nie tworzy
        // się w panelu ani nie usuwa — powstają w procesie sprzedaży.
        return ['orders.view', 'orders.update'];
    }
}
