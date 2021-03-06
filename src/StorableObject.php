<?php

namespace Tequilarapido\DiskStore;

use ReflectionClass;
use ReflectionProperty;

abstract class StorableObject
{
    /**
     * Defines the key that identify the object to store.
     *
     * This will be the name of the file on disk ( {key}.json )
     */
    abstract static public function keyName();

    /**
     * Defines the laravel disk that will be used for storage.
     */
    abstract static public function diskName();

    /**
     * Create a new instance with given key.
     *
     * @param $key
     * @return static
     */
    public static function for($key)
    {
        $instance = new static();

        $instance->{static::keyName()} = $key;

        return $instance;
    }

    /**
     * Creates object from array.
     *
     * @param array $array
     * @return static
     */
    public static function fromArray(array $array)
    {
        $instance = new static;

        foreach ((new ReflectionClass($instance))->getProperties() as $property) {
            if ($property->isStatic() || !$property->isPublic()) {
                continue;
            }

            $property->setValue($instance, array_get($array, $property->getName()));
        }

        return $instance;
    }

    /**
     * Return array from the object.
     * This will be json encoded and saved to disk.
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];

        foreach ((new ReflectionClass($this))->getProperties() as $property) {
            if ($property->isStatic() || !$property->isPublic()) {
                continue;
            }

            $array[$property->getName()] = $this->{$property->getName()};
        }

        return $array;
    }

    /**
     * Returns key value.
     *
     * @return mixed
     */
    public function key()
    {
        return $this->{static::keyName()};
    }

    /**
     * Find the object on the storage
     *
     * @param $key
     * @return static
     */
    public static function find($key)
    {
        $raw = static::diskStorage()->read($key);

        return $raw ? static::fromArray($raw) : null;
    }

    /**
     * Find the object on the storage.
     * Return an empty instance if not found.
     *
     * @param $key
     *
     * @return static
     */
    public static function findOrNew($key)
    {
        if ($instance = static::find($key)) {
            return $instance;
        }

        $instance = new static;
        $instance->{static::keyName()} = $key;
        return $instance;
    }

    /**
     * Return true if object is stored for a given key
     *
     * @param $key
     * @return bool
     */
    public static function exists($key)
    {
        return !is_null(static::find($key));
    }

    /**
     * Store the object to disk.
     *
     * return $this
     */
    public function store()
    {
        static::diskStorage()->store($this);

        return $this;
    }

    /**
     * Read from disk.
     *
     * @return static
     */
    public function read()
    {
        $raw = static::diskStorage()->read($this->key());

        return $raw ? static::fromArray($raw) : null;
    }

    /**
     * Return file path.
     *
     * @return mixed
     */
    public function path()
    {
        return $this->diskStorage()->getFileFor($this->key());
    }

    /**
     * Return an instance of DiskStorage responsible
     * of handling the object on the disk.
     *
     * @return DiskStorage
     */
    protected static function diskStorage()
    {
        return resolve(DiskStorage::class)->setDiskName(static::diskName());
    }
}
