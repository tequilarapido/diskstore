<?php

namespace Tequilarapido\DiskStore;


use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;

class DiskStorage
{
    /** @var disk name that will be used for storing objects */
    protected $diskName;

    /**
     * @var Filesystem
     */
    private $files;

    /**
     * @var FilesystemManager
     */
    private $storage;

    /**
     * DiskStorage constructor.
     *
     * @param Filesystem $files
     * @param FilesystemManager $storage
     */
    public function __construct()
    {
        $this->files = app()->make('files');
        $this->storage = app()->make('filesystem');
    }

    /**
     * Set disk name
     */
    public function setDiskName($diskName)
    {
        $this->diskName = $diskName;

        return $this;
    }

    /**
     * Stores the object to disk
     *
     * @param StorableObject $object
     */
    public function store(StorableObject $object)
    {
        $this->prepareStorage();

        $this->files->put(
            $this->getFileFor($object->key()),
            json_encode($object->toArray())
        );
    }

    /**
     * Reads object from storage
     *
     * @param $key
     * @return null
     */
    public function read($key)
    {
        if (!$file = $this->hasSaved($key)) {
            return null;
        }

        return json_decode($this->files->get($this->getFileFor($key)));
    }

    /**
     * Do we have a stored file for a given key.
     *
     * @param $key
     * @return bool
     */
    public function hasSaved($key)
    {
        return $this->files->exists(
            $this->getFileFor($key)
        );
    }

    /**
     * Prepare disk.
     */
    private function prepareStorage()
    {
        if (!$this->files->exists($path = $this->disk()->path(''))) {
            $this->files->makeDirectory($path, null, true);
        }
    }

    /**
     * Return laravel disk instance for this object.
     *
     * @return Filesystem
     */
    private function disk()
    {
        return $this->storage->disk('locations');
    }

    /**
     * Return file path for a given key.
     *
     * @param $key
     * @return mixed
     */
    private function getFileFor($key)
    {
        if (!$key) {
            throw new \LogicException('A key value is required to find the file used for storing this object on disk.');
        }

        return $this->disk()->path("{$key}.json");
    }
}