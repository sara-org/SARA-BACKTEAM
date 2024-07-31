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
        Schema::create('sponcerships', function (Blueprint $table) {
            $table->id();
            $table->integer('balance'); 
            $table->boolean('spon_status')->default(0);
            $table->date('sponcership_date');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('animal_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('animal_id')->references('id')->on('animals');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('sponcerships', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['animal_id']);

        });

        Schema::dropIfExists('sponcerships');
    }
};
