<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            if (! Schema::hasColumn('carts', 'status')) {
                $table->string('status', 30)->default('active')->index()->after('session_id');
            }
            if (! Schema::hasColumn('carts', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->index()->after('currency_code');
            }
        });

        Schema::table('cart_items', function (Blueprint $table) {
            if (! Schema::hasColumn('cart_items', 'unit_price')) {
                $table->decimal('unit_price', 15, 2)->default(0)->after('quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            if (Schema::hasColumn('cart_items', 'unit_price')) {
                $table->dropColumn('unit_price');
            }
        });

        Schema::table('carts', function (Blueprint $table) {
            if (Schema::hasColumn('carts', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
            if (Schema::hasColumn('carts', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
