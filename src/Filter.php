<?php

namespace HieuLe\MongoODM;

use MongoDB\Driver\Exception\LogicException;

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

    public function __construct($isRoot = true)
    {
        $this->isRootDoc = !!$isRoot;
    }

    public function newFilter()
    {
        return new Filter(false);
    }

    public function field($field)
    {
        $this->currentField = $field;

        return $this;
    }

    public function eq($value)
    {
        return $this->operator('$eq', $value);
    }

    public function ne($value)
    {
        return $this->operator('$ne', $value);
    }

    public function gt($value)
    {
        return $this->operator('$gt', $value);
    }

    public function gte($value)
    {
        return $this->operator('$gte', $value);
    }

    public function lt($value)
    {
        return $this->operator('$lt', $value);
    }

    public function lte($value)
    {
        return $this->operator('$lte', $value);
    }

    public function in(array $values)
    {
        return $this->operator('$in', $values);
    }

    public function nin(array $values)
    {
        return $this->operator('$nin', $values);
    }

    public function not(Filter $filter)
    {

    }

    public function addOr(Filter $filter)
    {
        return $this->addLogicExpr('$or', $filter);
    }

    public function addAnd(Filter $filter)
    {
        return $this->addLogicExpr('$and', $filter);
    }

    public function addNor(Filter $filter)
    {
        return $this->addLogicExpr('$nor', $filter);
    }

    public function exists($value)
    {
        return $this->operator('$exists', $value);
    }

    public function type($value)
    {
        return $this->operator('$type', $value);
    }

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