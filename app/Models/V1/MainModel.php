<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#Load Component External
use Cache;
use Config;
use Carbon\Carbon;

#Load Helpers V1

#Load Collection V1

class MainModel extends Model
{

    /**
     * @author [Prayugo]
     * @create date 2020-05-06 02:50:48
     * @modify date 2020-05-06 02:50:48
     * @desc [getUser]
     */
    #================ getUser ==================================
    static function getUser($ApiKey){
        ini_set('memory_limit','1024M');
        $query = DB::connection('mysql')
            ->table('User')
            ->where('token', $ApiKey);
        $query = $query->get();

        $result = [];
        if(count($query)) $result = collect($query)->map(function($x){ return (array) $x; })->toArray();
        return $result;
    }
    #================ End getUser ==================================

    /**
     * @author [Prayugo]
     * @create date 2020-05-06 02:50:48
     * @modify date 2020-05-06 02:50:48
     * @desc [insertListMangaMysql]
     */
    #================  insertListMangaMysql ==================================
    public static function insertListMangaMysql($data_all = [], $justInsert = FALSE){
        $tabel_name = 'list_manga';
        $query = DB::connection('mysql')
            ->table($tabel_name);
        if($justInsert){
            $query = $query->insert($data_all);
        }else{
            $query = $query->insertGetId($data_all);
        }
        $error = [];
        $data['status'] = 200;
        $data['message'] = 'success insert '.$tabel_name;
        if(!$query) {
            $data['status'] = 400;
            $data['message'] = 'failed insert '.$tabel_name;
            $error['msg'] = 'error insert '.$tabel_name;
            $error['num'] = 'error num insert '.$tabel_name;
        }

        $data['error'] 	= $error;
        if(!$justInsert) $data['id_result'] = $query;
        return $data;
    }
    #================ End insertListMangaMysql ==================================

    /**
     * @author [Prayugo]
     * @create date 2020-05-06 02:50:48
     * @modify date 2020-05-06 02:50:48
     * @desc [updateListMangaMysql]
     */
    #================  updateListMangaMysql ==================================
    public static function updateListMangaMysql($data_all = [], $conditions){
        $tabel_name = 'list_manga';
        $query = DB::connection('mysql')
            ->table($tabel_name);

        foreach($conditions as $key => $value){
            $query = $query->where($key, $value);
        }
        $query = $query->update($data_all);
        $data['status'] = 400;
        $data['message'] = 'failed insert '.$tabel_name;
        if($query){
            $data['status'] = 200;
            $data['message'] = 'success insert '.$tabel_name;
        }
        return $data;
    }
    #================ End updateListMangaMysql ==================================

    /**
     * @author [Prayugo]
     * @create date 2020-05-06 02:50:48
     * @modify date 2020-05-06 02:50:48
     * @desc [getDataListmanga]
     */
    #================  getDataListManga ==================================
    static function getDataListManga($params = []){
        $code = (isset($params['code']) ? $params['code'] : '');
        $id = (isset($params['id']) ? $params['id'] : '');
        $slug = (isset($params['slug']) ? $params['slug'] : '');
        $title = (isset($params['title']) ? $params['title'] : '');
        $startByIndex = (isset($params['start_by_index']) ? $params['start_by_index'] : '');
        $EndByIndex = (isset($params['end_by_index']) ? $params['end_by_index'] : '');
        $startDate = (isset($params['start_date']) ? $params['start_date'] : '');
        $endDate = (isset($params['end_date']) ? $params['end_date'] : '');
        $isUpdated = (isset($params['is_updated']) ? $params['is_updated'] : FALSE); #untuk data terbaru 2 jam terakhir
        if($isUpdated){ #ambil data update atau terbaru
            $startDate = date('Y-m-d');
            $endDate = date("Y-m-d", strtotime('tomorrow'));
        }

        $tabel_name = 'list_manga';
        ini_set('memory_limit','1024M');
        $query = DB::connection('mysql')
            ->table($tabel_name)
            ->orderBy('id', 'asc');
        if(!empty($code)) $query = $query->where('code', '=', $code);
        if(!empty($id)) $query = $query->where('id', '=', $id);
        if(!empty($slug)) $query = $query->where('slug', '=', $slug);
        if(!empty($title)) $title = $query->where('title', 'Like', "%".$title.'%');
        if(!empty($startByIndex)) $query = $query->where('name_index', 'Like', "%".$startByIndex);
        if(!empty($EndByIndex)){
            $alphas = range($startByIndex, $EndByIndex);
            for($i = 0;$i<count($alphas); $i++){
                $query = $query->orWhere('name_index', 'Like', "%".$alphas[$i]);
            }
        }
        if(!empty($startDate) && empty($endDate)) $query = $query->where('cron_at', '>=', $startDate);
        if($startDate && $endDate) $query = $query->whereBetween('cron_at', [$startDate, $endDate]);

        $query = $query->get();

        $result = [];
        if(count($query)) $result = collect($query)->map(function($x){ return (array) $x; })->toArray();
        return $result;

    }
    #================ End getDataListManga ==================================

