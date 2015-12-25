<?php

namespace HieuLe\MongoODM\Operator;

class AddToSet extends Operator
{

    protected $usedWithEach = false;

    public function __construct($value)
    {
        parent::__construct('$addToSet', $value);
    }

    public function useWithEach($withEach = true)
    {
        $this->usedWithEach = !!$withEach;

        return $this;
    }

    public function getValue()
    {
        if (!$this->usedWithEach) {
            return $this->value;
        } else {
            return [
                '$each' => $this->value,
            ];
        }
    }
}