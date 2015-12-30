<?php

namespace HieuLe\MongoODMTest;

use HieuLe\MongoODM\Filter;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Filter
     */
    protected $filter;

    public function setUp()
    {
        parent::setUp();

        $this->filter = new Filter();
    }

    public function testDefaultConstructor()
    {
        $this->assertTrue($this->filter->isRoot());
    }

    public function testSubQueryConstructor()
    {
        $this->assertFalse($this->filter->newFilter()->isRoot());
    }

    /**
     * @expectedException \MongoDB\Driver\Exception\LogicException
     */
    public function testExceptionThrownWhenNoFieldSelected()
    {
        $this->filter->eq(1);
    }

    public function testNoExceptionWhenNoFieldSelectedInSubQuery()
    {
        $this->filter->newFilter()->eq(1);
    }

    public function testEq()
    {
        $query = [
            'qty'       => [
                '$eq' => 20,
            ],
            'item.name' => [
                '$eq' => 'ab',
            ],
        ];
        $this->filter->field('qty')->eq(20)
            ->field('item.name')->eq('ab');

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testNe()
    {
        $query = [
            'qty'           => [
                '$ne' => 20,
            ],
            'carrier.state' => [
                '$ne' => 'NY',
            ],
        ];
        $this->filter->field('qty')->ne(20)
            ->field('carrier.state')->ne('NY');

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testGt()
    {
        $query = [
            'qty'         => [
                '$gt' => 20,
            ],
            'carrier.fee' => [
                '$gt' => 2,
            ],
        ];
        $this->filter->field('qty')->gt(20)
            ->field('carrier.fee')->gt(2);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testGte()
    {
        $query = [
            'qty' => [
                '$gte' => 20,
            ],
        ];
        $this->filter->field('qty')->gte(20);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testLt()
    {
        $query = [
            'carrier.fee' => [
                '$lt' => 20,
            ],
        ];
        $this->filter->field('carrier.fee')->lt(20);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testLte()
    {
        $query = [
            'carrier.fee' => [
                '$lte' => 5,
            ],
        ];
        $this->filter->field('carrier.fee')->lte(5);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testIn()
    {
        $query = [
            'tags' => [
                '$in' => ['appliances', 'school'],
            ],
        ];
        $this->filter->field('tags')->in(['appliances', 'school']);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testNin()
    {
        $query = [
            'qty' => [
                '$nin' => [5, 15],
            ],
        ];
        $this->filter->field('qty')->nin([5, 15]);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testOr()
    {
        $query = [
            '$or' => [
                [
                    'quantity' => [
                        '$lt' => 20,
                    ],
                ],
                [
                    'price' => [
                        '$eq' => 10,
                    ],
                ],
            ],
        ];
        $this->filter->addOr($this->filter->newFilter()->field('quantity')->lt(20))
            ->addOr($this->filter->newFilter()->field('price')->eq(10));

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testAnd()
    {
        $query = [
            '$and' => [
                [
                    '$or' => [
                        [
                            'price' => [
                                '$eq' => 0.99,
                            ],
                        ],
                        [
                            'price' => [
                                '$eq' => 1.99,
                            ],
                        ],
                    ],
                ],
                [
                    '$or' => [
                        [
                            'sale' => [
                                '$eq' => true,
                            ],
                        ],
                        [
                            'qty' => [
                                '$lt' => 20,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $firstOrQuery  = $this->filter->newFilter()->addOr($this->filter->newFilter()->field('price')->eq(0.99))
            ->addOr($this->filter->newFilter()->field('price')->eq(1.99));
        $secondOrQuery = $this->filter->newFilter()->addOr($this->filter->newFilter()->field('sale')->eq(true))
            ->addOr($this->filter->newFilter()->field('qty')->lt(20));
        $this->filter->addAnd($firstOrQuery)
            ->addAnd($secondOrQuery);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testNot()
    {
        $query = [
            'price' => [
                '$not' => [
                    '$gt' => 1.99,
                ],
            ],
        ];
        $this->filter->field('price')->not($this->filter->newFilter()->gt(1.99));

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testNotRegex()
    {
        $query = [
            'price' => [
                '$not' => '/^p.*/',
            ],
        ];
        $this->filter->field('price')->notRegex('/^p.*/');

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testNor()
    {
        $query = [
            '$nor' => [
                [
                    'price' => [
                        '$eq' => 1.99,
                    ],
                ],
                [
                    'qty' => [
                        '$lt' => 20,
                    ],
                ],
                [
                    'sale' => [
                        '$eq' => true,
                    ],
                ],
            ],
        ];
        $this->filter->addNor($this->filter->newFilter()->field('price')->eq(1.99))
            ->addNor($this->filter->newFilter()->field('qty')->lt(20))
            ->addNor($this->filter->newFilter()->field('sale')->eq(true));

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testExists()
    {
        $query = [
            'qty' => [
                '$exists' => true,
                '$nin'    => [5, 15],
            ],
        ];
        $this->filter->field('qty')->exists(true)
            ->nin([5, 15]);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testType()
    {
        $query = [
            'qty' => [
                '$exists' => true,
                '$type'   => 1,
            ],
        ];
        $this->filter->field('qty')->exists(true)
            ->type(1);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testMod()
    {
        $query = [
            'qty' => [
                '$mod' => [4, 0],
            ],
        ];
        $this->filter->field('qty')->mod(4, 0);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testRegex()
    {
        $query = [
            'name' => [
                '$regex'   => '/acme.*corp/',
                '$options' => 'si',
            ],
        ];
        $this->filter->field('name')->regex('/acme.*corp/', 'si');

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testText()
    {
        $query = [
            '$text' => [
                '$search'             => "leche",
                '$language'           => 'es',
                '$caseSensitive'      => true,
                '$diacriticSensitive' => true,
            ],
        ];
        $this->filter->text('leche', 'es', true, true);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testWhere()
    {
        $query = [
            '$where' => "this.credits == this.debits",
        ];
        $this->filter->where("this.credits == this.debits");

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testGeoWithin()
    {
        $query = [
            'loc' => [
                '$geoWithin' => [
                    '$geometry' => [
                        'type'        => 'Polygon',
                        'coordinates' => [
                            [
                                [-100, 60],
                                [-100, 0],
                                [-100, -60],
                                [100, -60],
                                [100, 60],
                                [-100, 60],
                            ],
                        ],
                        'crs'         => [
                            'type'       => 'name',
                            'properties' => [
                                'name' => 'urn:x-mongodb:crs:strictwinding:EPSG:4326',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->filter->field('loc')->geoWithin(Filter::TYPE_POLYGON, [
            [
                [-100, 60],
                [-100, 0],
                [-100, -60],
                [100, -60],
                [100, 60],
                [-100, 60],
            ],
        ], true);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testGeoWithinLegacy()
    {
        $query = [
            'loc' => [
                '$geoWithin' => [
                    '$box' => [[0, 0], [100, 100]],
                ],
            ],
        ];

        $this->filter->field('loc')->geoWithinLegacy(Filter::SHAPE_BOX, [[0, 0], [100, 100]]);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testGeoIntersects()
    {
        $query = [
            'loc' => [
                '$geoIntersects' => [
                    '$geometry' => [
                        'type'        => 'Polygon',
                        'coordinates' => [
                            [
                                [-100, 60],
                                [-100, 0],
                                [-100, -60],
                                [100, -60],
                                [100, 60],
                                [-100, 60],
                            ],
                        ],
                        'crs'         => [
                            'type'       => 'name',
                            'properties' => [
                                'name' => 'urn:x-mongodb:crs:strictwinding:EPSG:4326',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->filter->field('loc')->geoIntersects(Filter::TYPE_POLYGON, [
            [
                [-100, 60],
                [-100, 0],
                [-100, -60],
                [100, -60],
                [100, 60],
                [-100, 60],
            ],
        ], true);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testNear()
    {
        $query = [
            'location' => [
                '$near' => [
                    '$geometry'    => [
                        'type'        => 'Point',
                        'coordinates' => [-73.9667, 40.78],
                    ],
                    '$minDistance' => 1000,
                    '$maxDistance' => 5000,
                ],
            ],
        ];
        $this->filter->field('location')->near(-73.9667, 40.78, 5000, 1000);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testNearLegacy()
    {
        $query = [
            'location' => [
                '$near'        => [-73.9667, 40.78],
                '$maxDistance' => 0.10,
            ],
        ];

        $this->filter->field('location')->nearLegacy(-73.9667, 40.78, 0.10);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testNearSphere()
    {
        $query = [
            'location' => [
                '$nearSphere' => [
                    '$geometry'    => [
                        'type'        => 'Point',
                        'coordinates' => [-73.9667, 40.78],
                    ],
                    '$minDistance' => 1000,
                    '$maxDistance' => 5000,
                ],
            ],
        ];
        $this->filter->field('location')->nearSphere(-73.9667, 40.78, 5000, 1000);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testNearSphereLegacy()
    {
        $query = [
            'location' => [
                '$nearSphere'  => [-73.9667, 40.78],
                '$maxDistance' => 0.10,
                '$minDistance' => 0,
            ],
        ];

        $this->filter->field('location')->nearSphereLegacy(-73.9667, 40.78, 0.10, 0);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testAll()
    {
        $query = [
            'tags' => [
                '$all' => ["appliance", "school", "book"],
            ],
        ];

        $this->filter->field('tags')->all(["appliance", "school", "book"]);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testElemMatch()
    {
        $query = [
            'results' => [
                '$elemMatch' => [
                    'product' => [
                        '$eq' => 'xyz',
                    ],
                    'score'   => [
                        '$gte' => 8,
                    ],
                ],
            ],
        ];

        $this->filter->field('results')->elemMatch($this->filter->newFilter()->field('product')->eq('xyz')->field('score')->gte(8));

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testSize()
    {
        $query = [
            'field' => [
                '$size' => 2,
            ],
        ];

        $this->filter->field('field')->size(2);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testBitsAllSet()
    {
        $query = [
            'a' => [
                '$bitsAllSet' => [1, 5],
            ],
        ];

        $this->filter->field('a')->bitsAllSet([1, 5]);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testBitsAnySet()
    {
        $query = [
            'a' => [
                '$bitsAnySet' => [1, 5],
            ],
        ];

        $this->filter->field('a')->bitsAnySet([1, 5]);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testBitsAllClear()
    {
        $query = [
            'a' => [
                '$bitsAllClear' => 35,
            ],
        ];

        $this->filter->field('a')->bitsAllClear(35);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testBitsAnyClear()
    {
        $query = [
            'a' => [
                '$bitsAnyClear' => 35,
            ],
        ];

        $this->filter->field('a')->bitsAnyClear(35);

        $this->assertEquals($query, $this->filter->toArray());
    }

    public function testComment()
    {
        $query = [
            '$comment' => 'Bar',
        ];
        $this->filter->comment('Foo');
        $this->filter->comment('Bar');

        $this->assertEquals($query, $this->filter->toArray());
    }

}