    /**
     * @author [Prayugo]
     * @create date 2020-05-10 07:18:27
     * @modify date 2020-05-10 07:18:27
     * @desc [getDataDetailManga]
     */
    #================  getDataDetailManga ==================================
    public static function getDataDetailManga($params = []){
        $ID = (isset($params['id']) ? $params['id'] : '');
        $idListmanga = (isset($params['id_list_manga']) ? $params['id_list_manga'] : '');
        $code = (isset($params['code']) ? $params['code'] : '');
        $Title = (isset($params['title']) ? $params['title'] : '');
        $Slug = (isset($params['slug']) ? $params['slug'] : '');
        $startDate = (isset($params['start_date']) ? $params['start_date'] : '');
        $endDate = (isset($params['end_date']) ? $params['end_date'] : '');
        $isUpdated = (isset($params['is_updated']) ? $params['is_updated'] : FALSE); #untuk data terbaru 2 jam terakhir

        $tabel_name = 'detail_manga';
        ini_set('memory_limit','1024M');
        $query = DB::connection('mysql')
            ->table($tabel_name);

        if(!empty($ID)) $query = $query->where('id', '=', $ID);
        if(!empty($idListmanga)) $query = $query->where('id_list_manga', '=', $idListmanga);
        if(!empty($Slug)) $query = $query->where('slug', '=', $Slug);
        if(!empty($code)) $query = $query->where('code', '=', $code);
        if(!empty($Title)) $query = $query->where('title', 'Like', "%".$Title);

        if($isUpdated){ #ambil data update atau terbaru
            $startDate = date('Y-m-d');
            $endDate = date("Y-m-d", strtotime('tomorrow'));
            $query = $query->whereBetween('cron_at', [$startDate, $endDate]);
        }else{
            if(!empty($startDate) && empty($endDate)) $query = $query->where('cron_at', '>=', $startDate);
            if(!empty($startDate) && !empty($endDate)) $query = $query->whereBetween('cron_at', [$startDate, $endDate]);
        }
        $query = $query->get();

        $result = [];
        if(count($query)) $result = collect($query)->map(function($x){ return (array) $x; })->toArray();
        return $result;
    }
    #================ End getDataDetailManga ==================================


    /**
     * @author [Prayugo]
     * @create date 2020-05-10 07:18:27
     * @modify date 2020-05-10 07:18:27
     * @desc [insertDetailMangaMysql]
     */
    #================  insertDetailMangaMysql ==================================
    public static function insertDetailMangaMysql($data_all = [], $justInsert = FALSE){
        $tabel_name = 'detail_manga';
        $query = DB::connection('mysql')
            ->table($tabel_name);
        if($justInsert){
            $query = $query->insert($data_all);
        }else{
            $query = $query->insertGetId($data_all);
        }
        $error = [];
        $data['status'] = 200;
        $data['message'] = 'success insert '.$tabel_name;
        if(!$query) {
            $data['status'] = 400;
            $data['message'] = 'failed insert '.$tabel_name;
            $error['msg'] = 'error insert '.$tabel_name;
            $error['num'] = 'error num insert '.$tabel_name;
        }

        $data['error'] 	= $error;
        if(!$justInsert) $data['id_result'] = $query;
        return $data;
    }
    #================ End insertDetailMangaMysql ==================================

