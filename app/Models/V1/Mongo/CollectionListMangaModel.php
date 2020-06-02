<?php

namespace App\Models\V1\Mongo;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

use Config;

class CollectionListMangaModel extends Eloquent
{
    protected $connection = 'mongodb';
    protected $collection = 'contents';
    protected $primarykey = "_id";

    public function __construct() {
        $this->collection = Config::get("general_config.mongo.use_collections_list_manga");
    }
}