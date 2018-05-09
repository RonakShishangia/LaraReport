<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttendanceReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('employee_id')->unsigned();
            $table->date('date')->nullabe();
            $table->string('name')->nullable();
            $table->string('department')->nullable();
            $table->time('officeIn')->nullable();
            $table->time('officeOut')->nullable();
            $table->time('noonOut')->nullable();
            $table->time('noonIn')->nullable();
            $table->string('attendance')->nullable();
            $table->time('tatalTime')->nullable();
            $table->string('OT')->nullable();
            $table->longText('thumbs')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_reports');
    }
}
