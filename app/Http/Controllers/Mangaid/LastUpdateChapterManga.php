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

#Load Controller
use App\Http\Controllers\Mangaid\DetailMangaController;

#Load Helper V1
use App\Helpers\V1\Converter as Converter;
use App\Helpers\V1\ResponseConnected as ResponseConnected;

#Load Models
use App\Models\V1\MainModel as MainModel;

class LastUpdateChapterManga extends Controller
{
    public function __construct()
    {
        $this->DetailMangaController = new DetailMangaController();
    }

    public function LastUpdateChapterMangaScrap(Request $request = NULL, $params = NULL){
        $awal = microtime(true);
        if(!empty($request) || $request != NULL){
            $param = $params; # get param dari populartopiclist atau dari cron
            if(is_null($params)) $param = $request->all();
            $ApiKey = $request->header("X-API-KEY");
            $PageNumber = (isset($param['params']['PageNumber']) ? ($param['params']['PageNumber']) : 1);
        }
        if(!empty($params) || $params != NULL){
            $PageNumber = (isset($params['params']['PageNumber']) ? ($params['params']['PageNumber']) : 1);
            $ApiKey = (isset($params['params']['X-API-KEY']) ? ($params['params']['X-API-KEY']) : '');
        }
        $Users = MainModel::getUser($ApiKey);
        if(!empty($Users)){
            $ConfigController = new ConfigController();
            $BASE_URL = $ConfigController->BASE_URL_MANGA;
            $LAST_UPDATE = $ConfigController->LAST_UPDATE_MANGA;
            if($PageNumber < 2){
                $BASE_URL_UPDATE = $BASE_URL.$LAST_UPDATE;
            }else{
                $BASE_URL_UPDATE = $BASE_URL.$LAST_UPDATE."?page=".$PageNumber;
            }
            return $this->LastUpdateChapterMangaScrapValue($BASE_URL, $BASE_URL_UPDATE, $awal);
        }
    }

    public function LastUpdateChapterMangaScrapValue($BASE_URL, $BASE_URL_UPDATE, $awal){
        $BASE_URL_LIST = $BASE_URL_UPDATE;
        $client = new Client(['cookies' => new FileCookieJar('cookies.txt')]);
        $client->getConfig('handler')->push(CloudflareMiddleware::create());
        $goutteClient = new GoutteClient();
        $goutteClient->setClient($client);
        $crawler = $goutteClient->request('GET', $BASE_URL_LIST);
        $response = $goutteClient->getResponse();
        $status = $response->getStatusCode();
        if($status === 200){
            $SubLastUpdate = $crawler->filter('.mangalist')->each(function ($node,$i) {
                $chapterUpdate= $node->filter('.media-body')->each(function ($node,$i) {
                    $hrefDetail = $node->filter('h3 > a')->attr('href');
                    $slugDetail = str_replace('/','-',substr($hrefDetail, strrpos($hrefDetail, 'manga/' )+6));
                    $title      = ucwords(str_replace('-',' ',$slugDetail));
                    $hrefChapter = $node->filter('div > a')->attr('href');
                    $slugChapter = str_replace('/','-',substr($hrefChapter, strrpos($hrefChapter, 'manga/' )+6));
                    $chapter    = 'Chapter '.substr($hrefChapter, strrpos($hrefChapter, '/' )+1);
                    $chapterUpdate = [
                        'hrefDetail' => $hrefDetail,
                        'slugDetail' => $slugDetail,
                        'title'     => $title,
                        'chapter'   => $chapter,
                        'hrefChapter' => $hrefChapter,
                        'slugChapter' => $slugChapter
                    ];
                    return $chapterUpdate;
                });
                
                return $chapterUpdate;
            });
            $SubPagination = $crawler->filter('.col-xs-12')->each(function ($node,$i) {
                $listPagination = $node->filter('.pagination')->each(function ($node,$i) {
                    $listPagination = $node->filter('li')->each(function ($node,$i) {
                    $numberPagination = $node->filter('a')->text('Default text content');
                        return $numberPagination;
                    });
                    return $listPagination;
                });
                return $listPagination;
            });
            $MaxNumber = 0;
            foreach($SubPagination[0][0] as $valueSub){
                if(is_numeric($valueSub)){
                    $MaxNumber = $valueSub;
                }
            }
            // dd($SubLastUpdate);
            if($SubLastUpdate){
                for($i = 0 ; $i < count($SubLastUpdate[0]); $i++){
                    $hrefDetail = isset($SubLastUpdate[0][$i]['hrefDetail']) ? $SubLastUpdate[0][$i]['hrefDetail'] : '';
                    $slugDetail = isset($SubLastUpdate[0][$i]['slugDetail']) ? $SubLastUpdate[0][$i]['slugDetail'] : '';
                    $title = isset($SubLastUpdate[0][$i]['title']) ? $SubLastUpdate[0][$i]['title'] : '';
                    $chapter = isset($SubLastUpdate[0][$i]['chapter']) ? $SubLastUpdate[0][$i]['chapter'] : '';
                    $hrefChapter = isset($SubLastUpdate[0][$i]['hrefChapter']) ? $SubLastUpdate[0][$i]['hrefChapter'] : '';
                    $slugChapter = isset($SubLastUpdate[0][$i]['slugChapter']) ? $SubLastUpdate[0][$i]['slugChapter'] : '';
                    $codeDetail = md5($slugDetail);
                    $paramCodeDetail['code'] = $codeDetail;
                    $codeChapter = md5($slugChapter);
                    $paramIdListChapter['code'] = $codeChapter;
                    $detailManga = MainModel::getDataDetailManga($paramCodeDetail);
                    if(empty($detailManga)){
                        $listDetailManga = [
                            'params' => [
                                'X-API-KEY' => env('X_API_KEY',''),
                                'detail_href' => $hrefDetail
                            ]
                        ];
                        $dataDetailMangaScrap = $this->DetailMangaController->DetailMangaScrap(NULL,$listDetailManga);
                        $detailManga = MainModel::getDataDetailManga($paramCodeDetail);
                        $idDetailManga = (empty($detailManga)) ? 0 : $detailManga[0]['id'];
                        $idListManga = (empty($detailManga)) ? 0 : $detailManga[0]['id_list_manga'];
                        $dataChapter = MainModel::getDataChapterManga($paramIdListChapter);
                    }else{
                        $idDetailManga = (empty($detailManga)) ? 0 : $detailManga[0]['id'];
                        $idListManga = (empty($detailManga)) ? 0 : $detailManga[0]['id_list_manga'];
                        $dataChapter = MainModel::getDataChapterManga($paramIdListChapter);
                        dd($dataChapter);
                    }
                }
            }else{
                //page not found    
            }
        }else{
            //page not found
        }
    }
}