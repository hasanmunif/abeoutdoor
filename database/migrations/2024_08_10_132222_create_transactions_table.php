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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('name');
            $table->string('trx_id');
            $table->string('phone_number');
            $table->string('proof')->nullable();
            $table->text('address');
            $table->date('started_at');
            $table->unsignedBigInteger('duration');
            $table->date('ended_at');
            $table->boolean('is_paid');
            $table->enum('status', [
                'menunggu konfirmasi',
                'diproses',
                'selesai',
                'dibatalkan',
                'menunggu pembayaran'
            ])->default('menunggu konfirmasi');
            $table->enum('delivery_type', ['pickup', 'delivery'])->default('pickup');
            $table->unsignedBigInteger('total_amount');
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('payment_method');
            $table->string('transaction_group_id')->nullable();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};