    /**
     * @author [Prayugo]
     * @create date 2020-05-10 07:18:27
     * @modify date 2020-05-10 07:18:27
     * @desc [updateDetailMangaMysql]
     */
    #================  updateDetailMangaMysql ==================================
    public static function updateDetailMangaMysql($data_all = [], $conditions){
        $tabel_name = 'detail_manga';
        $query = DB::connection('mysql')
            ->table($tabel_name);

        foreach($conditions as $key => $value){
            $query = $query->where($key, $value);
        }
        $query = $query->update($data_all);
        $data['status'] = 400;
        $data['message'] = 'failed insert '.$tabel_name;
        if($query){
            $data['status'] = 200;
            $data['message'] = 'success insert '.$tabel_name;
        }
        return $data;
    }
    #================ End updateDetailMangaMysql ==================================


    /**
     * @author [Prayugo]
     * @create date 2020-05-10 07:18:27
     * @modify date 2020-05-10 07:18:27
     * @desc [getDataChapterManga]
     */
    #================  getDataChapterManga ==================================
    public static function getDataChapterManga($params = []){
        $code = (isset($params['code']) ? $params['code'] : '');
        $slug = (isset($params['slug']) ? $params['slug'] : '');
        $idChapter = (isset($params['id_chapter']) ? $params['id_chapter'] : '');
        $idDetailManganga = (isset($params['id_detail_manga']) ? $params['id_detail_manga'] : '');
        $idListManga = (isset($params['id_list_manga']) ? $params['id_list_manga'] : '');
        $startDate = (isset($params['start_date']) ? $params['start_date'] : '');
        $endDate = (isset($params['end_date']) ? $params['end_date'] : '');

        $tabel_name = 'chapter';
        ini_set('memory_limit','1024M');
        $query = DB::connection('mysql')
            ->table($tabel_name)
            ->orderBy('slug', 'desc');
        if(!empty($code)) $query = $query->where('code', '=', $code);
        if(!empty($idChapter)) $query = $query->where('id', '=', $idChapter);
        if(!empty($slug)) $query = $query->where('slug', '=', $slug);
        if(!empty($idDetailManganga)) $query = $query->where('id_detail_manga', '=', $idDetailManganga);
        if(!empty($idListManga)) $query = $query->where('id_list_manga', '=', $idListManga);

        if(!empty($startDate) && empty($endDate)) $query = $query->where('cron_at', '>=', $startDate);
        if($startDate && $endDate) $query = $query->whereBetween('cron_at', [$startDate, $endDate]);
        $query = $query->get();

        $result = [];
        if(count($query)) $result = collect($query)->map(function($x){ return (array) $x; })->toArray();
        return $result;
    }
    #================ End getDataChapterManga ==================================


    /**
     * @author [Prayugo]
     * @create date 2020-05-10 07:18:27
     * @modify date 2020-05-10 07:18:27
     * @desc [insertChapterMangaMysql]
     */
    #================  insertChapterMangaMysql ==================================
    public static function insertChapterMangaMysql($data_all = [], $justInsert = FALSE){
        $tabel_name = 'chapter';
        $query = DB::connection('mysql')
            ->table($tabel_name);
        if($justInsert){
            $query = $query->insert($data_all);
        }else{
            $query = $query->insertGetId($data_all);
        }
        $error = [];
        $data['status'] = 200;
        $data['message'] = 'success insert '.$tabel_name;
        if(!$query) {
            $data['status'] = 400;
            $data['message'] = 'failed insert '.$tabel_name;
            $error['msg'] = 'error insert '.$tabel_name;
            $error['num'] = 'error num insert '.$tabel_name;
        }

        $data['error'] 	= $error;
        if(!$justInsert) $data['id_result'] = $query;
        return $data;
    }
    #================ End insertChapterMangaMysql ==================================

    /**
     * @author [Prayugo]
     * @create date 2020-05-10 07:18:27
     * @modify date 2020-05-10 07:18:27
     * @desc [updateChapterMangaMysql]
     */
    #================  updateChapterMangaMysql ==================================
    public static function updateChapterMangaMysql($data_all = [], $conditions){
        $tabel_name = 'chapter';
        $query = DB::connection('mysql')
            ->table($tabel_name);

        foreach($conditions as $key => $value){
            $query = $query->where($key, $value);
        }
        $query = $query->update($data_all);
        $data['status'] = 400;
        $data['message'] = 'failed insert '.$tabel_name;
        if($query){
            $data['status'] = 200;
            $data['message'] = 'success insert '.$tabel_name;
        }
        return $data;
    }
    #================ End updateChapterMangaMysql ==================================

