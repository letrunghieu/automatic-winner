<?php

namespace HieuLe\MongoODM\Operator;

class Slice extends Operator
{
    public function __construct($limit, $skip = 0)
    {
        if ($skip == 0) {
            $value = $limit;
        } else {
            $value = [$skip, $limit];
        }
        parent::__construct('$slice', $value);
    }
}