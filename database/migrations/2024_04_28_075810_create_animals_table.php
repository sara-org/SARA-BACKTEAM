<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnimalsTable extends Migration
{
    public function up()
    {
        Schema::create('animals', function (Blueprint $table) {

            $table->id();
            $table->string('name');
            $table->integer('age');
            $table->longText('photo')->nullable();
            $table->date('entry_date');
            $table->enum('health', ['healthy', 'unhealthy', 'under treatment']);
            $table->timestamps();
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('animaltype_id');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('animaltype_id')->references('id')->on('animaltypes');

        });
    }

    public function down()
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['animaltype_id']);
        });

        Schema::dropIfExists('animals');
    }
}
