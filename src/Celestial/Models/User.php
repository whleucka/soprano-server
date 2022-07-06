<?php

namespace Celestial\Models;

use Constellation\Model\Model;

class User extends Model
{
    public function __construct(?array $id = null)
    {
        parent::__construct(
            'users',
            ['id'],
            $id
        );
    }
}
