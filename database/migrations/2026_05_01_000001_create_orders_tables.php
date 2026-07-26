<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('number')->unique();
            $table->string('status')->default('draft')->index();
            $table->string('currency', 3)->default('PLN');
            $table->unsignedBigInteger('total_amount')->default(0);

            // Dane kupującego jako zapis historyczny — nie odwołanie do modułu
            // klientów, który może powstać później i zmieniać swoje dane.
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->text('shipping_address')->nullable();
            $table->text('note')->nullable();

            $table->timestamp('placed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('orders_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

            // SKU jest identyfikatorem naturalnym, NIE kluczem obcym do katalogu:
            // zamówienie przetrwa usunięcie produktu i odinstalowanie modułu.
            $table->string('sku');
            $table->string('name');
            $table->unsignedBigInteger('unit_amount');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('line_amount');
            $table->timestamps();

            $table->index(['order_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders_lines');
        Schema::dropIfExists('orders');
    }
};
