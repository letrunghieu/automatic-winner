<?php

namespace HieuLe\MongoODM;

class UpdateAction
{
    protected $expressions;

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

    public function currentDate($field, $useTimestamp = false)
    {
        return $this->operator('$currentDate', $field, ['$type' => ($useTimestamp ? 'timestamp' : 'time')]);
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