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

}