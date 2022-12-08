<?php

namespace Celestial\Models;

use Constellation\Model\Model;

class Customer extends Model
{
    public function __construct(?array $id = null)
    {
        parent::__construct("customers", ["id"], $id);
    }
}
