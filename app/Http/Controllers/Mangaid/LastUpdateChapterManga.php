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
            return $this->LastUpdateChapterMangaScrapValue($BASE_URL, $BASE_URL_UPDATE,$PageNumber ,$awal);
        }
    }
    

    public function LastUpdateChapterMangaScrapValue($BASE_URL, $BASE_URL_UPDATE, $PageNumber, $awal){
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
                    $getTextDiv = $node->filter('div')->text('Default text content');
                    $subDate = substr($getTextDiv, 0, strpos($getTextDiv, ' ago'));
                    $DateTime = explode(' ',trim(substr($subDate,-8)));
                    $chapterUpdate = [
                        'hrefDetail' => $hrefDetail,
                        'slugDetail' => $slugDetail,
                        'title'     => $title,
                        'chapter'   => $chapter,
                        'hrefChapter' => $hrefChapter,
                        'slugChapter' => $slugChapter,
                        'time' => $DateTime
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
            // $MaxNumber = 0;
            foreach($SubPagination[0][0] as $valueSub){
                if(is_numeric($valueSub)){
                    $MaxNumber = $valueSub;
                }
            }
            // dd($MaxNumber);
            if($SubLastUpdate){
                for($i = 0 ; $i < count($SubLastUpdate[0]); $i++){
                    $hrefDetail = isset($SubLastUpdate[0][$i]['hrefDetail']) ? $SubLastUpdate[0][$i]['hrefDetail'] : '';
                    $slugDetail = isset($SubLastUpdate[0][$i]['slugDetail']) ? $SubLastUpdate[0][$i]['slugDetail'] : '';
                    $title = isset($SubLastUpdate[0][$i]['title']) ? $SubLastUpdate[0][$i]['title'] : '';
                    $chapter = isset($SubLastUpdate[0][$i]['chapter']) ? $SubLastUpdate[0][$i]['chapter'] : '';
                    $hrefChapter = isset($SubLastUpdate[0][$i]['hrefChapter']) ? $SubLastUpdate[0][$i]['hrefChapter'] : '';
                    $slugChapter = isset($SubLastUpdate[0][$i]['slugChapter']) ? $SubLastUpdate[0][$i]['slugChapter'] : '';
                    $countTime = isset($SubLastUpdate[0][$i]['time'][0]) ? $SubLastUpdate[0][$i]['time'][0] : 0;
                    $countDays = isset($SubLastUpdate[0][$i]['time'][1]) ? substr($SubLastUpdate[0][$i]['time'][1],0,2) : 0;
                    $publishDateChapter = $this->convertPublishDate($countDays, $countTime);
                    
                    $totalSearchPage = $MaxNumber;
                    $pageSearch = $PageNumber;
                    $codeDetail = md5($slugDetail);
                    $paramCodeDetail['code'] = $codeDetail;
                    $codeChapter = md5($slugChapter);
                    $paramIdChapter['code'] = $codeChapter;
                    $detailManga = MainModel::getDataDetailManga($paramCodeDetail);
                    $paramLastUpdate = [
                        'total_search_page' => $totalSearchPage,
                        'page_search' => $pageSearch,
                        "code" => $codeChapter,
                        'slug' => $slugChapter,
                        'title' => $title,
                        'status' => '',
                        'chapter' => $chapter,
                        'href' => $hrefChapter,
                        'publish_date' => $publishDateChapter
                    ];
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
                        $dataChapter = MainModel::getDataChapterManga($paramIdChapter);
                        $idChapter = (empty($dataChapter)) ? 0 : $dataChapter[0]['id'];
                        // cek jika data chapter tidak ada maka akan menginput baru
                        if(empty($dataChapter)){
                            $Input = array(
                                'id_detail_manga' => $idDetailManga,
                                'id_list_manga' => $idListManga,
                                "code" => $codeChapter,
                                'slug' => $slugChapter,
                                'chapter' => $chapter,
                                'date_publish' => $publishDateChapter,
                                'chapter_href' => $hrefChapter,
                                'cron_at' => Carbon::now()->format('Y-m-d H:i:s')
                            );
                            $LogSaveChapter [] = "Data Save - ".$slugChapter;
                            $save = MainModel::insertChapterMangaMysql($Input);
                            $dataChapter = MainModel::getDataChapterManga($paramIdChapter);
                            $idChapter = (empty($dataChapter)) ? 0 : $dataChapter[0]['id'];
                        }
                        {//insert lats update
                            $dataLastUpdateChapter = MainModel::getDataLastUpdateChapterManga($paramIdChapter);
                            $LogSave [] = $this->saveLastUpdate($detailManga, $dataChapter, $dataLastUpdateChapter ,$paramLastUpdate);
                        }
                    }else{
                        $idDetailManga = (empty($detailManga)) ? 0 : $detailManga[0]['id'];
                        $idListManga = (empty($detailManga)) ? 0 : $detailManga[0]['id_list_manga'];
                        $dataChapter = MainModel::getDataChapterManga($paramIdChapter);
                        $idChapter = (empty($dataChapter)) ? 0 : $dataChapter[0]['id'];
                        // cek jika data chapter tidak ada maka akan menginput baru
                        if(empty($dataChapter)){
                            $Input = array(
                                'id_detail_manga' => $idDetailManga,
                                'id_list_manga' => $idListManga,
                                "code" => $codeChapter,
                                'slug' => $slugChapter,
                                'chapter' => $chapter,
                                'date_publish' => $publishDateChapter,
                                'chapter_href' => $hrefChapter,
                                'cron_at' => Carbon::now()->format('Y-m-d H:i:s')
                            );
                            $LogSaveChapter [] = "Data Save - ".$slugChapter;
                            $save = MainModel::insertChapterMangaMysql($Input);
                            $dataChapter = MainModel::getDataChapterManga($paramIdChapter);
                            $idChapter = (empty($dataChapter)) ? 0 : $dataChapter[0]['id'];
                        }
                        {//insert lats update
                            $dataLastUpdateChapter = MainModel::getDataLastUpdateChapterManga($paramIdChapter);
                            $LogSave [] = $this->saveLastUpdate($detailManga, $dataChapter, $dataLastUpdateChapter ,$paramLastUpdate);
                        }
                        
                    }
                }
                return ResponseConnected::Success("Last Update Chapter Manga", Null, $LogSave, $awal);
            }else{
                return ResponseConnected::PageNotFound("Last Update Chapter Manga","Page Not Found.", $awal);
            }
        }else{
            return ResponseConnected::PageNotFound("Last Update Chapter Manga","Page Not Found.", $awal);
        }
    }

    public function saveLastUpdate($detailManga, $dataChapter, $dataLastUpdateChapter ,$paramLastUpdate){
        $idDetailManga = (empty($detailManga)) ? 0 : $detailManga[0]['id'];
        $idListManga = (empty($detailManga)) ? 0 : $detailManga[0]['id_list_manga'];
        $idChapter = (empty($dataChapter)) ? 0 : $dataChapter[0]['id'];
        
        if(empty($dataLastUpdateChapter)){
            $Input = array(
                'id_detail_manga' => $idDetailManga,
                'id_list_manga' => $idListManga,
                'id_chapter' => $idChapter,
                'total_search_page' => $paramLastUpdate['total_search_page'],
                'page_search' => $paramLastUpdate['page_search'],
                "code" => $paramLastUpdate['code'],
                'slug' => $paramLastUpdate['slug'],
                'title' => $paramLastUpdate['title'],
                'status' => '',
                'chapter' => $paramLastUpdate['chapter'],
                'publish' => $paramLastUpdate['publish_date'],
                'href' => $paramLastUpdate['href'],
                'cron_at' => Carbon::now()->format('Y-m-d H:i:s')
            );
            
            $LogSave  = "Data Save - ".$paramLastUpdate['slug'];
            $save = MainModel::insertLastUpdateChapterMangaMysql($Input);
        }else{
            $conditions['id'] = $dataLastUpdateChapter[0]['id'];
            $Update = array(
                'id_detail_manga' => $idDetailManga,
                'id_list_manga' => $idListManga,
                'id_chapter' => $idChapter,
                'total_search_page' => $paramLastUpdate['total_search_page'],
                'page_search' => $paramLastUpdate['page_search'],
                "code" => $paramLastUpdate['code'],
                'slug' => $paramLastUpdate['slug'],
                'title' => $paramLastUpdate['title'],
                'status' => '',
                'chapter' => $paramLastUpdate['chapter'],
                'publish' => $paramLastUpdate['publish_date'],
                'href' => $paramLastUpdate['href'],
                'cron_at' => Carbon::now()->format('Y-m-d H:i:s')
            );
            $LogSave  = "Data Update - ".$paramLastUpdate['slug'];
            $save = MainModel::updateLastUpdateChapterMangaMysql($Update,$conditions);
        }  
        return $LogSave;
    }

    public function convertPublishDate($countDays,$countTime){
        if($countDays == 'ye'){
            $addDays = $countTime.' years';
        }elseif($countDays == 'da'){
            $addDays = $countTime.' days';
        }elseif($countDays == 'mi'){
            $addDays = $countTime.' minuts';
        }elseif($countDays == 'se'){
            $addDays = $countTime.' seconds';
        }elseif($countDays == 'ho'){
            $addDays = $countTime.' hours';
        }elseif($countDays == 'we'){
            $addDays = $countTime.' weeks';
        }elseif($countDays == 'mo'){
            $addDays = $countTime.' months';
        }else{
            $addDays = '0 days';
        }
        $date = date('Y-m-d H:i:s');
        $newtimestamp = strtotime($date.'-'.$addDays);
        $datePublish = date('Y-m-d H:i:s', $newtimestamp);
        return $datePublish;
    }
}