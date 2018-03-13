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
    public function __construct(Filesystem $files, FilesystemManager $storage)
    {
        $this->files = $files;
        $this->storage = $storage;
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

    public function hasSaved($key)
    {
        return $this->files->exists(
            $this->getFileFor($key)
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

    private function getFileFor($twitter_id)
    {
        if (!$twitter_id) {
            throw new \LogicException('Twitter id is required.');
        }

        return $this->disk()->path("{$twitter_id}.json");
    }


}