<?php

declare(strict_types=1);

namespace Varsite\Orders;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Varsite\Orders\Enums\OrderStatus;
use Varsite\Orders\Models\Order;
use Varsite\Orders\Policies\OrderPolicy;
use Varsite\Platform\Capabilities\Column;
use Varsite\Platform\Capabilities\Filter;
use Varsite\Platform\Capabilities\ResourceCapability;
use Varsite\Platform\Capabilities\CapabilityRegistry;
use Varsite\Platform\Routing\ModuleRouteRegistrar;
use Varsite\Platform\Support\ModuleManager;

/**
 * Moduł Zamówienia.
 *
 * Rejestracja przebiega w trzech krokach — tożsamość, trasy, możliwości.
 * Jakie możliwości zarejestrować, decyduje domena modułu; szkielet ich nie
 * narzuca. Wzorce i dostępne rodzaje: docs/EXTENSIBILITY.md.
 */
final class OrdersServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->app->make(ModuleManager::class)->module(
            new OrdersModule(),
            function (): void {
            $this->app->make(ModuleRouteRegistrar::class)
                ->register('orders', require __DIR__.'/../routes/admin.php');

            Gate::policy(Order::class, OrderPolicy::class);

            $this->registerCapabilities();
            },
        );
    }

    /**
     * Możliwości modułu — zasoby, widgety, ustawienia.
     *
     * Nawigacja, paleta poleceń i wyszukiwarka globalna wynikają z możliwości,
     * więc moduł nie deklaruje pozycji menu ani API wyszukiwania.
     */
    private function registerCapabilities(): void
    {
        $this->app->make(CapabilityRegistry::class)->register(
            ResourceCapability::make('orders.orders')
                ->label('Zamówienie', 'Zamówienia')
                ->icon('receipt')
                ->endpoint('/v1/admin/orders')
                ->permission('orders.view')
                ->columns([
                    Column::text('number')->label('Numer')->sortable()->primary(),
                    Column::status('status', OrderStatus::tones())->label('Stan'),
                    Column::text('customer_name')->label('Kupujący'),
                    Column::number('total_amount')->label('Wartość (grosze)')->sortable(),
                    Column::date('placed_at')->label('Złożono')->sortable(),
                ])
                ->filters([
                    Filter::search(['number', 'customer_name', 'customer_email']),
                    Filter::segmented('status', ['all' => 'Wszystkie'] + OrderStatus::options()),
                ])
                // Bez formularza i bez akcji: dokumentu handlowego nie edytuje
                // się w panelu. Stan zmienia osobny endpoint z kontrolą przejść.
                ->actions([]),
        );
    }
}
