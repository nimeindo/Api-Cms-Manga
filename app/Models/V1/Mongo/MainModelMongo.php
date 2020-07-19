<?php

namespace App\Models\V1\Mongo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#Load Component External
use Cache;
use Config;
use Carbon\Carbon;

#Load Collection V1
use App\Models\V1\Mongo\CollectionDetailMangaModel;
use App\Models\V1\Mongo\CollectionLastUpdateChapterModel;
use App\Models\V1\Mongo\CollectionListMangaModel;
use App\Models\V1\Mongo\CollectionGenreListModel;
use App\Models\V1\Mongo\CollectionChapterMangaModel;
use App\Models\V1\Mongo\CollectionTopDetailMangaModel;
use App\Models\V1\Mongo\CollectionRecomendationMangaModel;

class MainModelMongo extends Model
{

    /**
     * @author [Prayugo]
     * @create date 2020-05-31 00:14:08
     * @modify date 2020-05-31 00:14:08
     *  @desc [updateDetailListManga]
     */
    // ====================================== updateDetailListManga ======================================================================
    static function updateDetailListManga($data = [], $collections = NULL, $conditions = [], $upsert = TRUE, $database_name = 'mongodb'){
        if($upsert == TRUE){ #USE UPSERT
            $query = CollectionDetailMangaModel::on($database_name)->raw(function($collection) use ($conditions, $data)
            {
                return $collection->updateOne(
                $conditions,
                ['$set' => $data],
                ['upsert' => true]);
            });

            if($query->isAcknowledged() == TRUE){
                if($query->getModifiedCount() > 0 || $query->getUpsertedCount() > 0){
                    $result['status'] = 200;
                    $result['message'] = 'Berhasil Update';
                    $result['message_local'] = 'Berhasil Update';
                }else{
                    $result['status'] = 200;
                    $result['message'] = 'Gagal Update';
                    $result['message_local'] = 'Match 1, Update 0';
                }
            }else{
                $result['status'] = 400;
                $result['message'] = 'Gagal Update';
                $result['message_local'] = 'Gagal Update';
            }
        }else{
            $query = CollectionDetailMangaModel::on($database_name)->raw()->updateOne(
                $conditions,
                ['$set' => $data],
                ['w' => 'majority']
            );

            if($query->isAcknowledged() == TRUE){
                if($query->getModifiedCount() > 0){
                    $result['status'] = 200;
                    $result['message'] = 'Berhasil Update';
                    $result['message_local'] = 'Berhasil Update';
                }else{
                    $result['status'] = 200;
                    $result['message'] = 'Gagal Update';
                    $result['message_local'] = 'Match 1, Update 0';
                }
            }else{
                $result['status'] = 400;
                $result['message'] = 'Gagal Update';
                $result['message_local'] = 'Gagal Update';
            }
        }
        return $result;
    }
    // ====================================== End updateDetailListManga ======================================================================   

    /**
     * @author [Prayugo]
     * @create date 2020-06-02 00:44:09
     * @modify date 2020-06-02 00:44:09
     * @desc [updateLastUpdateChapterManga]
     */
    // ====================================== updateLastUpdateChapterManga ======================================================================
    static function updateLastUpdateChapterManga($data = [], $collections = NULL, $conditions = [], $upsert = TRUE, $database_name = 'mongodb'){
        if($upsert == TRUE){ #USE UPSERT
            $query = CollectionLastUpdateChapterModel::on($database_name)->raw(function($collection) use ($conditions, $data)
            {
                return $collection->updateOne(
                $conditions,
                ['$set' => $data],
                ['upsert' => true]);
            });

            if($query->isAcknowledged() == TRUE){
                if($query->getModifiedCount() > 0 || $query->getUpsertedCount() > 0){
                    $result['status'] = 200;
                    $result['message'] = 'Berhasil Update';
                    $result['message_local'] = 'Berhasil Update';
                }else{
                    $result['status'] = 200;
                    $result['message'] = 'Gagal Update';
                    $result['message_local'] = 'Match 1, Update 0';
                }
            }else{
                $result['status'] = 400;
                $result['message'] = 'Gagal Update';
                $result['message_local'] = 'Gagal Update';
            }
        }else{
            $query = CollectionLastUpdateModel::on($database_name)->raw()->updateOne(
                $conditions,
                ['$set' => $data],
                ['w' => 'majority']
            );

            if($query->isAcknowledged() == TRUE){
                if($query->getModifiedCount() > 0){
                    $result['status'] = 200;
                    $result['message'] = 'Berhasil Update';
                    $result['message_local'] = 'Berhasil Update';
                }else{
                    $result['status'] = 200;
                    $result['message'] = 'Gagal Update';
                    $result['message_local'] = 'Match 1, Update 0';
                }
            }else{
                $result['status'] = 400;
                $result['message'] = 'Gagal Update';
                $result['message_local'] = 'Gagal Update';
            }
        }
        return $result;
    }
    // ====================================== End updateLastUpdateChapterManga ======================================================================

