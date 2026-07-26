<?php

declare(strict_types=1);

namespace Varsite\Orders\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Varsite\Orders\Enums\OrderStatus;
use Varsite\Orders\Events\OrderStatusChanged;
use Varsite\Orders\Models\Order;

/**
 * Administracyjne API zamówień.
 *
 * Zamówień się nie edytuje ani nie usuwa: dokument handlowy jest zapisem
 * zdarzenia, a nie rekordem do poprawiania. Zmienia się wyłącznie STAN,
 * i tylko zgodnie z dozwolonymi przejściami.
 */
final class OrderController
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Order::class);

        $orders = Order::query()
            ->withSum('lines', 'quantity')
            ->when($request->string('q')->toString() !== '', function ($query) use ($request): void {
                $phrase = $request->string('q')->toString();
                $query->where(fn ($q) => $q->where('number', 'like', "%{$phrase}%")
                    ->orWhere('customer_email', 'like', "%{$phrase}%")
                    ->orWhere('customer_name', 'like', "%{$phrase}%"));
            })
            ->when($request->string('status')->toString() !== '', fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->latest('id')
            ->paginate(20);

        return response()->json($orders);
    }

    public function show(int $order): JsonResponse
    {
        $model = Order::with('lines')->findOrFail($order);
        Gate::authorize('view', $model);

        return response()->json(['data' => [
            ...$model->toArray(),
            // Pozycje to zapisy historyczne — wydajemy je dokładnie takie,
            // jakie utrwalono przy składaniu zamówienia.
            'lines' => $model->lines->map(static fn ($line): array => [
                'sku' => $line->sku,
                'name' => $line->name,
                'quantity' => $line->quantity,
                'unit_amount' => $line->unit_amount,
                'line_amount' => $line->line_amount,
            ]),
        ]]);
    }

    /** Zmiana stanu — jedyna dozwolona modyfikacja zamówienia. */
    public function updateStatus(Request $request, int $order): JsonResponse
    {
        $model = Order::findOrFail($order);
        Gate::authorize('update', $model);

        $data = $request->validate(['status' => ['required', new Enum(OrderStatus::class)]]);
        $target = OrderStatus::from($data['status']);
        $current = $model->status;

        if (! $current->canTransitionTo($target)) {
            return response()->json([
                'message' => sprintf(
                    'Przejście ze stanu "%s" do "%s" jest niedozwolone.',
                    OrderStatus::options()[$current->value],
                    OrderStatus::options()[$target->value],
                ),
            ], 422);
        }

        $model->status = $target;

        if ($target === OrderStatus::Placed && $model->placed_at === null) {
            $model->placed_at = now();
        }

        $model->save();

        // Zdarzenie biznesowe: inne konteksty mogą zareagować (wiadomość,
        // dokument, zwolnienie rezerwacji). Nie służy do kopiowania danych.
        OrderStatusChanged::dispatch($model, $current, $target);

        return response()->json(['data' => $model->fresh()]);
    }
}
