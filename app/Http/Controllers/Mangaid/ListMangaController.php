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
use App\Helpers\V1\ResponseConnected as ResponseConnected;
use App\Helpers\V1\Converter as Converter;

#Load Models
use App\Models\V1\MainModel as MainModel;
use App\Models\V1\Mongo\MainModelMongo as MainModelMongo;

class ListMangaController extends Controller
{

    function __construct(){
        $this->mongo = Config::get('mongo');
    }
        /**
     * @author [prayugo]
     * @create date 2020-05-05 02:19:09
     * @desc [ListMangaScrap]
     */
    // ================================== ListMangaScrap Save to Mysql =========================
    public function ListMangaScrap(Request $request = Null, $params = Null){
        
        $awal = microtime(true);
        if(!empty($request) || $request != NULL){
            $ApiKey = $request->header("X-API-KEY");
        }
        if(!empty($params) || $params != NULL){
            $ApiKey = (isset($params['params']['X-API-KEY']) ? ($params['params']['X-API-KEY']) : '');
        }
        
        $Users = MainModel::getUser($ApiKey);
        if(!empty($Users)){
            $ConfigController = new ConfigController();
            $BASE_URL = $ConfigController->BASE_URL_MANGA;
            $SLUG_MANGA_LIST = $ConfigController->SLUG_MANGA_LIST;
            return $this->ListMangaScrapValue($BASE_URL, $SLUG_MANGA_LIST, $awal);
        }
    }
    public function ListMangaScrapValue($BASE_URL, $SLUG_MANGA_LIST, $awal){
        
        $BASE_URL_LIST = $BASE_URL.$SLUG_MANGA_LIST;
        $client = new Client(['cookies' => new FileCookieJar('cookies.txt')]);
        $client->getConfig('handler')->push(CloudflareMiddleware::create());
        $goutteClient = new GoutteClient();
        $goutteClient->setClient($client);
        $crawler = $goutteClient->request('GET', $BASE_URL_LIST);
        $response = $goutteClient->getResponse();
        $status = $response->getStatusCode();
        if($status === 200){
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
            
            for($i = 1 ;$i <= $MaxNumber ;$i++){
                $crawlerPagin = $goutteClient->request('GET', $BASE_URL_LIST.'?page='.$i);
                $responsePagin = $goutteClient->getResponse();
                $statusPagin = $responsePagin->getStatusCode();
                if($statusPagin === 200){
                    $SubListManga = $crawlerPagin->filter('.content > .col-sm-6')->each(function ($node,$i) {
                            $listManga = $node->filter('.media-left')->each(function ($node,$i) {
                                $href = $node->filter('a')->attr("href");
                                $slug = substr($href, strrpos($href, '/' )+1);
                                $title = str_replace("-", " ",$slug);
                                $listManga = [
                                    "href" => $href,
                                    "slug" => $slug,
                                    "title" => $title
                                ];
                                return $listManga;
                        });
                        return $listManga;
                    });

                    if($SubListManga){
                        foreach($SubListManga as $valueListManga){
                            $slugListManga = Str::slug($valueListManga[0]['slug']);
                            $code = md5($slugListManga);
                            $href = $valueListManga[0]['href'];
                            $title = $valueListManga[0]['title'];
                            $NameIndexVal = substr($title, 0,1); 
                            $NameIndexVal = !ctype_alpha($NameIndexVal) ? '##' : '#'.strtoupper($NameIndexVal);
                            
                            {#Save List Manga To Mysql
                                $paramCheck['code'] = $code;
                                $checkExist = MainModel::getDataListManga($paramCheck);
                                if(empty($checkExist)){
                                    $paramInput = [
                                        'code' => $code,
                                        "href" => $href,
                                        "slug" => $slugListManga,
                                        "title" => $title,
                                        'name_index' => $NameIndexVal,
                                        'cron_at' => Carbon::now()->format('Y-m-d H:i:s')
                                    ];
                                    $LogSave [] = "Data Save - ".$title;
                                    $save = MainModel::insertListMangaMysql($paramInput);   
                                }else{
                                    $conditions['id'] = $checkExist[0]['id'];
                                    $paramUpdate = array(
                                        'code' => $code,
                                        "href" => $href,
                                        "slug" => $slugListManga,
                                        "title" => $title,
                                        'name_index' => $NameIndexVal,
                                        'cron_at' => Carbon::now()->format('Y-m-d H:i:s')
                                    );
                                    $LogSave [] =  "Data Update - ".$title;
                                    $save = MainModel::updateListMangaMysql($paramUpdate,$conditions);
                                    
                                }
                            }
                        }
                        
                    }else{
                        return ResponseConnected::PageNotFound("List Manga","Page Not Found.", $awal);
                    }
                }else{
                    return ResponseConnected::PageNotFound("List Manga","Page Not Found.", $awal);
                }
            }
            return ResponseConnected::Success("List Manga", Null, $LogSave, $awal);
        }else{
            return ResponseConnected::PageNotFound("List Manga","Page Not Found.", $awal);
                        
        }
    }

