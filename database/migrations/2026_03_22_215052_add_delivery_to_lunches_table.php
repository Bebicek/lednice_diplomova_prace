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
        Schema::table('lunches', function (Blueprint $table) {
            $table->integer('delivery_cost')->default(0)->after('total_amount');
            $table->string('split_method')->default('by_price')->after('delivery_cost'); // 'equal' | 'by_price'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lunches', function (Blueprint $table) {
            $table->dropColumn(['delivery_cost', 'split_method']);
        });
    }
};
