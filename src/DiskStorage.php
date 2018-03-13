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
            $this->getFileFor($object->keyValue()),
            json_encode($object->toArray())
        );
    }

    /**
     * Reads object from storage
     *
     * @param $keyValue
     * @return null
     */
    public function read($keyValue)
    {
        if (!$file = $this->hasSaved($keyValue)) {
            return null;
        }

        return json_decode($this->files->get($this->getFileFor($keyValue)));
    }

    public function hasSaved($keyValue)
    {
        return $this->files->exists(
            $this->getFileFor($keyValue)
        );
    }

    private function prepareStorage()
    {
        if (!$this->files->exists($path = $this->disk()->path(''))) {
            $this->files->makeDirectory($path, null, true);
        }
    }

    private function disk()
    {
        return $this->storage->disk('locations');
    }

    private function getFileFor($keyValue)
    {
        if (!$keyValue) {
            throw new \LogicException('A key value is required to find the file used for storing this object on disk.');
        }

        return $this->disk()->path("{$keyValue}.json");
    }


}