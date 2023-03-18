<?php

namespace Celestial\Migrations;

use Constellation\Database\Blueprint;
use Constellation\Database\Migration;
use Constellation\Database\Schema;

return new class extends Migration
{
    public function up()
    {
        return Schema::create("blog", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->varchar("title");
            $table->varchar("subtitle");
            $table->varchar("slug");
            $table->varchar("header_image");
            $table->text("content");
            $table->enum("status", ["public", "private"])->default("'private'");
            $table->dateTime("publish_at");
            $table->timestamps();
            $table->primaryKey("id");
            $table->foreignKey("user_id")
                ->references("users", "id")
                ->onDelete("CASCADE");
        });
    }

    public function down()
    {
        return Schema::drop("blog");
    }
};
