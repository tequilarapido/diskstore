<?php

namespace Tequilarapido\DiskStore;

abstract class StorableObject
{
    /**
     * Defines the key that identify the object to store.
     *
     * This will be the name of the file on disk ( {key}.json )
     */
    abstract static public function key();

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
     * Find the object on the storage
     *
     * @param $keyValue
     * @return null|StorableObject
     */
    public static function find($keyValue)
    {
        $raw = static::diskStorage()->read($keyValue);

        return $raw ? static::fromArray($raw) : null;
    }

    /**
     * Find the object on the storage.
     * Return an empty instance if not found.
     *
     * @param $keyValue
     *
     * @return $this
     */
    public static function findOrNew($keyValue)
    {
        if ($instance = static::find($keyValue)) {
            return $instance;
        }

        $instance = new static;
        $instance->{static::key()} = $keyValue;
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
        $raw = static::diskStorage()->read($this->keyValue());

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

    /**
     * Returns key value.
     *
     * @return mixed
     */
    protected function keyValue()
    {
        return $this->{static::key()};
    }
}