    /**
     * @author [prayugo]
     * @create date 2020-05-11 06:26:46
     * @modify date 2020-05-11 06:26:46
     * @desc [getDataImageChapterManga]
     */
    #================  getDataImageChapterManga ==================================
    public static function getDataImageChapterManga($params = []){
        $code = (isset($params['code']) ? $params['code'] : '');
        $slug = (isset($params['slug']) ? $params['slug'] : '');
        $id = (isset($params['id']) ? $params['id'] : '');
        $id_chapter = (isset($params['id_chapter']) ? $params['id_chapter'] : '');
        $startDate = (isset($params['start_date']) ? $params['start_date'] : '');
        $endDate = (isset($params['end_date']) ? $params['end_date'] : '');

        $tabel_name = 'image_chapter';
        ini_set('memory_limit','1024M');
        $query = DB::connection('mysql')
            ->table($tabel_name)
            ->orderBy('no_frame','desc')
            ->groupby('no_frame')
            ->distinct();

        if(!empty($code)) $query = $query->where('code', '=', $code);
        if(!empty($slug)) $query = $query->where('slug', '=', $slug);
        if(!empty($id)) $query = $query->where('id', '=', $id);
        if(!empty($id_chapter)) $query = $query->where('id_chapter', '=', $id_chapter);

        if(!empty($startDate) && empty($endDate)) $query = $query->where('cron_at', '>=', $startDate);
        if($startDate && $endDate) $query = $query->whereBetween('cron_at', [$startDate, $endDate]);
        $query = $query->get();

        $result = [];
        if(count($query)) $result = collect($query)->map(function($x){ return (array) $x; })->toArray();
        return $result;
    }
    #================ End getDataImageChapterManga ==================================


    /**
     * @author [prayugo]
     * @create date 2020-05-11 06:26:46
     * @modify date 2020-05-11 06:26:46
     * @desc [insertImageChapterMangaMysql]
     */
    #================  insertImageChapterMangaMysql ==================================
    public static function insertImageChapterMangaMysql($data_all = [], $justInsert = FALSE){
        $tabel_name = 'image_chapter';
        $query = DB::connection('mysql')
            ->table($tabel_name);
        if($justInsert){
            $query = $query->insert($data_all);
        }else{
            $query = $query->insertGetId($data_all);
        }
        $error = [];
        $data['status'] = 200;
        $data['message'] = 'success insert '.$tabel_name;
        if(!$query) {
            $data['status'] = 400;
            $data['message'] = 'failed insert '.$tabel_name;
            $error['msg'] = 'error insert '.$tabel_name;
            $error['num'] = 'error num insert '.$tabel_name;
        }

        $data['error'] 	= $error;
        if(!$justInsert) $data['id_result'] = $query;
        return $data;
    }
    #================ End insertImageChapterMangaMysql ==================================

    /**
     * @author [prayugo]
     * @create date 2020-05-11 06:26:46
     * @modify date 2020-05-11 06:26:46
     * @desc [updateImageChapterMangaMysql]
     */
    #================  updateImageChapterMangaMysql ==================================
    public static function updateImageChapterMangaMysql($data_all = [], $conditions){
        $tabel_name = 'image_chapter';
        $query = DB::connection('mysql')
            ->table($tabel_name);

        foreach($conditions as $key => $value){
            $query = $query->where($key, $value);
        }
        $query = $query->update($data_all);
        $data['status'] = 400;
        $data['message'] = 'failed insert '.$tabel_name;
        if($query){
            $data['status'] = 200;
            $data['message'] = 'success insert '.$tabel_name;
        }
        return $data;
    }
    #================ End updateImageChapterMangaMysql ==================================

