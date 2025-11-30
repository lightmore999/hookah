<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_booking_hookahs', function (Blueprint $table) {
            // Удаляем уникальное ограничение, чтобы можно было добавлять одинаковые кальяны несколько раз
            // Сначала нужно удалить внешние ключи, затем индекс, затем восстановить ключи
        });

        // Получаем список внешних ключей
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'table_booking_hookahs' 
            AND CONSTRAINT_NAME != 'PRIMARY'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        // Временно удаляем внешние ключи
        foreach ($foreignKeys as $fk) {
            try {
                DB::statement("ALTER TABLE table_booking_hookahs DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            } catch (\Exception $e) {
                // Игнорируем ошибки
            }
        }

        // Удаляем уникальный индекс
        try {
            DB::statement("ALTER TABLE table_booking_hookahs DROP INDEX table_booking_hookahs_table_booking_id_hookah_id_unique");
        } catch (\Exception $e) {
            // Индекс может не существовать
        }

        // Восстанавливаем внешние ключи
        Schema::table('table_booking_hookahs', function (Blueprint $table) {
            $table->foreign('table_booking_id')->references('id')->on('table_bookings')->onDelete('cascade');
            $table->foreign('hookah_id')->references('id')->on('hookahs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_booking_hookahs', function (Blueprint $table) {
            // Восстанавливаем уникальное ограничение
            $table->unique(['table_booking_id', 'hookah_id']);
        });
    }
};
