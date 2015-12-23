<?php

namespace HieuLe\MongoODM;

use MongoDB\Driver\Exception\InvalidArgumentException;

class Query
{
    protected $filters;

    protected $projection;

    protected $sorts = [];

    protected $cursorLimit = 0;

    protected $cursorSkip = 0;

    protected $updateActions;

    /**
     * Sort by a field
     *
     * @param string $field the field name
     * @param string $order 'asc' to sort ascending or 'desc' to sort descending
     *
     * @return $this
     */
    public function sort($field, $order = 'asc')
    {
        if (!in_array($order, ['asc', 'desc'])) {
            throw new InvalidArgumentException("[order] must be 'asc' or 'desc'");
        }
        $this->sorts[$field] = $order === 'asc' ? 1 : -1;

        return $this;
    }

    /**
     * @param int $limit the number of documents to be returned
     *
     * @return $this
     */
    public function limit($limit)
    {
        $this->cursorLimit = $limit;

        return $this;
    }

    /**
     * @param int $number the number of documents to skip before returning
     *
     * @return $this
     */
    public function skip($number)
    {
        $this->cursorSkip = $number;

        return $this;
    }

    /**
     * Specify the fields to return using booleans or projection operators
     *
     * @param Projection $projection
     *
     * @return $this
     */
    public function project(Projection $projection)
    {
        $this->projection = $projection;

        return $this;
    }

    /**
     * The search filter
     *
     * @param Filter $filters
     *
     * @return $this
     */
    public function filter(Filter $filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * A document containing either update operators (e.g. $set) or a replacement document (i.e. only field:value
     * expressions).
     *
     * @param UpdateAction $actions
     *
     * @return $this
     */
    public function update(UpdateAction $actions)
    {
        $this->updateActions = $actions;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return mixed
     */
    public function getProjection()
    {
        return $this->projection;
    }

    /**
     * @return mixed
     */
    public function getUpdateActions()
    {
        return $this->updateActions;
    }

}