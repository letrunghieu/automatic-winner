<?php

namespace HieuLe\MongoODM;

use MongoDB\Driver\Exception\LogicException;

/**
 * Queries specify criteria, or conditions, that identify the documents that MongoDB returns to the clients
 *
 * @see     https://docs.mongodb.org/manual/core/read-operations-introduction/
 *
 * @package HieuLe\MongoODM
 */
class Filter
{

    const SHAPE_BOX = '$box';
    const SHAPE_POLYGON = '$polygon';
    const SHAPE_CENTER = '$center';
    const SHAPE_CENTER_SPHERE = '$centerSphere';

    const TYPE_POLYGON = 'Polygon';
    const TYPE_MULTI_POLYGON = 'MultiPolygon';

    const CRS_NAME = 'urn:x-mongodb:crs:strictwinding:EPSG:4326';

    protected $expressions;

    protected $currentField;

    protected $isRootDoc;

    /**
     * Filter constructor.
     *
     * @param bool $isRoot this query document is the root document or the embedded one
     */
    public function __construct($isRoot = true)
    {
        $this->isRootDoc = !!$isRoot;
    }

    /**
     * Create new sub query document
     *
     * @return Filter
     */
    public function newFilter()
    {
        return new Filter(false);
    }

    /**
     * Set the current field for next operator
     *
     * @param $field
     *
     * @return Filter
     */
    public function field($field)
    {
        $this->currentField = $field;

        return $this;
    }

    /**
     * Specifies equality condition. The $eq operator matches documents where the value of a field equals the specified
     * value.
     *
     * @param $value
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/v3.0/reference/operator/query/eq/
     */
    public function eq($value)
    {
        return $this->operator('$eq', $value);
    }

    /**
     * $ne selects the documents where the value of the field is not equal (i.e. !=) to the specified value. This
     * includes documents that do not contain the field.
     *
     * @param $value
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/v3.0/reference/operator/query/ne/
     */
    public function ne($value)
    {
        return $this->operator('$ne', $value);
    }

    /**
     * $gt selects those documents where the value of the field is greater than (i.e. >) the specified value.
     *
     * @param $value
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/v3.0/reference/operator/query/gt/
     */
    public function gt($value)
    {
        return $this->operator('$gt', $value);
    }

    /**
     * $gte selects the documents where the value of the field is greater than or equal to (i.e. >=) a specified value
     * (e.g. value.)
     *
     * @param $value
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/v3.0/reference/operator/query/gte/
     */
    public function gte($value)
    {
        return $this->operator('$gte', $value);
    }

    /**
     * $lt selects the documents where the value of the field is less than (i.e. <) the specified value.
     *
     * @param $value
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/v3.0/reference/operator/query/lt/
     */
    public function lt($value)
    {
        return $this->operator('$lt', $value);
    }

    /**
     * $lte selects the documents where the value of the field is less than or equal to (i.e. <=) the specified value.
     *
     * @param $value
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/v3.0/reference/operator/query/lte/
     */
    public function lte($value)
    {
        return $this->operator('$lte', $value);
    }

    /**
     * The $in operator selects the documents where the value of a field equals any value in the specified array.
     *
     * @param array $values
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/v3.0/reference/operator/query/in/
     */
    public function in(array $values)
    {
        return $this->operator('$in', $values);
    }

    /**
     * $nin selects the documents where:
     *
     * * the field value is not in the specified array or
     * * the field does not exist.
     *
     * @param array $values
     *
     * @return Filter
     * @see https://docs.mongodb.org/v3.0/reference/operator/query/nin/
     */
    public function nin(array $values)
    {
        return $this->operator('$nin', $values);
    }

    /**
     * $not performs a logical NOT operation on the specified <operator-expression> and selects the documents that do
     * not match the <operator-expression>. This includes documents that do not contain the field.
     *
     * @param Filter $filter
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/v3.0/reference/operator/query/not/
     */
    public function not(Filter $filter)
    {
        return $this->operator('$not', $filter);
    }

    /**
     * Using the $not operator with the regular expression
     *
     * @param $regexOption
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/v3.0/reference/operator/query/not/
     */
    public function notRegex($regexOption)
    {
        return $this->operator('$not', $regexOption);
    }

    /**
     * The $or operator performs a logical OR operation on an array of two or more <expressions> and selects the
     * documents that satisfy at least one of the <expressions>.
     *
     * @param Filter $filter
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/v3.0/reference/operator/query/or/
     */
    public function addOr(Filter $filter)
    {
        return $this->addLogicExpr('$or', $filter);
    }

    /**
     * $and performs a logical AND operation on an array of two or more expressions (e.g. <expression1>, <expression2>,
     * etc.) and selects the documents that satisfy all the expressions in the array. The $and operator uses
     * short-circuit evaluation. If the first expression (e.g. <expression1>) evaluates to false, MongoDB will not
     * evaluate the remaining expressions.
     *
     * @param Filter $filter
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/v3.0/reference/operator/query/and/
     */
    public function addAnd(Filter $filter)
    {
        return $this->addLogicExpr('$and', $filter);
    }

    /**
     * $nor performs a logical NOR operation on an array of one or more query expression and selects the documents that
     * fail all the query expressions in the array. The $nor has the following syntax:
     *
     * @param Filter $filter
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/v3.0/reference/operator/query/nor/
     */
    public function addNor(Filter $filter)
    {
        return $this->addLogicExpr('$nor', $filter);
    }

