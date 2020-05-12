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
        $startByIndex = (isset($params['start_by_index']) ? $params['start_by_index'] : '');
        $EndByIndex = (isset($params['end_by_index']) ? $params['end_by_index'] : '');
        $startDate = (isset($params['start_date']) ? $params['start_date'] : '');
        $endDate = (isset($params['end_date']) ? $params['end_date'] : '');
        $tabel_name = 'list_manga';
        ini_set('memory_limit','1024M');
        $query = DB::connection('mysql')
            ->table($tabel_name);
        
        if(!empty($code)) $query = $query->where('code', '=', $code);
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
        $id = (isset($params['id']) ? $params['id'] : '');
        $id_detail_manga = (isset($params['id_detail_manga']) ? $params['id_detail_manga'] : '');
        $startDate = (isset($params['start_date']) ? $params['start_date'] : '');
        $endDate = (isset($params['end_date']) ? $params['end_date'] : '');
        
        $tabel_name = 'chapter';
        ini_set('memory_limit','1024M');
        $query = DB::connection('mysql')
            ->table($tabel_name);
        
        if(!empty($code)) $query = $query->where('code', '=', $code);
        if(!empty($id)) $query = $query->where('id', '=', $id);
        if(!empty($id_detail_manga)) $query = $query->where('id_detail_manga', '=', $id_detail_manga);

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
        $id = (isset($params['id']) ? $params['id'] : '');
        $id_chapter = (isset($params['id_chapter']) ? $params['id_chapter'] : '');
        $startDate = (isset($params['start_date']) ? $params['start_date'] : '');
        $endDate = (isset($params['end_date']) ? $params['end_date'] : '');
        
        $tabel_name = 'image_chapter';
        ini_set('memory_limit','1024M');
        $query = DB::connection('mysql')
            ->table($tabel_name);
        
        if(!empty($code)) $query = $query->where('code', '=', $code);
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

}