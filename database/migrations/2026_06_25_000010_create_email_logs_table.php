<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('language_code', 10)->nullable()->after('customer_email')->index();
        });

        Schema::create('email_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('event', 60)->index();
            $table->string('idempotency_key', 64)->unique();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_transaction_id')->nullable()->constrained('payment_transactions')->nullOnDelete();
            $table->string('recipient_email', 320)->index();
            $table->string('subject');
            $table->string('locale', 10)->default('en');
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->json('payload')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'event']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['language_code']);
            $table->dropColumn('language_code');
        });
    }
};