    /**
     * @author [Prayugo]
     * @create date 2020-06-02 00:44:09
     * @modify date 2020-06-02 00:44:09
     * @desc [updateListManga]
     */
    // ====================================== updateListAnime ======================================================================
    static function updateListManga($data = [], $collections = NULL, $conditions = [], $upsert = TRUE, $database_name = 'mongodb'){
        if($upsert == TRUE){ #USE UPSERT
            $query = CollectionListMangaModel::on($database_name)->raw(function($collection) use ($conditions, $data)
            {
                return $collection->updateOne(
                $conditions,
                ['$set' => $data],
                ['upsert' => true]);
            });

            if($query->isAcknowledged() == TRUE){
                if($query->getModifiedCount() > 0 || $query->getUpsertedCount() > 0){
                    $result['status'] = 200;
                    $result['message'] = 'Berhasil Update';
                    $result['message_local'] = 'Berhasil Update';
                }else{
                    $result['status'] = 200;
                    $result['message'] = 'Gagal Update';
                    $result['message_local'] = 'Match 1, Update 0';
                }
            }else{
                $result['status'] = 400;
                $result['message'] = 'Gagal Update';
                $result['message_local'] = 'Gagal Update';
            }
        }else{
            $query = CollectionListMangaModel::on($database_name)->raw()->updateOne(
                $conditions,
                ['$set' => $data],
                ['w' => 'majority']
            );

            if($query->isAcknowledged() == TRUE){
                if($query->getModifiedCount() > 0){
                    $result['status'] = 200;
                    $result['message'] = 'Berhasil Update';
                    $result['message_local'] = 'Berhasil Update';
                }else{
                    $result['status'] = 200;
                    $result['message'] = 'Gagal Update';
                    $result['message_local'] = 'Match 1, Update 0';
                }
            }else{
                $result['status'] = 400;
                $result['message'] = 'Gagal Update';
                $result['message_local'] = 'Gagal Update';
            }
        }
        return $result;
    }
    // ====================================== End updateListAnime ======================================================================

    /**
     * @author [Prayugo]
     * @create date 2020-06-02 00:44:09
     * @modify date 2020-06-02 00:44:09
     * @desc [updateGenreListManga]
     */
    // ====================================== updateGenreListManga ======================================================================
    static function updateGenreListManga($data = [], $collections = NULL, $conditions = [], $upsert = TRUE, $database_name = 'mongodb'){
        if($upsert == TRUE){ #USE UPSERT
            $query = CollectionGenreListModel::on($database_name)->raw(function($collection) use ($conditions, $data)
            {
                return $collection->updateOne(
                $conditions,
                ['$set' => $data],
                ['upsert' => true]);
            });

            if($query->isAcknowledged() == TRUE){
                if($query->getModifiedCount() > 0 || $query->getUpsertedCount() > 0){
                    $result['status'] = 200;
                    $result['message'] = 'Berhasil Update';
                    $result['message_local'] = 'Berhasil Update';
                }else{
                    $result['status'] = 200;
                    $result['message'] = 'Gagal Update';
                    $result['message_local'] = 'Match 1, Update 0';
                }
            }else{
                $result['status'] = 400;
                $result['message'] = 'Gagal Update';
                $result['message_local'] = 'Gagal Update';
            }
        }else{
            $query = CollectionGenreListModel::on($database_name)->raw()->updateOne(
                $conditions,
                ['$set' => $data],
                ['w' => 'majority']
            );

            if($query->isAcknowledged() == TRUE){
                if($query->getModifiedCount() > 0){
                    $result['status'] = 200;
                    $result['message'] = 'Berhasil Update';
                    $result['message_local'] = 'Berhasil Update';
                }else{
                    $result['status'] = 200;
                    $result['message'] = 'Gagal Update';
                    $result['message_local'] = 'Match 1, Update 0';
                }
            }else{
                $result['status'] = 400;
                $result['message'] = 'Gagal Update';
                $result['message_local'] = 'Gagal Update';
            }
        }
        return $result;
    }
    // ====================================== End updateTrendingWeekAnime ======================================================================

