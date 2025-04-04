<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aeps_payout_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedBigInteger('report_id')->default(0);
            $table->string('ref_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('bene_id')->nullable();
            $table->string('mode')->nullable();
            $table->decimal('amount',10,2)->default(0);
            $table->unsignedTinyInteger('status')->default(2);
            $table->text('message')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aeps_payout_requests');
    }
};
