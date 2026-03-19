<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('debts', function (Blueprint $table) {
            // default(true) = fridge/order debts are auto accepted
            // false = lunch debts wait for participant acceptance
            $table->boolean('is_accepted')->default(true)->after('is_paid');
            $table->timestamp('accepted_at')->nullable()->after('is_accepted');
        });
    }

    public function down(): void
    {
        Schema::table('debts', function (Blueprint $table) {
            $table->dropColumn(['is_accepted', 'accepted_at']);
        });
    }
};
