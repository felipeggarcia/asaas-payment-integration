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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->string('customer_id')->nullable();
            $table->string('payment_method');
            $table->decimal('amount', 10, 2);
            $table->date('due_date')->nullable();
            $table->string('status');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->text('invoice_url')->nullable();
            $table->text('bank_slip_url')->nullable();
            $table->text('pix_qr_code')->nullable();
            $table->text('pix_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
