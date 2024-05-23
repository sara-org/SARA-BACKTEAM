<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedicalRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->text('description');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('animal_id');
            $table->timestamps();
            $table->foreign('doctor_id')->references('id')->on('users');
            $table->foreign('animal_id')->references('id')->on('animals');
        });
    }
        public function down()
        {
    Schema::table('medical_records', function (Blueprint $table) {
        $table->dropForeign(['doctor_id']);
        $table->dropForeign(['animal_id']);
    });

    Schema::dropIfExists('medical_records');
}
}