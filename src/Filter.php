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

    /**
     * Provides regular expression capabilities for pattern matching strings in queries. MongoDB uses Perl compatible
     * regular expressions (i.e. “PCRE” ) version 8.36 with UTF-8 support.
     *
     * @param string $pattern
     * @param string $option
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/manual/reference/operator/query/regex/
     */
    public function regex($pattern, $option = '')
    {
        $this->operator('$regex', $pattern);
        if (!!$option) {
            $this->operator('$options', $option);
        }

        return $this;
    }

    /**
     * $text performs a text search on the content of the fields indexed with a text index.
     *
     * @param      $search
     * @param null $language
     * @param bool $caseSensitive
     * @param bool $diacriticSensitive
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/manual/reference/operator/query/text/
     */
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

    /**
     * Use the $where operator to pass either a string containing a JavaScript expression or a full JavaScript function
     * to the query system. The $where provides greater flexibility, but requires that the database processes the
     * JavaScript expression or function for each document in the collection.
     *
     * @param $javascript
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/manual/reference/operator/query/where/
     */
    public function where($javascript)
    {
        $this->expressions['$where'] = $javascript;

        return $this;
    }

    /**
     * The $all operator selects the documents where the value of a field is an array that contains all the specified
     * elements.
     *
     * @param array $values
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/manual/reference/operator/query/all/
     */
    public function all(array $values)
    {
        return $this->operator('$all', $values);
    }

    /**
     * The $elemMatch operator matches documents that contain an array field with at least one element that matches all
     * the specified query criteria.
     *
     * @param Filter $filter
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/manual/reference/operator/query/elemMatch/
     */
    public function elemMatch(Filter $filter)
    {
        return $this->operator('$elemMatch', $filter);
    }

    /**
     * The $size operator matches any array with the number of elements specified by the argument.
     *
     * @param $value
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/manual/reference/operator/query/size/
     */
    public function size($value)
    {
        return $this->operator('$size', $value);
    }

    /**
     * $bitsAllSet matches documents where all of the bit positions given by the query are set (i.e. 1) in field.
     *
     * @param $bits
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/manual/reference/operator/query/bitsAllSet/
     */
    public function bitAllSet($bits)
    {
        return $this->operator('$bitAllSet', $bits);
    }

    /**
     * $bitsAnySet matches documents where any of the bit positions given by the query are set (i.e. 1) in field.
     *
     * @param $bits
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/manual/reference/operator/query/bitsAnySet/
     */
    public function bitAnySet($bits)
    {
        return $this->operator('$bitAnySet', $bits);
    }

    /**
     * $bitsAllClear matches documents where all of the bit positions given by the query are clear (i.e. 0) in field.
     *
     * @param $bits
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/manual/reference/operator/query/bitsAllClear/
     */
    public function bitAllClear($bits)
    {
        return $this->operator('$bitAllClear', $bits);
    }

    /**
     * $bitsAnyClear matches documents where any of the bit positions given by the query are clear (i.e. 0) in field.
     *
     * @param $bits
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/manual/reference/operator/query/bitsAnyClear/
     */
    public function bitAnyClear($bits)
    {
        return $this->operator('$bitAnyClear', $bits);
    }

    /**
     * The $comment query operator associates a comment to any expression taking a query predicate.
     *
     * Because comments propagate to the profile log, adding a comment can make your profile data easier to interpret
     * and trace.
     *
     * @param $comment
     *
     * @return $this
     *
     * @see https://docs.mongodb.org/manual/reference/operator/query/comment/
     */
    public function comment($comment)
    {
        $this->expressions['$comment'] = $comment;

        return $this;
    }

    /**
     * Selects documents with geospatial data that exists entirely within a specified shape. When determining
     * inclusion, MongoDB considers the border of a shape to be part of the shape, subject to the precision of floating
     * point numbers.
     *
     * @param       $type
     * @param array $coordinates
     * @param bool  $crs specify a GeoJSON polygons or multipolygons using the default coordinate reference system
     *                   (CRS),
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/manual/reference/operator/query/geoWithin
     */
    public function geoWithin($type, array $coordinates, $crs = false)
    {
        $value = $this->createGeometry($type, $coordinates, $crs);

        return $this->operator('$geoWithin', $value);
    }

    /**
     * Selects documents with geospatial data that exists entirely within a specified shape that defined by legacy
     * coordinate pairs on a plane. When determining inclusion, MongoDB considers the border of a shape to be part of
     * the shape, subject to the precision of floating point numbers.
     *
     * @param $shape
     * @param $coordinates
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/manual/reference/operator/query/geoWithin
     */
    public function geoWithinLegacy($shape, $coordinates)
    {
        return $this->operator('$geoWithin', [$shape => $coordinates]);
    }

    /**
     * Selects documents whose geospatial data intersects with a specified GeoJSON object; i.e. where the intersection
     * of the data and the specified object is non-empty. This includes cases where the data and the specified object
     * share an edge.
     *
     * @param       $type
     * @param array $coordinates
     * @param bool  $crs specify a single-ringed GeoJSON polygon with a custom MongoDB CRS
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/manual/reference/operator/query/geoIntersects/
     */
    public function geoIntersects($type, array $coordinates, $crs = false)
    {
        $value = $this->createGeometry($type, $coordinates, $crs);

        return $this->operator('$geoIntersects', $value);
    }

    /**
     * Specifies a point for which a geospatial query returns the documents from nearest to farthest.
     *
     * @param $long
     * @param $lat
     * @param $max
     * @param $min
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/manual/reference/operator/query/near/
     */
    public function near($long, $lat, $max, $min)
    {
        $value = $this->createNearStmtValue($long, $lat, $max, $min);

        return $this->operator('$near', $value);
    }

    /**
     * Specifies a point (using legacy coordinates) for which a geospatial query returns the documents from nearest to
     * farthest.
     *
     * @param $x
     * @param $y
     * @param $max
     *
     * @return $this
     *
     * @see https://docs.mongodb.org/manual/reference/operator/query/near/
     */
    public function nearLegacy($x, $y, $max)
    {
        $this->operator('$near', [$x, $y]);
        $this->operator('$maxDistance', $max);

        return $this;
    }

    /**
     * Specifies a point for which a geospatial query returns the documents from nearest to farthest. MongoDB
     * calculates distances for $nearSphere using spherical geometry.
     *
     * @param $long
     * @param $lat
     * @param $max
     * @param $min
     *
     * @return Filter
     *
     * @see https://docs.mongodb.org/manual/reference/operator/query/nearSphere/
     */
    public function nearSphere($long, $lat, $max, $min)
    {
        $value = $this->createNearStmtValue($long, $lat, $max, $min);

        return $this->operator('$nearSphere', $value);
    }

    /**
     * Specifies a point (using legacy coordinate) for which a geospatial query returns the documents from nearest to
     * farthest. MongoDB calculates distances for $nearSphere using spherical geometry.
     *
     * @param $x
     * @param $y
     * @param $max
     *
     * @return $this
     *
     * @see https://docs.mongodb.org/manual/reference/operator/query/nearSphere/
     */
    public function nearSphereLegacy($x, $y, $max)
    {
        $this->operator('$nearSphere', [$x, $y]);
        $this->operator('$minDistance', $max);
        $this->operator('$maxDistance', $max);

        return $this;
    }

    /**
     * Throw exception if no current field is selected
     */
    protected function checkFieldSelected()
    {
        if (!$this->currentField) {
            throw new LogicException('A field must be select via [field] method before apply an operator');
        }
    }

    /**
     * Add new operator
     *
     * @param $op
     * @param $val
     *
     * @return $this
     */
    protected function operator($op, $val)
    {
        if ($this->isRootDoc) {
            $this->checkFieldSelected();
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

    /**
     * Add new `and`, `or` or `xor` sub query
     *
     * @param        $operator
     * @param Filter $condition
     *
     * @return $this
     */
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