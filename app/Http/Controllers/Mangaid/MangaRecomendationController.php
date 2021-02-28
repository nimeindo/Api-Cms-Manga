<?php
namespace App\Http\Controllers\Mangaid;
use \Illuminate\Http\Request;
use \Illuminate\Http\Response;
use \App\Http\Controllers\Controller;
use \App\Http\Controllers\ConfigController;
use \GuzzleHttp\Client;
use \Goutte\Client as GoutteClient;
use \Tuna\CloudflareMiddleware;
use \GuzzleHttp\Cookie\FileCookieJar;
use \GuzzleHttp\Psr7;
use \Carbon\Carbon;
use Illuminate\Support\Str;
use Config; 
use Throwable;

#Load Helper V1
use App\Helpers\V1\Converter as Converter;
use App\Helpers\V1\ResponseConnected as ResponseConnected;

#Load Models
use App\Models\V1\MainModel as MainModel;
use App\Models\V1\Mongo\MainModelMongo as MainModelMongo;

class MangaRecomendationController extends Controller
{
    function __construct(){
        $this->mongo = Config::get('mongo');
    }

    public function generateRecomendationManga(Request $request = NULL, $params = NULL){
        $param = $params; # get param dari populartopiclist atau dari cron
        if(is_null($params)) $param = $request->all();
        
        $id = (isset($param['params']['id_detail']) ? $param['params']['id_detail'] : NULL);
        $idListManga = (isset($param['params']['id_list_manga']) ? $param['params']['id_list_manga'] : NULL);
        $code = (isset($param['params']['code']) ? $param['params']['code'] : '');
        $slug = (isset($param['params']['slug']) ? $param['params']['slug'] : '');
        $title = (isset($param['params']['title']) ? $param['params']['title'] : '');
        $startDate = (isset($param['params']['start_date']) ? $param['params']['start_date'] : NULL);
        $endDate = (isset($param['params']['end_date']) ? $param['params']['end_date'] : NULL);
        $isUpdated = (isset($param['params']['is_updated']) ? filter_var($param['params']['is_updated'], FILTER_VALIDATE_BOOLEAN) : FALSE);
        
        #jika pakai range date
        $showLog = (isset($param['params']['show_log']) ? $param['params']['show_log'] : FALSE);
        $parameter = [
            'id' => $id,
            'id_list_manga' => $idListManga,
            'code' => $code,
            'slug' => $slug,
            'title' => $title,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_updated' => $isUpdated
        ];
        $detailManga = MainModel::getDataDetailManga($parameter);
        $errorCount = 0;
        $successCount = 0;
        if(count($detailManga)){
            foreach($detailManga as $valueDetailManga){
                
                $conditions = [
                    'id_auto' => $valueDetailManga['id'].'-detailManga',
                ];
                $param = [
                    'id_detail_manga' => $valueDetailManga['id']
                ];
                
                $MappingMongo = array(
                    'id_auto' => $valueDetailManga['id'].'-detailManga',
                    'id_list_manga' => $valueDetailManga['id_list_manga'],
                    'id_detail_manga' => $valueDetailManga['id'],
                    'source_type' => 'detail-manga',
                    'code' => $valueDetailManga['code'],
                    'title' => Converter::__normalizeSummary($valueDetailManga['title']),
                    'slug' => $valueDetailManga['slug'],
                    'type' => $valueDetailManga['tipe'],
                    'synopsis' => $valueDetailManga['synopsis'],
                    'image' => $valueDetailManga['image'],
                    'status' => $valueDetailManga['status'],
                    'rating' => $valueDetailManga['rating'],
                    'author' => $valueDetailManga['author'],
                    'release_date' => $valueDetailManga['release_date'],
                    'genre' => explode('|',substr(trim($valueDetailManga['genre']),0,-1)),
                    'keyword' => explode('-',$valueDetailManga['slug']),
                    'meta_title' => (Converter::__normalizeSummary(strtolower($valueDetailManga['title']))),
                    'meta_keywords' => explode('-',$valueDetailManga['slug']),
                    'meta_tags' => explode('-',$valueDetailManga['slug']),
                    'cron_at' => $valueDetailManga['cron_at']
                );
                
                $updateMongo = MainModelMongo::updateRecomendationManga($MappingMongo, $this->mongo['use_collection_recomendation_manga'], $conditions, TRUE);
                
                $status = 400;
                $message = '';
                $messageLocal = '';
                if($updateMongo['status'] == 200){
                    $status = 200;
                    $message = 'success';
                    $messageLocal = $updateMongo['message_local'];
                    $successCount++;

                }else{
                    #jika dari cron dan pakai last_date atau pakai generate error
                    #set error id generate
                    if( (!is_null($params) && $endDate == TRUE) || (!is_null($params) && !empty($ids)) ){
                        $error_id['response']['id'][$key] = $valueDetailManga['id']; #set id error generate
                    }

                    $status = 400;
                    $message = 'error';
                    $messageLocal = serialize($updateMongo['message_local']);
                    $errorCount++;
                }

                #show log response
                if($showLog){
                    $slug = $MappingMongo['slug'];
                    $prefixDate = Carbon::parse($MappingMongo['cron_at'])->format('Y-m-d H:i:s');
                    if($isUpdated == TRUE) $prefixDate = Carbon::parse($MappingMongo['cron_at'])->format('Y-m-d H:i:s');
                    echo $message.' | '.$prefixDate.' | '.$MappingMongo['id_auto'] .' => '.$slug.' | '.$messageLocal."\n";

                }
                
            }
            
        }else{
            $status = 400;
            $message = 'data tidak ditemukan';
        }

        $response['error'] = $errorCount;
        $response['success'] = $successCount;

        if(!is_null($params)){ # untuk cron
            return $response;
        }else{
            return (new Response($response, 200))
                ->header('Content-Type', 'application/json');
        }
    
    }

    public function unPublishRecomendatinManga(Request $request = NULL, $params = NULL){
        $param = $params; # get param dari populartopiclist atau dari cron
        if(is_null($params)) $param = $request->all();
        
        $id = (isset($param['params']['id_detail_manga']) ? $param['params']['id_detail_manga'] : NULL);
        $idListManga = (isset($param['params']['id_list_manga']) ? $param['params']['id_list_manga'] : NULL);
        $code = (isset($param['params']['code']) ? $param['params']['code'] : '');
        $slug = (isset($param['params']['slug']) ? $param['params']['slug'] : '');
        $isUpdated = (isset($param['params']['is_updated']) ? filter_var($param['params']['is_updated'], FILTER_VALIDATE_BOOLEAN) : FALSE);
        #jika pakai range date
        $showLog = (isset($param['params']['show_log']) ? $param['params']['show_log'] : FALSE);
        $parameter = [
            'slug' => $slug,
        ];

        #Get Data Mongo
        $detailManga = MainModelMongo::getDataRecomendationManga($parameter);

        $status = 400;
        $message = 'data tidak ditemukan';
        $dataUnpublish = '';
        if(count($detailManga)){
            foreach($detailManga['collection'] as $detailMangaV){
                $dataUnpublish = $detailMangaV['id_auto'].'-'.$detailMangaV['slug'];
            }

            $delete = MainModelMongo::deleteData($this->mongo['use_collection_recomendation_manga'], $parameter);

            if($delete['status'] == 200){

                $status = 200;
                $message = 'success';

            }else{

                $status = 400;
                $message = 'error delete mongo';

            }
        }

        $result['response']['status'] = $status;
        $result['response']['message'] = $message;
        $result['response']['id'] = $dataUnpublish;

        return (new Response($result, 200))
            ->header('Content-Type', 'application/json');

    }

}