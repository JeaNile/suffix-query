<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateReverseMappingTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reverse_mapping', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('original_data', 255)->nullable(false)->comment('原始数据');
            $table->string('reverse_data', 255)->nullable(false)->comment('逆序数据');
            $table->rawIndex('original_data(5)', 'idx_original_data');
            $table->comment('逆序映射表');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reverse_mapping');
    }
}
