<?php

namespace Tequilarapido\DiskStore;

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
     * Creates object from array.
     *
     * @param array $array
     * @return StorableObject
     */
    abstract public static function fromArray(array $array);

    /**
     * Return array from the object.
     * This will be json encoded and saved to disk.
     *
     * @return array
     */
    abstract public function toArray();

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
     * @return null|StorableObject
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
     * @return $this
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
     * Store the object to disk.
     */
    public function store()
    {
        static::diskStorage()->store($this);
    }

    /**
     * Read from disk.
     *
     * @return null|StorableObject
     */
    public function read()
    {
        $raw = static::diskStorage()->read($this->key());

        return $raw ? static::fromArray($raw) : null;
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