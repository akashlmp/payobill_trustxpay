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
        Schema::create('matmtransactions', function (Blueprint $table) {
            $table->id(); // id column with auto-increment
            $table->integer('user_id'); // user_id column
            $table->string('created_at', 22)->nullable()->collation('utf8mb4_0900_ai_ci'); // created_at column
            $table->timestamp('updated_at'); // updated_at column
            $table->integer('status_id'); // status_id column
            $table->text('apiresponse')->nullable()->collation('utf8mb4_0900_ai_ci'); // apiresponse column
            $table->text('threeway_response')->nullable()->collation('utf8mb4_0900_ai_ci'); // threeway_response column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matmtransactions');
    }
};
