<?php
namespace Celestial\Migrations;

use Constellation\Database\Blueprint;
use Constellation\Database\Migration;
use Constellation\Database\Schema;

return new class extends Migration {
    public function up()
    {
        return Schema::create("sessions", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->varchar("ip");
            $table->varchar("url");
            $table->timestamp("created_at");
            $table->primaryKey("id");
            $table->foreignKey("user_id")->references("users", "id");
        });
    }

    public function down()
    {
        return Schema::drop("sessions");
    }
};
