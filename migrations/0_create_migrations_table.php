<?php
namespace Celestial\Migrations;

use Constellation\Database\Blueprint;
use Constellation\Database\Migration;
use Constellation\Database\Schema;

return new class extends Migration {
    public function up()
    {
        return Schema::create("migrations", function (Blueprint $table) {
            $table->id();
            $table->char("migration_hash", 32);
            $table->timestamp("created_at");
            $table->primaryKey("id");
        });
    }

    public function down()
    {
        return Schema::drop("migrations");
    }
};
