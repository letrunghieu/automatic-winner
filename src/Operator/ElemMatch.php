<?php
/**
 * Created by PhpStorm.
 * User: Codeforce
 * Date: 12/25/2015
 * Time: 10:20 AM
 */

namespace HieuLe\MongoODM\Operator;

use HieuLe\MongoODM\Filter;

class ElemMatch extends Operator
{

    /**
     * ElemMatch constructor.
     *
     * @param Filter $filter
     */
    public function __construct(Filter $filter)
    {
        parent::__construct('$elemMatch', $filter);
    }
}