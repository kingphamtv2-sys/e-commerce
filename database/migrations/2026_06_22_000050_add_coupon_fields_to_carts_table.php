<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table): void {
            if (! Schema::hasColumn('carts', 'coupon_id')) {
                $table->foreignId('coupon_id')->nullable()->after('currency_code')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('carts', 'coupon_code')) {
                $table->string('coupon_code')->nullable()->after('coupon_id');
            }
            if (! Schema::hasColumn('carts', 'coupon_discount_amount')) {
                $table->decimal('coupon_discount_amount', 15, 2)->default(0)->after('coupon_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table): void {
            if (Schema::hasColumn('carts', 'coupon_id')) {
                $table->dropConstrainedForeignId('coupon_id');
            }
            if (Schema::hasColumn('carts', 'coupon_code')) {
                $table->dropColumn('coupon_code');
            }
            if (Schema::hasColumn('carts', 'coupon_discount_amount')) {
                $table->dropColumn('coupon_discount_amount');
            }
        });
    }
};
