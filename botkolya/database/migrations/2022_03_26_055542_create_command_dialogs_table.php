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
        Schema::create('command_dialogs', function (Blueprint $table) {
            $table->id();
            $table->integer("telegram_user_id");
            $table->integer("telegram_chat_id");
            $table->string('command');;
            $table->text('data');           
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
        Schema::dropIfExists('command_dialogs');
    }
};
