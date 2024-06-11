<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('empreqs', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('request_id');
        $table->foreign('request_id')->references('id')->on('requests')->onDelete('cascade');
        $table->integer('amount');
        $table->string('item');
        $table->enum('status', ['sended','doing','unavailable','done'])->default('sended');
        $table->timestamps();


    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empreqs');
    }
};
