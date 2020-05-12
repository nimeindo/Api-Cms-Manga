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

#Load Models
use App\Models\V1\MainModel as MainModel;

class ListMangaController extends Controller
{
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
}
