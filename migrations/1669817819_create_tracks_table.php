<?php
namespace Celestial\Migrations;

use Constellation\Database\Blueprint;
use Constellation\Database\Migration;
use Constellation\Database\Schema;

return new class extends Migration {
    public function up()
    {
        return Schema::create("tracks", function (Blueprint $table) {
            $table->id();
            $table->char("md5", 32);
            $table->varchar("cover");
            $table->unsignedBigInteger("filesize");
            $table->text("filenamepath");
            $table->varchar("file_format");
            $table->varchar("mime_type");
            $table->unsignedBigInteger("bitrate");
            $table->decimal("playtime_seconds", 12, 5);
            $table->varchar("playtime_string");
            $table->varchar("track_number");
            $table->varchar("artist");
            $table->varchar("album");
            $table->varchar("title");
            $table->varchar("year");
            $table->varchar("genre");
            $table->unique("md5");
            $table->timestamps();
            $table->primaryKey("id");
        });
    }

    public function down()
    {
        return Schema::drop("tracks");
    }
};