    /**
     * When <boolean> is true, $exists matches the documents that contain the field, including documents where the
     * field value is null. If <boolean> is false, the query returns only the documents that do not contain the field.
     *
     * @param $value
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/v3.0/reference/operator/query/exists/
     */
    public function exists($value)
    {
        return $this->operator('$exists', !!$value);
    }

    /**
     * $type selects the documents where the value of the field is an instance of the specified numeric BSON type. This
     * is useful when dealing with highly unstructured data where data types are not predictable.
     *
     * @param $value
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/v3.0/reference/operator/query/type/
     */
    public function type($value)
    {
        return $this->operator('$type', $value);
    }

    /**
     * Select documents where the value of a field divided by a divisor has the specified remainder (i.e. perform a
     * modulo operation to select documents).
     *
     * @param $division
     * @param $remainder
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/v3.0/reference/operator/query/mod/
     */
    public function mod($division, $remainder)
    {
        return $this->operator('$mod', [$division, $remainder]);
    }

    public function regex($pattern, $option = '')
    {
        $this->operator('$regex', $pattern);
        if (!!$option) {
            $this->operator('$options', $option);
        }

        return $this;
    }

    public function text($search, $language = null, $caseSensitive = false, $diacriticSensitive = false)
    {
        $this->expressions['$text'] = ['$search' => $search];
        if (!!$language) {
            $this->expressions['$text']['$language'] = $language;
        }
        if ($caseSensitive) {
            $this->expressions['$caseSensitive'] = true;
        }
        if ($diacriticSensitive) {
            $this->expressions['$diacriticSensitive'] = true;
        }

        return $this;
    }

    public function where($javascript)
    {
        $this->expressions['$where'] = $javascript;

        return $this;
    }

    public function all(array $values)
    {
        return $this->operator('$all', $values);
    }

    public function elemMatch(Filter $filter)
    {
        return $this->operator('$elemMatch', $filter);
    }

    public function size($value)
    {
        return $this->operator('$size', $value);
    }

    public function bitAllSet($bits)
    {
        return $this->operator('$bitAllSet', $bits);
    }

    public function bitAnySet($bits)
    {
        return $this->operator('$bitAnySet', $bits);
    }

    public function bitAllClear($bits)
    {
        return $this->operator('$bitAllClear', $bits);
    }

    public function bitAnyClear($bits)
    {
        return $this->operator('$bitAnyClear', $bits);
    }

    public function comment($comment)
    {
        $this->expressions['$comment'] = $comment;

        return $this;
    }

    public function geoWithin($type, array $coordinates, $crs = false)
    {
        $value = $this->createGeometry($type, $coordinates, $crs);

        return $this->operator('$geoWithin', $value);
    }

    public function geoWithinLegacy($shape, $coordinates)
    {
        return $this->operator('$geoWithin', [$shape => $coordinates]);
    }

    public function geoIntersects($type, array $coordinates, $crs = false)
    {
        $value = $this->createGeometry($type, $coordinates, $crs);

        return $this->operator('$geoIntersects', $value);
    }

    public function near($long, $lat, $max, $min)
    {
        $value = $this->createNearStmtValue($long, $lat, $max, $min);

        return $this->operator('$near', $value);
    }

    public function nearLegacy($x, $y, $max)
    {
        $this->operator('$near', [$x, $y]);
        $this->operator('$maxDistance', $max);

        return $this;
    }

    public function nearSphere($long, $lat, $max, $min)
    {
        $value = $this->createNearStmtValue($long, $lat, $max, $min);

        return $this->operator('$nearSphere', $value);
    }

    public function nearSphereLegacy($x, $y, $max)
    {
        $this->operator('$nearSphere', [$x, $y]);
        $this->operator('$minDistance', $max);
        $this->operator('$maxDistance', $max);

        return $this;
    }

    protected function checkFieldSelected()
    {
        if (!$this->currentField) {
            throw new LogicException('A field must be select via [field] method before apply an operator');
        }
    }

    protected function operator($op, $val)
    {
        if ($this->isRootDoc && !$this->expressions) {
            throw new LogicException(
                'In the root document, a field must be selected via [field] before apply and operator'
            );
        }
        if (!$this->currentField) {
            $this->expressions[$op] = $val;
        } else {
            if (!isset($this->expressions[$this->currentField])) {
                $this->expressions[$this->currentField] = [];
            }

            $this->expressions[$this->currentField][$op] = $val;
        }

        return $this;
    }

    protected function addLogicExpr($operator, Filter $condition)
    {
        if (!isset($this->expressions[$operator])) {
            $this->expressions[$operator] = [];
        }

        $this->expressions[$operator][] = $condition;

        return $this;
    }

    protected function createGeometry($type, array $coordinates, $crs = false)
    {
        $value = [
            '$geometry' => [
                'type'        => $type,
                'coordinates' => $coordinates,
            ],
        ];

        if ($crs) {
            $value['$geometry']['crs'] = [
                'type'       => 'name',
                'properties' => [
                    'name' => static::CRS_NAME,
                ],
            ];
        }

        return $value;
    }

    protected function createNearStmtValue($long, $lat, $max, $min)
    {
        $value = [
            '$geometry'    => [
                'type'        => 'Point',
                'coordinates' => [$long, $lat],
            ],
            '$maxDistance' => $max,
            '$minDistance' => $min,
        ];

        return $value;
    }
}