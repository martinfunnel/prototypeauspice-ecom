<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->foreignUuid('commune_id')->nullable()->constrained('communes')->onDelete('set null');
            $table->string('commune_name');
            $table->text('address');
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 12, 0)->default(0);
            $table->decimal('delivery_fee', 12, 0)->default(0);
            $table->decimal('total', 12, 0)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->index('customer_phone');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
