<?php
namespace Celestial\Migrations;

use Constellation\Database\Blueprint;
use Constellation\Database\Migration;
use Constellation\Database\Schema;

return new class extends Migration {
    public function up()
    {
        return Schema::create("playlists", function (Blueprint $table) {
            $table->id();
            $table->uuid("uuid");
            $table->unsignedBigInteger("customer_id");
            $table->timestamps();
            $table->primaryKey("id");
            $table->foreignKey("customer_id")
              ->references("customers", "id")
              ->onDelete("CASCADE");
        });
    }

    public function down()
    {
        return Schema::drop("playlists");
    }
};
