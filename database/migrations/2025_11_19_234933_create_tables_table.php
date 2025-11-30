<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('table_number'); // стол 1, стол 2, стол 3, стол 4, Барная стойка, стол 6, стол 7
            $table->date('booking_date');
            $table->time('booking_time');
            $table->integer('duration'); // длительность в минутах
            $table->integer('guests_count');
            $table->text('comment')->nullable();
            $table->string('phone')->nullable();
            $table->string('guest_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_bookings');
    }
};
