<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('organization_service_message', function (Blueprint $table) {
            $table->integer('service_message_id')->unsigned()->nullable(false);
            $table->foreign('service_message_id')->references('id')->on('service_messages')->onDelete('cascade');

            $table->integer('organization_id')->unsigned()->nullable(false);
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('organization_service_message');
    }
};
