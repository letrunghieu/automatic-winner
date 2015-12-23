<?php

namespace HieuLe\MongoODM;

use Illuminate\Support\Str;

class CollectionResolver
{
    /**
     * Default database name
     *
     * @var string
     */
    protected $defaultDatabase;

    /**
     * Cache the resolved collections
     *
     * @var array
     */
    protected $resolvedCollections = [];

    /**
     * CollectionResolver constructor.
     *
     * @param string $defaultDatabase
     */
    public function __construct($defaultDatabase)
    {
        $this->defaultDatabase = $defaultDatabase;
    }

    /**
     * Get the collection and database name from a model class name without caching
     *
     * @param $class
     *
     * @return array
     */
    public function resolveClass($class)
    {
        $data       = [];
        $database   = $class::getDatabase();
        $collection = $class::getCollection();

        $data['database'] = $database ?: $this->defaultDatabase;
        if ($collection) {
            $data['collection'] = $collection;
        } else {
            $data['collection'] = str_replace('\\', '', Str::snake(Str::plural(class_basename($class))));
        }

        return $data;
    }

    /**
     * Get the collection and database name from a model class name
     *
     * @param $class
     *
     * @return array
     */
    public function resolve($class)
    {
        if (!isset($this->resolvedCollections[$class])) {
            $this->resolvedCollections[$class] = $this->resolveClass($class);
        }

        return $this->resolvedCollections[$class];
    }

}