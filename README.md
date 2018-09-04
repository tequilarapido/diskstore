
# tequilrapido/diskstore



This packages help with storing data to disk.  This comes handy if persisting data in a database is not the best solution. (ie, Data size etc ...)

Storing entities to disks.


## Install 

```
composer require "tequilarapido/diskstore:0.0.2"
```


## Usage

- define a disk where entities will be stored in `config/filesystem.php`

```php

    'disks' => [
    
        // ...
        
        'tweets' => ['driver' => 'local', 'root' => storage_path('/documents/locations')],
        
        // ...
     ],

```


- define your entity extending `StorableObject`. Every public property will be serialized and saved.


```

class FollowerLocation extends StorableObject
{
    public $twitter_id;

    public $location;


    /** This identifies the entity. And must be unique */    
    public static function keyName()
    {
        return 'twitter_id';
    }

    /** Defines the disk that wil be used for storage **/
    public static function diskName()
    {
        return 'locations';
    }
    
    public function setLocation($location) {
        $this->location = $location;
        
        return $this;
    }
    
    // ...
}
```

- Check if an entity is persisted to disk : 


```php

 if(FollowerLocation::exists($twitter_id)) {
   //...
 }

```

- Save entity :

```php

FollowerLocation::fromArray([
    'twitter_id' => $follower->twitter_id,
    'location' => $location,
])->store();

```

- Work with an empty entity with a specific uid :

```php

FollowerLocation::for($twitter_id)->setLocation($location)->store();

```




- Find an entity by its unique id 

```php
    FollowerLocation::find($follower->twitter_id);
```


- Find an entity by its unique id or create/store on if none

```php
    FollowerLocation::findOrNew($twitter_id)->setLocation($location)->store();
```


- Customize what will be serialized by overriding the `toArray` method
```php

class FollowerLocation extends StorableObject
{
    // ...

    public function toArray() {
       return [
            'twitter_id' => 'This need to be here as its uid of the entity',
            // ... 
       ];
    }
}    

```

- Get stored entity full path file : 


```php
 $path  = FollowerLocation::path()
  
``









