<?php

namespace HieuLe\MongoODM\Operator;

class Push extends Operator
{

    protected $usedWithEach = false;

    protected $slice = false;

    protected $position = false;

    protected $sorts = false;

    public function __construct($value)
    {
        parent::__construct('$push', $value);
    }

    public function useWithEach($withEach = true)
    {
        $this->usedWithEach = !!$withEach;

        return $this;
    }

    public function useSlice($number)
    {
        if (is_int($number)) {
            $this->slice = false;
        }

        $this->slice = $number;

        return $this;
    }

    public function sortBy($direction, $field = '')
    {
        if ($direction == false) {
            $this->sorts = false;

            return $this;
        }
        if (!in_array('asc', 'desc')) {
            throw new InvalidArgumentException("[direction] must be 'asc' or 'desc'");
        }
        $dir = $direction === 'asc' ? 1 : -1;

        if (!$field) {
            $this->sorts = $dir;
        } else {
            if (!is_array($this->sorts)) {
                $this->sorts = [];
            }

            $this->sorts[$field] = $dir;
        }

        return $this;
    }

    public function usePosition($position)
    {
        $this->position = $position;

        return $this;
    }

    public function getValue()
    {
        if (!$this->usedWithEach) {
            return $this->value;
        }

        $value = [
            '$each' => $this->value,
        ];
        if ($this->slice !== false) {
            $value['$slice'] = $this->slice;
        }

        if ($this->sort !== false) {
            $value['$sort'] = $this->sorts;
        }

        if ($this->position !== false) {
            $value['$position'] = $this->position;
        }

        return $value;
    }
}