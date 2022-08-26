<?php

namespace Celestial\Migrations;

use Constellation\Database\Blueprint;
use Constellation\Database\Migration;
use Constellation\Database\Schema;

return new class extends Migration {
    public function up()
    {
        return Schema::create("users", function (Blueprint $table) {
            $table->id();
            $table->uuid("uuid");
            $table->varchar("name");
            $table->varchar("email");
            $table->binary("password", 100);
            $table->timestamps();
            $table->unique("email");
            $table->primaryKey("id");
        });
    }

    public function down()
    {
        return Schema::drop("users");
    }
};
