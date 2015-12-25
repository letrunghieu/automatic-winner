<?php

namespace HieuLe\MongoODM;

use HieuLe\MongoODM\Contract\Arrayable;
use HieuLe\MongoODM\Operator\ElemMatch;
use HieuLe\MongoODM\Operator\Meta;
use HieuLe\MongoODM\Operator\Slice;

class Projection implements Arrayable
{
    protected $expressions = [];

    /**
     * @return Filter
     */
    public function newFilter()
    {
        return new Filter();
    }

    /**
     * @param $field
     *
     * @return $this
     */
    public function select($field)
    {
        $this->expressions[$field] = 1;

        return $this;
    }

    /**
     * @param $field
     */
    public function ignore($field)
    {
        $this->expressions[$field] = 0;
    }

    /**
     * @param $field
     *
     * @return Projection
     */
    public function selectDollar($field)
    {
        return $this->select("{$field}.$");
    }

    /**
     * @param        $field
     * @param Filter $filter
     *
     * @return $this
     */
    public function selectElemMatch($field, Filter $filter)
    {
        $this->expressions[$field] = new ElemMatch($filter);

        return $this;
    }

    /**
     * @param $field
     *
     * @return $this
     */
    public function selectMeta($field)
    {
        $this->expressions[$field] = new Meta();

        return $this;
    }

    /**
     * @param     $field
     * @param     $limit
     * @param int $skip
     *
     * @return $this
     */
    public function selectSlice($field, $limit, $skip = 0)
    {
        $this->expressions[$field] = new Slice($limit, $skip);

        return $this;
    }

    /**
     *
     */
    public function toArray()
    {

    }
}