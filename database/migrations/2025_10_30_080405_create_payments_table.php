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
            $table->foreignId('lot_id')->unique()->constrained('auction_lots')->cascadeOnDelete();
            $table->foreignId('bidder_profile_id')->constrained('bidder_profiles');

            // Invoice & pembayaran
            $table->string('invoice_no', 50)->unique();
            $table->decimal('amount_due',14,2);
            $table->string('status', 20)->default('PENDING')->index();
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('paid_at')->nullable();                 
            $table->json('payment_instructions')->nullable();       
            $table->string('pg_transaction_id', 64)->nullable();  
            
            // Shipping (RajaOngkir)
            $table->unsignedInteger('shipping_rajaongkir_district_id')->nullable();
            $table->integer('shipping_weight')->default(0);
            $table->string('shipping_courier', 20)->nullable();
            $table->string('shipping_service', 50)->nullable();
            $table->integer('shipping_fee')->default(0);
            $table->string('shipping_etd', 20)->nullable();
            $table->string('shipping_tracking_no', 50)->nullable();
            $table->string('shipping_status', 20)->default('PENDING');
            $table->timestamp('shipping_shipped_at')->nullable();
            $table->timestamp('shipping_completed_at')->nullable();
            $table->text('shipping_raw_response')->nullable();

            // Alamat pengiriman
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('phone', 20)->nullable();

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
