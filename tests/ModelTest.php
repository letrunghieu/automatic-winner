<?php

namespace HieuLe\MongoODMTest;

use Carbon\Carbon;
use HieuLe\MongoDateTime\CO2;
use HieuLe\MongoODM\DocumentManager;
use HieuLe\MongoODMTest\Stuff\ModelA;
use MongoDB\BSON\ObjectID;
use MongoDB\Client;

class ModelTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var DocumentManager
     */
    protected $dm;

    public function setUp()
    {
        parent::setUp();

        $client   = new Client('mongodb://localhost:27017');
        $this->dm = new DocumentManager($client, 'demo_db');
    }

    public function testInsertNewDocument()
    {
        $doc = new ModelA();

        $this->dm->persist($doc);

        $this->assertInstanceOf(ObjectID::class, $doc->_id);
    }

    public function testInsertDateTime()
    {
        $co2 = new CO2(Carbon::now());
        $doc = new ModelA(['datetime' => $co2]);

        $this->dm->persist($doc);

        $rec = $this->dm->findOne(ModelA::class, ['_id' => $doc->_id]);
        $this->assertSame($co2->getCarbon()->getTimestamp(), $rec->datetime->getCarbon()->getTimestamp());
        $this->assertSame($co2->getCarbon()->getTimezone()->getName(),
            $rec->datetime->getCarbon()->getTimezone()->getName());
    }
}