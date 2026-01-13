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
    Schema::table('histories', function (Blueprint $table) {
        $table->integer('tokens_used')->default(1); // 默认消耗 1 token
        $table->decimal('fee', 8, 2)->default(0.99); // 1 token = 0.99 USD
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('histories', function (Blueprint $table) {
            //
        });
    }
};
