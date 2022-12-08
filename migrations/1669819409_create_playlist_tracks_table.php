<?php

namespace Celestial\Migrations;

use Constellation\Database\Blueprint;
use Constellation\Database\Migration;
use Constellation\Database\Schema;

return new class extends Migration
{
    public function up()
    {
        return Schema::create("playlist_tracks", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("playlist_id");
            $table->unsignedBigInteger("track_id");
            $table->unique("playlist_id, track_id");
            $table->foreignKey("playlist_id")
                ->references("playlists", "id")
                ->onDelete("CASCADE");
            $table->timestamps();
            $table->primaryKey("id");
        });
    }

    public function down()
    {
        return Schema::drop("playlist_tracks");
    }
};
