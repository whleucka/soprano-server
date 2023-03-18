<?php

namespace Celestial\Models;

use Constellation\Model\Model;

class Blog extends Model
{
    public function __construct(?array $id = null)
    {
        parent::__construct("blog", ["id"], $id);
    }
}
