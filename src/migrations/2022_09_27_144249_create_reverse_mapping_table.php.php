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
        Schema::create('data_mapping', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('original_data', 128)->default('')->nullable(false)->comment('原始数据');
            $table->string('reverse_data', 128)->default('')->nullable(false)->comment('逆序数据');
            $table->unique(['reverse_data', 'original_data'], 'udx_order_no');
            $table->comment('逆序映射表');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_mapping');
    }
}
