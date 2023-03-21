<?php
namespace Celestial\Migrations;

use Constellation\Database\Blueprint;
use Constellation\Database\Migration;
use Constellation\Database\Schema;

return new class extends Migration {
    public function up()
    {
        return Schema::create("track_likes", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("customer_id");
            $table->unsignedBigInteger("track_id");
            $table->primaryKey("id");
            $table->foreignKey("customer_id")
              ->references("customers", "id")
              ->onDelete("CASCADE");
            $table->foreignKey("track_id")
              ->references("tracks", "id")
              ->onDelete("CASCADE");
            $table->unique("customer_id,track_id");
        });
    }

    public function down()
    {
        return Schema::drop("track_likes");
    }
};

