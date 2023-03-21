<?php

namespace Celestial\Models;

use Constellation\Model\Model;

class Playlist extends Model
{
    public function __construct(?array $id = null)
    {
        parent::__construct("playlists", ["id"], $id);
    }
}
