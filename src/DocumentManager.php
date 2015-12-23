<?php

namespace HieuLe\MongoODM;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Driver\Exception\RuntimeException;

/**
 * Class DocumentManager
 *
 * @package HieuLe\MongoODM
 */
class DocumentManager
{
    /**
     * @var Client
     */
    protected $client;

    protected $database;

    /**
     * @var CollectionResolver
     */
    protected $resolver;

    /**
     * The collection of each model class
     *
     * @var array
     */
    protected $collections = [];

    /**
     * DocumentManager constructor.
     *
     * @param Client $client
     * @param        $database
     */
    public function __construct(Client $client, $database)
    {
        $this->client   = $client;
        $this->database = $database;
        $this->resolver = new CollectionResolver($database);
    }

    public function persist(Model $model)
    {
        $collection = $this->getCollection(get_class($model));

        $result = $collection->insertOne($model->getDocument());

        if (!$result->isAcknowledged()) {
            throw new RuntimeException('The insertion is not acknowledged');
        }

        $model->_id = $result->getInsertedId();

        return $model;
    }

    protected function insertMany(Collection $collection, $models) {
        $result = $collection->insertMany($models);
        $result->getInsertedIds();
    }

    public function findOne($class, $filter = [], $options = [])
    {
        $collection = $this->getCollection($class);

        $result = $collection->findOne($filter, $options);

        if (!$result) {
            return $result;
        }

        return new $class($result);
    }

    /**
     * Get the collection associated to a model class
     *
     * @param string $class
     *
     * @return Collection
     */
    public function getCollection($class)
    {
        if (!isset($this->collections[$class])) {
            $info = $this->resolver->resolve($class);
            $this->collections[$class] = $this->client->selectCollection($info['database'], $info['collection']);
        }

        return $this->collections[$class];
    }

}