    /**
     * @author [prayugo]
     * @create date 2020-05-11 06:26:46
     * @modify date 2020-05-11 06:26:46
     * @desc [getDataLastUpdateChapterManga]
     */
    #================  getDataLastUpdateChapterManga ==================================
    public static function getDataLastUpdateChapterManga($params = []){
        $code = (isset($params['code']) ? $params['code'] : '');
        $id = (isset($params['id']) ? $params['id'] : '');
        $id_chapter = (isset($params['id_chapter']) ? $params['id_chapter'] : '');
        $idDetail = (isset($params['id_detail']) ? $params['id_detail'] : '');
        $startDate = (isset($params['start_date']) ? $params['start_date'] : '');
        $endDate = (isset($params['end_date']) ? $params['end_date'] : '');
        $isUpdated = (isset($params['is_updated']) ? $params['is_updated'] : false);

        if($isUpdated){
            $startDate = date('Y-m-d');
            $endDate = date("Y-m-d", strtotime('tomorrow'));
        }
        $tabel_name = 'last_update_chapter';
        ini_set('memory_limit','1024M');
        $query = DB::connection('mysql')
            ->table($tabel_name);

        if(!empty($code)) $query = $query->where('code', '=', $code);
        if(!empty($id)) $query = $query->where('id', '=', $id);
        if(!empty($id_chapter)) $query = $query->where('id_chapter', '=', $id_chapter);
        if(!empty($idDetail)) $query = $query->where('id_detail_manga', '=', $idDetail);

        if(!empty($startDate) && empty($endDate)) $query = $query->where('cron_at', '>=', $startDate);
        if($startDate && $endDate) $query = $query->whereBetween('cron_at', [$startDate, $endDate]);
        $query = $query->get();

        $result = [];
        if(count($query)) $result = collect($query)->map(function($x){ return (array) $x; })->toArray();
        return $result;
    }
    #================ End getDataLastUpdateChapterManga ==================================


    /**
     * @author [prayugo]
     * @create date 2020-05-11 06:26:46
     * @modify date 2020-05-11 06:26:46
     * @desc [insertLastUpdateChapterMangaMysql]
     */
    #================  insertLastUpdateChapterMangaMysql ==================================
    public static function insertLastUpdateChapterMangaMysql($data_all = [], $justInsert = FALSE){
        $tabel_name = 'last_update_chapter';
        $query = DB::connection('mysql')
            ->table($tabel_name);
        if($justInsert){
            $query = $query->insert($data_all);
        }else{
            $query = $query->insertGetId($data_all);
        }
        $error = [];
        $data['status'] = 200;
        $data['message'] = 'success insert '.$tabel_name;
        if(!$query) {
            $data['status'] = 400;
            $data['message'] = 'failed insert '.$tabel_name;
            $error['msg'] = 'error insert '.$tabel_name;
            $error['num'] = 'error num insert '.$tabel_name;
        }

        $data['error'] 	= $error;
        if(!$justInsert) $data['id_result'] = $query;
        return $data;
    }
    #================ End insertLastUpdateChapterMangaMysql ==================================

    /**
     * @author [prayugo]
     * @create date 2020-05-11 06:26:46
     * @modify date 2020-05-11 06:26:46
     * @desc [updateLastUpdateChapterMangaMysql]
     */
    #================  updateLastUpdateChapterMangaMysql ==================================
    public static function updateLastUpdateChapterMangaMysql($data_all = [], $conditions){
        $tabel_name = 'last_update_chapter';
        $query = DB::connection('mysql')
            ->table($tabel_name);

        foreach($conditions as $key => $value){
            $query = $query->where($key, $value);
        }
        $query = $query->update($data_all);
        $data['status'] = 400;
        $data['message'] = 'failed insert '.$tabel_name;
        if($query){
            $data['status'] = 200;
            $data['message'] = 'success insert '.$tabel_name;
        }
        return $data;
    }
    #================ End updateLastUpdateChapterMangaMysql ==================================

    /**
     * @author [Prayugo]
     * @create date 2019-12-09 01:28:51
     * @modify date 2019-12-09 01:28:51
     * @desc function checkExistImageChapter
     */
    #================  checkExistImageChapter ==================================
    public static function checkExistImageChapter($params = []){

        ini_set('memory_limit','20480M');
        $query2 = DB::connection('mysql')
        ->table('chapter')
        ->select('chapter.id')
        ->join('image_chapter', 'chapter.id', '=', 'image_chapter.id_chapter')
        ->distinct();
        $query = DB::connection('mysql')
        ->table('chapter as CH')
        ->select([
            'CH.id'
        ])
        ->whereNotIn('CH.id',$query2);
        $query = $query->get();

        $result = [];
        if(count($query)) $result = collect($query)->map(function($x){ return (array) $x; })->toArray();
        return $result;
    }
    #================ End checkExistImageChapter ==================================

