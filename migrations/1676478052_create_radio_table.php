<?php

namespace Celestial\Migrations;

use Constellation\Database\Blueprint;
use Constellation\Database\Migration;
use Constellation\Database\Schema;

return new class extends Migration
{
    public function up()
    {
        return Schema::create("radio", function (Blueprint $table) {
            $table->id();
            $table->varchar("station_name");
            $table->varchar("location");
            $table->varchar("src_url");
            $table->varchar("cover_url");
            $table->timestamps();
            $table->primaryKey("id");
        });
    }

    public function down()
    {
        return Schema::drop("radio");
    }
};
