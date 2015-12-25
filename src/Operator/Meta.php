<?php

namespace HieuLe\MongoODM\Operator;

class Meta extends Operator
{
    /**
     * Meta constructor.
     */
    public function __construct()
    {
        parent::__construct('$meta', 'textScore');
    }
}