    /**
     * @author [Prayugo]
     * @create date 2020-06-02 00:44:09
     * @modify date 2020-06-02 00:44:09
     * @desc [updateChapterManga]
     */
    // ====================================== updateChapterManga ======================================================================
    static function updateChapterManga($data = [], $collections = NULL, $conditions = [], $upsert = TRUE, $database_name = 'mongodb'){
        if($upsert == TRUE){ #USE UPSERT
            $query = CollectionChapterMangaModel::on($database_name)->raw(function($collection) use ($conditions, $data)
            {
                return $collection->updateOne(
                $conditions,
                ['$set' => $data],
                ['upsert' => true]);
            });

            if($query->isAcknowledged() == TRUE){
                if($query->getModifiedCount() > 0 || $query->getUpsertedCount() > 0){
                    $result['status'] = 200;
                    $result['message'] = 'Berhasil Update';
                    $result['message_local'] = 'Berhasil Update';
                }else{
                    $result['status'] = 200;
                    $result['message'] = 'Gagal Update';
                    $result['message_local'] = 'Match 1, Update 0';
                }
            }else{
                $result['status'] = 400;
                $result['message'] = 'Gagal Update';
                $result['message_local'] = 'Gagal Update';
            }
        }else{
            $query = CollectionChapterMangaModel::on($database_name)->raw()->updateOne(
                $conditions,
                ['$set' => $data],
                ['w' => 'majority']
            );

            if($query->isAcknowledged() == TRUE){
                if($query->getModifiedCount() > 0){
                    $result['status'] = 200;
                    $result['message'] = 'Berhasil Update';
                    $result['message_local'] = 'Berhasil Update';
                }else{
                    $result['status'] = 200;
                    $result['message'] = 'Gagal Update';
                    $result['message_local'] = 'Match 1, Update 0';
                }
            }else{
                $result['status'] = 400;
                $result['message'] = 'Gagal Update';
                $result['message_local'] = 'Gagal Update';
            }
        }
        return $result;
    }
    // ====================================== End updateChapterManga ======================================================================

    /**
     * @author [Prayugo]
     * @create date 2020-07-04 12:01:06
     * @modify date 2020-07-04 12:01:06
     * @desc [updateTopDetailListManga]
     */
    // ====================================== updateTopDetailListManga ======================================================================
    static function updateTopDetailListManga($data = [], $collections = NULL, $conditions = [], $upsert = TRUE, $database_name = 'mongodb'){
        if($upsert == TRUE){ #USE UPSERT
            $query = CollectionTopDetailMangaModel::on($database_name)->raw(function($collection) use ($conditions, $data)
            {
                return $collection->updateOne(
                $conditions,
                ['$set' => $data],
                ['upsert' => true]);
            });

            if($query->isAcknowledged() == TRUE){
                if($query->getModifiedCount() > 0 || $query->getUpsertedCount() > 0){
                    $result['status'] = 200;
                    $result['message'] = 'Berhasil Update';
                    $result['message_local'] = 'Berhasil Update';
                }else{
                    $result['status'] = 200;
                    $result['message'] = 'Gagal Update';
                    $result['message_local'] = 'Match 1, Update 0';
                }
            }else{
                $result['status'] = 400;
                $result['message'] = 'Gagal Update';
                $result['message_local'] = 'Gagal Update';
            }
        }else{
            $query = CollectionTopDetailMangaModel::on($database_name)->raw()->updateOne(
                $conditions,
                ['$set' => $data],
                ['w' => 'majority']
            );

            if($query->isAcknowledged() == TRUE){
                if($query->getModifiedCount() > 0){
                    $result['status'] = 200;
                    $result['message'] = 'Berhasil Update';
                    $result['message_local'] = 'Berhasil Update';
                }else{
                    $result['status'] = 200;
                    $result['message'] = 'Gagal Update';
                    $result['message_local'] = 'Match 1, Update 0';
                }
            }else{
                $result['status'] = 400;
                $result['message'] = 'Gagal Update';
                $result['message_local'] = 'Gagal Update';
            }
        }
        return $result;
    }
    // ====================================== End updateTopDetailListManga ======================================================================

    /**
     * @author [Prayugo]
     * @create date 2020-07-04 12:01:06
     * @modify date 2020-07-04 12:01:06
     * @desc [deleteData]
     */
    // ====================================== deleteData ======================================================================
    static function deleteData($collections = NULL, $conditions = [], $database_name = 'mongodb'){
        $query = DB::connection($database_name)
            ->collection($collections);

        foreach($conditions as $key => $value){ #jika di ditemukan datanya maka update, jika tidak insert (upsert)
            $query = $query->where($key, $value);
        }

        $query = $query->delete();

        $data['status'] = 400;
        $data['message'] = 'Gagal Delete';
        if($query){
            $data['status'] = 200;
            $data['message'] = 'Berhasil Delete';
        }

        return $data;
    }
    // ====================================== End deleteData ======================================================================

