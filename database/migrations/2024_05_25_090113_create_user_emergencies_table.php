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
        Schema::create('user_emergencies', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('status', ['healthy', 'unhealthy', 'under treatment', 'passed', 'unavailable']);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('emergency_id');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('emergency_id')->references('id')->on('emergencies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_emergencies');
    }

};