    /**
     * @author [Prayugo]
     * @create date 2019-12-09 01:28:51
     * @modify date 2019-12-09 01:28:51
     * @desc function checkIdChapterOnImageChapter
     */
    #================  checkIdChapterOnImageChapter ==================================
    public static function checkIdChapterOnImageChapter($params = []){

        ini_set('memory_limit','20480M');
        $query = DB::connection('mysql')
        ->table('image_chapter')
        ->select(DB::raw("COUNT(id_chapter) id_chapter, id_chapter"))
        ->groupBy("id_chapter")
        ->havingRaw("COUNT(id_chapter) < 5");

        $query = $query->get();

        $result = [];
        if(count($query)) $result = collect($query)->map(function($x){ return (array) $x; })->toArray();
        return $result;
    }
    #================ End checkIdChapterOnImageChapter ==================================

    /**
     * @author [Prayugo]
     * @create date 2019-12-09 01:28:51
     * @modify date 2019-12-09 01:28:51
     * @desc function getDataListGenreManga
     */
    #================  getDataListGenreManga ==================================
    public static function getDataListGenreManga($params = []){
        $ID = (isset($params['id']) ? $params['id'] : '');
        $code = (isset($params['code']) ? $params['code'] : '');
        $genre = (isset($params['genre']) ? $params['genre'] : '');
        $Slug = (isset($params['slug']) ? $params['slug'] : '');
        $startByIndex = (isset($params['start_by_index']) ? $params['start_by_index'] : '');
        $EndByIndex = (isset($params['end_by_index']) ? $params['end_by_index'] : '');
        $isUpdated = (isset($params['is_updated']) ? $params['is_updated'] : FALSE); #untuk data terbaru 2 jam terakhir

        $tabel_name = 'genre_list';
        ini_set('memory_limit','1024M');
        $query = DB::connection('mysql')
            ->table($tabel_name);

        if(!empty($code)) $query = $query->where('code', '=', $code);
        if(!empty($ID)) $query = $query->where('id', '=', $ID);
        if(!empty($Slug)) $query = $query->where('slug', '=', $Slug);
        if(!empty($genre)) $query = $query->where('genre', 'Like', "%".$genre);
        if(!empty($startByIndex)) $query = $query->where('name_index', 'Like', "%".$startByIndex);
        if(!empty($EndByIndex)){
            $alphas = range($startByIndex, $EndByIndex);
            for($i = 0;$i < count($alphas); $i++){
                $query = $query->orWhere('name_index', 'Like', "%".$alphas[$i]);
            }
        }
        if($isUpdated){ #ambil data update atau terbaru
            $startDate = date('Y-m-d');
            $endDate = date("Y-m-d", strtotime('tomorrow'));
            $query = $query->whereBetween('cron_at', [$startDate, $endDate]);
        }
        $query = $query->get();

        $result = [];
        if(count($query)) $result = collect($query)->map(function($x){ return (array) $x; })->toArray();
        return $result;
    }
    #================ End getDataListGenreManga ==================================

    /**
     * @author [Prayugo]
     * @create date 2019-12-09 01:28:51
     * @modify date 2019-12-09 01:28:51
     * @desc function insertGenreListMangaMysql
     */
    #================  insertGenreListMangaMysql ==================================
    public static function insertGenreListMangaMysql($data_all = [], $justInsert = FALSE){
        $tabel_name = 'genre_list';
        $query = DB::connection('mysql')
            ->table($tabel_name);
        if($justInsert){
            $query = $query->insert($data_all);
        }else{
            $query = $query->insertGetId($data_all);
        }
        $error = [];
        $data['status'] = 200;
        $data['message'] = 'success insert '.$tabel_name;
        if(!$query) {
            $data['status'] = 400;
            $data['message'] = 'failed insert '.$tabel_name;
            $error['msg'] = 'error insert '.$tabel_name;
            $error['num'] = 'error num insert '.$tabel_name;
        }

        $data['error'] 	= $error;
        if(!$justInsert) $data['id_result'] = $query;
        return $data;
    }
    #================ End insertGenreListMangaMysql ==================================

