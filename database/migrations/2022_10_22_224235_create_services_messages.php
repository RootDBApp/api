<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('service_messages', function (Blueprint $table) {
            $table->integer('id')->unsigned()->autoIncrement();
            $table->tinyText('title')->nullable(false);
            $table->text('contents')->nullable(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_messages');
    }
};