    /**
     * @author [Prayugo]
     * @create date 2020-07-04 12:01:06
     * @modify date 2020-07-04 12:01:06
     * @desc [getDataTopDetailListManga]
     */
    #================ getDataTopDetailListManga ==================================
    static function getDataTopDetailListManga($params = [],$database_name = 'mongodb'){

        $timeout = Config::get('general_config.mongo.query_timeout');

        $slug = (isset($params['slug']) ? $params['slug'] : '');
        $idListManga = (isset($params['id_list_manga']) ? $params['id_list_manga'] : NULL);
        $idDetailManga = (isset($params['id_detail_manga']) ? $params['id_detail_manga'] : NULL);
        $code = (isset($params['code']) ? $params['code'] : '');
        
        $query = CollectionTopDetailMangaModel::on($database_name)
            ->timeout($timeout);
        if(!empty($slug)) $query = $query->where('slug', '=', $slug);
        if(!empty($idListManga)) $query = $query->where('id_list_manga', '=', (int)$idListManga);
        if(!empty($idDetailManga)) $query = $query->where('id_detail_manga', '=', (int)$idDetailManga);
        if(!empty($code)) $query = $query->where('code', '=', $code);
        
        $collection = '';
        $data['collection_count'] = 0;
        if(!empty($query)){
            $collection = $query->get();
            $data['collection_count'] = 1;
        }
        $data['collection'] = $collection;

        return $data;
    }
    #================ End getDataTopDetailListAnime ==================================

    /**
     * @author [Prayugo]
     * @create date 2020-07-06 17:30:37
     * @modify date 2020-07-06 17:30:37
     * @desc [updateRecomendationManga]
     */
    // ====================================== updateTopDetailListManga ======================================================================
    static function updateRecomendationManga($data = [], $collections = NULL, $conditions = [], $upsert = TRUE, $database_name = 'mongodb'){
        if($upsert == TRUE){ #USE UPSERT
            $query = CollectionRecomendationMangaModel::on($database_name)->raw(function($collection) use ($conditions, $data)
            {
                return $collection->updateOne(
                $conditions,
                ['$set' => $data],
                ['upsert' => true]);
            });

            if($query->isAcknowledged() == TRUE){
                if($query->getModifiedCount() > 0 || $query->getUpsertedCount() > 0){
                    $result['status'] = 200;
                    $result['message'] = 'Berhasil Update';
                    $result['message_local'] = 'Berhasil Update';
                }else{
                    $result['status'] = 200;
                    $result['message'] = 'Gagal Update';
                    $result['message_local'] = 'Match 1, Update 0';
                }
            }else{
                $result['status'] = 400;
                $result['message'] = 'Gagal Update';
                $result['message_local'] = 'Gagal Update';
            }
        }else{
            $query = CollectionRecomendationMangaModel::on($database_name)->raw()->updateOne(
                $conditions,
                ['$set' => $data],
                ['w' => 'majority']
            );

            if($query->isAcknowledged() == TRUE){
                if($query->getModifiedCount() > 0){
                    $result['status'] = 200;
                    $result['message'] = 'Berhasil Update';
                    $result['message_local'] = 'Berhasil Update';
                }else{
                    $result['status'] = 200;
                    $result['message'] = 'Gagal Update';
                    $result['message_local'] = 'Match 1, Update 0';
                }
            }else{
                $result['status'] = 400;
                $result['message'] = 'Gagal Update';
                $result['message_local'] = 'Gagal Update';
            }
        }
        return $result;
    }
    // ====================================== End updateTopDetailListManga ======================================================================
    
    /**
     * @author [Prayugo]
     * @create date 2020-07-06 17:30:37
     * @modify date 2020-07-06 17:30:37
     * @desc [getDataRecomendationManga]
     */
    #================ getDataRecomendationManga ==================================
    static function getDataRecomendationManga($params = [],$database_name = 'mongodb'){

        $timeout = Config::get('general_config.mongo.query_timeout');

        $slug = (isset($params['slug']) ? $params['slug'] : '');
        $idListManga = (isset($params['id_list_manga']) ? $params['id_list_manga'] : NULL);
        $idDetailManga = (isset($params['id_detail_manga']) ? $params['id_detail_manga'] : NULL);
        $code = (isset($params['code']) ? $params['code'] : '');
        
        $query = CollectionRecomendationMangaModel::on($database_name)
            ->timeout($timeout);
        if(!empty($slug)) $query = $query->where('slug', '=', $slug);
        if(!empty($idListManga)) $query = $query->where('id_list_manga', '=', (int)$idListManga);
        if(!empty($idDetailManga)) $query = $query->where('id_detail_manga', '=', (int)$idDetailManga);
        if(!empty($code)) $query = $query->where('code', '=', $code);
        
        $collection = '';
        $data['collection_count'] = 0;
        if(!empty($query)){
            $collection = $query->get();
            $data['collection_count'] = 1;
        }
        $data['collection'] = $collection;

        return $data;
    }
    #================ End getDataRecomendationManga ==================================
}