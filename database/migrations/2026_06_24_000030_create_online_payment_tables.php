<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('instruction')->nullable();
            $table->string('gateway_code', 50)->nullable()->index();
            $table->string('environment', 20)->default('sandbox');
            $table->json('config')->nullable();
            $table->text('credentials')->nullable();
            $table->decimal('min_order_amount', 15, 2)->nullable();
            $table->decimal('max_order_amount', 15, 2)->nullable();
            $table->integer('sort_order')->default(20);
            $table->string('status', 20)->default('inactive')->index();
            $table->timestamps();
        });

        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_payment_id')->nullable()->constrained('order_payments')->nullOnDelete();
            $table->foreignId('checkout_session_id')->nullable()->constrained('checkout_sessions')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_number', 100)->unique();
            $table->string('gateway_code', 50)->index();
            $table->string('payment_method_code', 50)->index();
            $table->string('gateway_transaction_id')->nullable()->index();
            $table->string('gateway_reference')->nullable()->index();
            $table->string('status', 30)->default('pending')->index();
            $table->decimal('amount', 15, 2);
            $table->string('currency_code', 10);
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->json('webhook_payload')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable()->index();
            $table->timestamps();
            $table->index(['order_id', 'created_at']);
        });

        Schema::create('payment_webhook_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('gateway_code', 50)->index();
            $table->string('event_id')->nullable();
            $table->foreignId('payment_transaction_id')->nullable()->constrained('payment_transactions')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type')->nullable();
            $table->json('payload');
            $table->json('headers')->nullable();
            $table->boolean('signature_valid')->default(false);
            $table->boolean('processed')->default(false)->index();
            $table->timestamp('processed_at')->nullable();
            $table->text('processing_error')->nullable();
            $table->timestamps();
            $table->unique(['gateway_code', 'event_id']);
        });

        Schema::table('order_payments', function (Blueprint $table): void {
            $table->string('transaction_id')->nullable()->after('currency_code')->index();
            $table->json('gateway_response')->nullable()->after('snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('order_payments', function (Blueprint $table): void {
            $table->dropIndex(['transaction_id']);
            $table->dropColumn(['transaction_id', 'gateway_response']);
        });
        Schema::dropIfExists('payment_webhook_logs');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payment_methods');
    }
};
