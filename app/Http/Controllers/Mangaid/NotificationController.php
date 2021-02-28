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

class NotificationController extends Controller
{
    public function addNotificationList(Request $request = NULL, $params = NULL){
        $param = $params; # get param dari populartopiclist atau dari cron
        if(is_null($params)) $param = $request->all();

        $idDetailManga = (isset($param['params']['id_detail_manga']) ? $param['params']['id_detail_manga'] : NULL);
        $idListManga = (isset($param['params']['id_list_manga']) ? $param['params']['id_list_manga'] : NULL);
        $code = (isset($param['params']['code']) ? $param['params']['code'] : '');
        $slug = (isset($param['params']['slug']) ? $param['params']['slug'] : '');
        #jika pakai log
        $showLog = (isset($param['params']['show_log']) ? $param['params']['show_log'] : FALSE);
        $parameter = [
            'id' => $idDetailManga,
            'id_list_manga' => $idListManga,
            'code' => $code,
            'slug' => $slug,
        ];
        $detailManga = MainModel::getDataDetailManga($parameter);
        $errorCount = 0;
        $successCount = 0;
        $message = 'error';
        $status = 400;
        if(count($detailManga)){
            foreach($detailManga as $valueDetailManga){
                $paramIdListNotification['slug'] = $valueDetailManga['slug'];
                $checkExist = MainModel::getDataListNotificationManga($paramIdListNotification);
                if(empty($checkExist)){
                    $Input = array(
                        'id_detail_manga' => $valueDetailManga['id'],
                        'id_list_manga' => $valueDetailManga['id_list_manga'],
                        "code" => $valueDetailManga['code'],
                        'slug' => $valueDetailManga['slug'],
                        'cron_at' => Carbon::now()->format('Y-m-d H:i:s')
                    );
                    $message = "Save Data";
                    $LogSave [] = "Data Save - ".$valueDetailManga['slug'];
                    $save = MainModel::insertListNotificationMangaMysql($Input);
                }else{
                    $conditions['id'] = $checkExist[0]['id'];
                    $Update = array(
                        'id_detail_manga' => $valueDetailManga['id'],
                        'id_list_manga' => $valueDetailManga['id_list_manga'],
                        "code" => $valueDetailManga['code'],
                        'slug' => $valueDetailManga['slug'],
                        'cron_at' => Carbon::now()->format('Y-m-d H:i:s')
                    );
                    $message = "Update Data";
                    $LogSave [] =  "Data Update - ".$valueDetailManga['slug'];
                    $save = MainModel::updateListNotifiactionMangaMysql($Update,$conditions);
                }
                #show log response
                if($showLog){
                    $slug = $valueDetailManga['slug'];
                    $prefixDate = Carbon::now()->format('Y-m-d H:i:s');
                    echo $message.' | '.$prefixDate.' => '.$slug.' | '."\n";
                }
                $successCount++;
            }
        }else{
            $errorCount++;
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
}
