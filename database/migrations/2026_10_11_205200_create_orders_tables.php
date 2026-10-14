<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending')->index();
            $table->string('payment_status')->default('pending')->index();
            $table->string('fulfilment_status')->default('unfulfilled')->index();
            $table->string('currency', 3)->default('MDL');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('options_total')->default(0);
            $table->unsignedInteger('delivery_total')->default(0);
            $table->unsignedInteger('discount_total')->default(0);
            $table->unsignedInteger('total');
            $table->timestamp('placed_at')->nullable()->index();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });

        Schema::create('order_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('shipping');
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->string('address_line');
            $table->string('city');
            $table->string('region')->nullable();
            $table->string('postal_code', 32)->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'type']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku');
            $table->string('name');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_base_price');
            $table->unsignedInteger('unit_options_total')->default(0);
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('line_total');
            $table->timestamps();

            $table->index('order_id');
        });

        Schema::create('order_item_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_option_value_id')->nullable()->constrained()->nullOnDelete();
            $table->string('option_group_name');
            $table->string('option_value_name');
            $table->unsignedInteger('surcharge')->default(0);
            $table->timestamps();
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status_type');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['order_id', 'created_at']);
        });

        Schema::create('order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('method_code');
            $table->string('method_name');
            $table->unsignedInteger('charge')->default(0);
            $table->string('status')->default('pending')->index();
            $table->string('tracking_number')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('method_code');
            $table->string('method_name');
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('MDL');
            $table->string('status')->default('pending')->index();
            $table->string('provider_reference')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });

        Schema::create('notification_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('template_key');
            $table->string('channel');
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'template_key']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_dispatches');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_deliveries');
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_item_options');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('order_addresses');
        Schema::dropIfExists('order_contacts');

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::dropIfExists('orders');
    }
};
