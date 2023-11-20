<?php

namespace Celestial\Models;

use Constellation\Model\Model;

class PlaylistTracks extends Model
{
    public function __construct(?array $id = null)
    {
        parent::__construct("playlist_tracks", ["id"], $id);
    }
}
