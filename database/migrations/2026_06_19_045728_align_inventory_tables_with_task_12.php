<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory_stocks', function (Blueprint $table) {
            $table->integer('reserved_quantity')->default(0)->index()->after('quantity');
            $table->index('quantity');
        });

        Schema::table('inventory_logs', function (Blueprint $table) {
            $table->foreignId('inventory_stock_id')->nullable()->after('id')->constrained('inventory_stocks')->cascadeOnDelete();
            $table->string('reason')->nullable()->after('after_quantity');
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });

        DB::table('inventory_logs')->orderBy('id')->each(function (object $log): void {
            $stockId = DB::table('inventory_stocks')
                ->where('product_id', $log->product_id)
                ->when(
                    $log->product_variant_id === null,
                    fn ($query) => $query->whereNull('product_variant_id'),
                    fn ($query) => $query->where('product_variant_id', $log->product_variant_id),
                )
                ->value('id');

            if ($stockId) {
                DB::table('inventory_logs')->where('id', $log->id)->update(['inventory_stock_id' => $stockId]);
            }
        });

        Schema::table('inventory_logs', function (Blueprint $table) {
            $table->renameColumn('before_quantity', 'quantity_before');
            $table->renameColumn('quantity', 'quantity_change');
            $table->renameColumn('after_quantity', 'quantity_after');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_logs', function (Blueprint $table) {
            $table->renameColumn('quantity_before', 'before_quantity');
            $table->renameColumn('quantity_change', 'quantity');
            $table->renameColumn('quantity_after', 'after_quantity');
        });

        Schema::table('inventory_logs', function (Blueprint $table) {
            $table->dropForeign(['inventory_stock_id']);
            $table->dropColumn(['inventory_stock_id', 'reason', 'updated_at']);
        });

        Schema::table('inventory_stocks', function (Blueprint $table) {
            $table->dropIndex(['quantity']);
            $table->dropIndex(['reserved_quantity']);
            $table->dropColumn('reserved_quantity');
        });
    }
};
