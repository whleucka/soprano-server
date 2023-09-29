<?php

namespace Celestial\Models;

use Constellation\Model\Model;

class Audit extends Model
{
    public function __construct(?array $id = null)
    {
        parent::__construct("audit", ["id"], $id);
    }
}
