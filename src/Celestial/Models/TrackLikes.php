<?php

namespace Celestial\Models;

use Constellation\Model\Model;

class TrackLikes extends Model
{
    public function __construct(?array $id = null)
    {
        parent::__construct("track_likes", ["customer_id", "track_id"], $id);
    }
}
