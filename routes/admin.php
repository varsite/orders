<?php

declare(strict_types=1);

use Varsite\Orders\Http\Controllers\Admin\OrderController;
use Varsite\Platform\Routing\ScopedRoutes;

return static function (ScopedRoutes $r): void {
    $r->middleware(['auth:sanctum'])->prefix('api/v1/admin/orders')->group(function (ScopedRoutes $r): void {
        $r->get('/', [OrderController::class, 'index']);
        $r->get('{order}', [OrderController::class, 'show']);
        // Brak POST, PATCH i DELETE na samym zamówieniu — dokument handlowy
        // nie jest rekordem do edycji. Zmienia się wyłącznie stan.
        $r->patch('{order}/status', [OrderController::class, 'updateStatus']);
    });
};
