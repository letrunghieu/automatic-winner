<?php

namespace HieuLe\MongoODM;

use Illuminate\Support\Str;
use MongoDB\BSON\Persistable;

/**
 * Class Model
 *
 * @package HieuLe\MongoODM
 */
abstract class Model implements Persistable
{
    protected static $collection = '';

    protected static $database = '';

    protected $document;

    /**
     * Model constructor.
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $this->document = new \stdClass();
        foreach ($attributes as $key => $value) {
            $this->document->{$key} = $value;
        }
    }

    /**
     * @return string
     */
    public static function getCollection()
    {
        return static::$collection;
    }

    /**
     * @return string
     */
    public static function getDatabase()
    {
        return static::$database;
    }

    /**
     * Provides an array or document to serialize as BSON
     * Called during serialization of the object to BSON. The method must return an array or stdClass.
     * Root documents (e.g. a MongoDB\BSON\Serializable passed to MongoDB\BSON\fromPHP()) will always be serialized as
     * a BSON document. For field values, associative arrays and stdClass instances will be serialized as a BSON
     * document and sequential arrays (i.e. sequential, numeric indexes starting at 0) will be serialized as a BSON
     * array.
     * @link http://php.net/manual/en/mongodb-bson-serializable.bsonserialize.php
     * @return array|object An array or stdClass to be serialized as a BSON array or document.
     */
    public function bsonSerialize()
    {
        return $this->document;
    }

    /**
     * Constructs the object from a BSON array or document
     * Called during unserialization of the object from BSON.
     * The properties of the BSON array or document will be passed to the method as an array.
     * @link http://php.net/manual/en/mongodb-bson-unserializable.bsonunserialize.php
     *
     * @param array $data Properties within the BSON array or document.
     */
    public function bsonUnserialize(array $data)
    {
        $this->document = $data;
    }

    /**
     * Get the collection associated with the model.
     *
     * @return string
     */
    public function getCollectionName()
    {
        if (static::$collection) {
            return static::$collection;
        }

        return str_replace('\\', '', Str::snake(Str::plural(class_basename($this))));
    }

    /**
     * Get the database associated with the model
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return static::$database;
    }

    /**
     * Get the internal document
     *
     * @return mixed
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Get the attribute value
     *
     * @param $name
     *
     * @return mixed
     */
    function __get($name)
    {
        return $this->document->{$name};
    }

    /**
     * Set the attribute value
     *
     * @param $name
     * @param $value
     */
    function __set($name, $value)
    {
        $this->document->{$name} = $value;
    }

}