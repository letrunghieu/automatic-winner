<?php

namespace HieuLe\MongoODM;

use HieuLe\MongoODM\Operator\AddToSet;
use HieuLe\MongoODM\Operator\Push;
use MongoDB\Exception\InvalidArgumentException;

class UpdateAction
{

    protected $expressions;

    public function field($field, $value)
    {
        $this->expressions[$field] = $value;

        return $this;
    }

    public function inc($field, $value, $firstOnly = false)
    {
        return $this->operator('$inc', $field, $value, $firstOnly);
    }

    public function mul($field, $value, $firstOnly = false)
    {
        return $this->operator('$mul', $field, $value, $firstOnly);
    }

    public function rename($field, $value, $firstOnly = false)
    {
        return $this->operator('$rename', $field, $value, $firstOnly);
    }

    public function setOnInsert($field, $value, $firstOnly = false)
    {
        return $this->operator('$setOnInsert', $field, $value, $firstOnly);
    }

    public function set($field, $value, $firstOnly = false)
    {
        return $this->operator('$set', $field, $value, $firstOnly);
    }

    public function doUnset($field)
    {
        return $this->operator('$unset', $field, '');
    }

    public function min($field, $value, $firstOnly = false)
    {
        return $this->operator('$min', $field, $value, $firstOnly);
    }

    public function max($field, $value, $firstOnly = false)
    {
        return $this->operator('$max', $field, $value, $firstOnly);
    }

    public function currentDate($field, $useTimestamp = false, $firstOnly = false)
    {
        return $this->operator('$currentDate', $field, ['$type' => ($useTimestamp ? 'timestamp' : 'time')], $firstOnly);
    }

    public function addToSet($field, $value, $firstOnly = false)
    {
        if (!$value instanceof AddToSet::class) {
            $value = new AddToSet($value);
        }

        return $this->operator('$addToSet', $field, $value, $firstOnly);
    }

    public function pop($field, $removeFirst = false, $firstOnly = false)
    {
        return $this->operator('$pop', $field, ($removeFirst ? 1 : -1), $firstOnly);
    }

    public function pullAll($field, array $values, $firstOnly = false)
    {
        return $this->operator('$pullALl', $field, $values, $firstOnly);
    }

    public function pull($field, $value, $firstOnly = false)
    {
        return $this->operator('$pull', $field, $value, $firstOnly);
    }

    public function pushALl($field, array $values, $firstOnly = false)
    {
        return $this->operator('$pushAll', $field, $values, $firstOnly);
    }

    public function push($field, $value, $firstOnly = false)
    {
        if (!$value instanceof Push::class) {
            $value = new Push($value);
        }

        return $this->operator('$push', $field, $value, $firstOnly);
    }

    public function bit($field, $operator, $integer)
    {
        if (!in_array($operator, ['and', 'or', 'xor'])) {
            throw new InvalidArgumentException("[operator] must be one of the following values: 'and', 'or', 'xor'");
        }

        return $this->operator('$bit', $field, [$operator => $integer]);
    }

    public function isolated($isolated = true)
    {
        if (!!$isolated) {
            $this->expressions['$isolated'] = 1;
        } else {
            unset($this->expressions['$isolated']);
        }

        return $this;
    }

    protected function operator($op, $field, $value, $firstOnly = false)
    {
        if (!isset($this->expressions[$op])) {
            $this->expressions[$op] = [];
        }

        if ($firstOnly) {
            $this->expressions[$op]["{$field}.$"] = $value;
        } else {
            $this->expressions[$op][$field] = $value;
        }

        return $this;
    }
}