    // ======================= List Manga Generate save to Mongo ======================
    public function ListMangaGenerate(Request $request = NULL, $params = NULL){

        $param = $params; # get param dari populartopiclist atau dari cron
        if(is_null($params)) $param = $request->all();

        $id = (isset($param['params']['id']) ? $param['params']['id'] : NULL);
        $code = (isset($param['params']['code']) ? $param['params']['code'] : '');
        $slug = (isset($param['params']['slug']) ? $param['params']['slug'] : '');
        $title = (isset($param['params']['title']) ? $param['params']['title'] : '');
        $startNameIndex = (isset($param['params']['start_name_index']) ? $param['params']['start_name_index'] : '');
        $endNameIndex = (isset($param['params']['end_name_index']) ? $param['params']['end_name_index'] : '');
        $startDate = (isset($param['params']['start_date']) ? $param['params']['start_date'] : NULL);
        $endDate = (isset($param['params']['end_date']) ? $param['params']['end_date'] : NULL);
        $isUpdated = (isset($param['params']['is_updated']) ? filter_var($param['params']['is_updated'], FILTER_VALIDATE_BOOLEAN) : FALSE);

        #jika pakai range date
        $showLog = (isset($param['params']['show_log']) ? $param['params']['show_log'] : FALSE);
        $parameter = [
            'id' => $id,
            'code' => $code,
            'slug' => $slug,
            'title' => $title,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_by_index' => $startNameIndex,
            'end_by_index' => $endNameIndex,
            'is_updated' => $isUpdated
        ];
        
        $ListManga = MainModel::getDataListManga($parameter);
        
        $errorCount = 0;
        $successCount = 0;
        if(count($ListManga)){
            foreach($ListManga as $ListManga){
                $conditions = [
                    'id_auto' => $ListManga['id'].'-listManga',
                ];
                $parameDetail = [
                    'id_list_manga' => $ListManga['id'],
                ];
                $detailManga = MainModel::getDataDetailManga($parameDetail);
                $idDetail = '';
                $status = '';
                $image = '';
                $genre = '';
                $rating = '';
                foreach($detailManga as $valueDetail){
                    $idDetail = $valueDetail['id'];
                    $status = $valueDetail['status'];
                    $image = $valueDetail['image'];
                    $genre = $valueDetail['genre'];
                    $rating = $valueDetail['rating'];
                }
                $MappingMongo = array(
                    'id_auto' => $ListManga['id'].'-listManga',
                    'id_list_manga' => $ListManga['id'],
                    'id_detail_Manga' => $idDetail,
                    'source_type' => 'list-Manga',
                    'code' => $ListManga['code'],
                    'title' => Converter::__normalizeSummary($ListManga['title']),
                    'slug' => $ListManga['slug'],
                    'name_index' => $ListManga['name_index'],
                    // 'image' => $image,
                    'status' => $status,
                    'rating' => $rating,
                    'genre' => explode('|',substr(trim($genre),0,-1)),
                    'keyword' => explode('-',$ListManga['slug']),
                    'meta_title' => (Converter::__normalizeSummary(strtolower($ListManga['title']))),
                    'meta_keywords' => explode('-',$ListManga['slug']),
                    'meta_tags' => explode('-',$ListManga['slug']),
                    'cron_at' => $ListManga['cron_at']
                );
                
                $updateMongo = MainModelMongo::updateListManga($MappingMongo, $this->mongo['use_collections_list_manga'], $conditions, TRUE);
                
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
                        $error_id['response']['id'][$key] = $ListManga['id']; #set id error generate
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

    // ======================= End List Manga Generate save to Mongo======================
}
