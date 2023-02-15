<?php

namespace Celestial\Models;

use Constellation\Model\Model;

class Radio extends Model
{
    public function __construct(?array $id = null)
    {
        parent::__construct("radio", ["id"], $id);
    }
}
