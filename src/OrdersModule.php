<?php

declare(strict_types=1);

namespace Varsite\Orders;

use Varsite\Platform\Contracts\ModuleManifest;
use Varsite\Platform\Contracts\PlatformModule;

final class OrdersModule implements PlatformModule
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            key: 'orders',
            name: 'Zamówienia',
            version: '1.0.0',
            description: 'Dokumenty sprzedaży z zapisem historycznym pozycji.',
            author: 'Varsite',
            section: 'sales',
            icon: 'receipt',
            order: 30,
            permissions: [
                'orders.view',
                'orders.update',
            ],
            requiresGeneration: '^0.6',
        );
    }
}
