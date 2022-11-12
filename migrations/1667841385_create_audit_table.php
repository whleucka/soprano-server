<?php
namespace Celestial\Migrations;

use Constellation\Database\Blueprint;
use Constellation\Database\Migration;
use Constellation\Database\Schema;

return new class extends Migration {
    public function up()
    {
        return Schema::create("audit", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id")->nullable();
            $table->varchar("table_name");
            $table->varchar("table_id");
            $table->varchar("field");
            $table->text("old_value")->nullable();
            $table->text("new_value")->nullable();
            $table->text("message");
            $table->datetime("created_at");
            $table->primaryKey("id");
            $table->foreignKey("user_id")
              ->references("users", "id")
              ->onDelete("SET NULL");
        });
    }

    public function down()
    {
        return Schema::drop("audit");
    }
};
