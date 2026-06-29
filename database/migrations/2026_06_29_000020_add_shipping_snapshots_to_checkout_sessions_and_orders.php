<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checkout_sessions', function (Blueprint $table): void {
            $table->foreignId('shipping_method_id')->nullable()->after('coupon_snapshot')->constrained('shipping_methods')->nullOnDelete();
            $table->string('shipping_method_code')->nullable()->after('shipping_method_id');
            $table->string('shipping_method_name')->nullable()->after('shipping_method_code');
            $table->text('shipping_method_description')->nullable()->after('shipping_method_name');
            $table->foreignId('shipping_zone_id')->nullable()->after('shipping_method_description')->constrained('shipping_zones')->nullOnDelete();
            $table->string('shipping_zone_name')->nullable()->after('shipping_zone_id');
            $table->decimal('base_shipping_amount', 15, 2)->default(0)->after('shipping_zone_name');
            $table->string('shipping_estimated_delivery')->nullable()->after('shipping_amount');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('shipping_method_id')->nullable()->after('coupon_snapshot')->constrained('shipping_methods')->nullOnDelete();
            $table->string('shipping_method_code')->nullable()->after('shipping_method_id');
            $table->string('shipping_method_name')->nullable()->after('shipping_method_code');
            $table->text('shipping_method_description')->nullable()->after('shipping_method_name');
            $table->foreignId('shipping_zone_id')->nullable()->after('shipping_method_description')->constrained('shipping_zones')->nullOnDelete();
            $table->string('shipping_zone_name')->nullable()->after('shipping_zone_id');
            $table->decimal('base_shipping_amount', 15, 2)->default(0)->after('shipping_zone_name');
            $table->string('shipping_estimated_delivery')->nullable()->after('shipping_fee');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['shipping_method_id']);
            $table->dropForeign(['shipping_zone_id']);
            $table->dropColumn([
                'shipping_method_id',
                'shipping_method_code',
                'shipping_method_name',
                'shipping_method_description',
                'shipping_zone_id',
                'shipping_zone_name',
                'base_shipping_amount',
                'shipping_estimated_delivery',
            ]);
        });

        Schema::table('checkout_sessions', function (Blueprint $table): void {
            $table->dropForeign(['shipping_method_id']);
            $table->dropForeign(['shipping_zone_id']);
            $table->dropColumn([
                'shipping_method_id',
                'shipping_method_code',
                'shipping_method_name',
                'shipping_method_description',
                'shipping_zone_id',
                'shipping_zone_name',
                'base_shipping_amount',
                'shipping_estimated_delivery',
            ]);
        });
    }
};
