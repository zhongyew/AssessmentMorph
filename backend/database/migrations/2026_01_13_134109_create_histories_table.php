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
    Schema::create('histories', function (Blueprint $table) {
        $table->id();
        $table->text('input_text');      // 存储长句子建议用 text
        $table->text('output_result');   // AI 纠错结果
        $table->timestamps();            // 这会自动生成 created_at (对应你的 date)
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