    /**
     * @author [Prayugo]
     * @create date 2020-06-01 15:15:49
     * @modify date 2020-06-01 15:15:49
     * @desc [updateGenreListMangaMysql]
     */
    #================  updateGenreListMangaMysql ==================================
    public static function updateGenreListMangaMysql($data_all = [], $conditions){
        $tabel_name = 'genre_list';
        $query = DB::connection('mysql')
            ->table($tabel_name);

        foreach($conditions as $key => $value){
            $query = $query->where($key, $value);
        }
        $query = $query->update($data_all);
        $data['status'] = 400;
        $data['message'] = 'failed insert '.$tabel_name;
        if($query){
            $data['status'] = 200;
            $data['message'] = 'success insert '.$tabel_name;
        }
        return $data;
    }
    #================ End updateGenreListMangaMysql ==================================

    /**
     * @author [Prayugo]
     * @create date 2021-02-28 09:17:34
    * @modify date 2021-02-28 09:17:34
     * @desc function getDataListNotificationManga
     */
    #================  getDataListNotificationManga ==================================
    public static function getDataListNotificationManga($params = []){
        $ID = (isset($params['id']) ? $params['id'] : '');
        $code = (isset($params['code']) ? $params['code'] : '');
        $Slug = (isset($params['slug']) ? $params['slug'] : '');
        $isUpdated = (isset($params['is_updated']) ? $params['is_updated'] : FALSE); #untuk data terbaru 2 jam terakhir

        $tabel_name = 'list_notification';
        ini_set('memory_limit','1024M');
        $query = DB::connection('mysql')
            ->table($tabel_name);

        if(!empty($code)) $query = $query->where('code', '=', $code);
        if(!empty($ID)) $query = $query->where('id', '=', $ID);
        if(!empty($Slug)) $query = $query->where('slug', '=', $Slug);
        if($isUpdated){ #ambil data update atau terbaru
            $startDate = date('Y-m-d');
            $endDate = date("Y-m-d", strtotime('tomorrow'));
            $query = $query->whereBetween('cron_at', [$startDate, $endDate]);
        }
        $query = $query->get();

        $result = [];
        if(count($query)) $result = collect($query)->map(function($x){ return (array) $x; })->toArray();
        return $result;
    }
    #================ End getDataListNotificationManga ==================================

    /**
     * @author [Prayugo]
     * @create date 2021-02-28 09:17:34
    * @modify date 2021-02-28 09:17:34
     * @desc function insertListNotificationMangaMysql
     */
    #================  insertListNotificationMangaMysql ==================================
    public static function insertListNotificationMangaMysql($data_all = [], $justInsert = FALSE){
        $tabel_name = 'list_notification';
        $query = DB::connection('mysql')
            ->table($tabel_name);
        if($justInsert){
            $query = $query->insert($data_all);
        }else{
            $query = $query->insertGetId($data_all);
        }
        $error = [];
        $data['status'] = 200;
        $data['message'] = 'success insert '.$tabel_name;
        if(!$query) {
            $data['status'] = 400;
            $data['message'] = 'failed insert '.$tabel_name;
            $error['msg'] = 'error insert '.$tabel_name;
            $error['num'] = 'error num insert '.$tabel_name;
        }

        $data['error'] 	= $error;
        if(!$justInsert) $data['id_result'] = $query;
        return $data;
    }
    #================ End insertListNotificationMangaMysql ==================================

    /**
     * @author [Prayugo]
     * @create date 2020-06-01 15:15:49
     * @modify date 2020-06-01 15:15:49
     * @desc [updateListNotifiactionMangaMysql]
     */
    #================  updateListNotifiactionMangaMysql ==================================
    public static function updateListNotifiactionMangaMysql($data_all = [], $conditions){
        $tabel_name = 'list_notification';
        $query = DB::connection('mysql')
            ->table($tabel_name);

        foreach($conditions as $key => $value){
            $query = $query->where($key, $value);
        }
        $query = $query->update($data_all);
        $data['status'] = 400;
        $data['message'] = 'failed insert '.$tabel_name;
        if($query){
            $data['status'] = 200;
            $data['message'] = 'success insert '.$tabel_name;
        }
        return $data;
    }
    #================ End updateListNotifiactionMangaMysql ==================================

}
