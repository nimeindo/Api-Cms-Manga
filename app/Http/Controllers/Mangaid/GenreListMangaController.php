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


class GenreListMangaController extends Controller
{ 
    /**
     * @author [Prayugo]
     * @create date 2020-06-02 00:44:09
     * @modify date 2020-06-02 00:44:09
     * @desc [__construct]
     */
    // ================================== __construct Save to Mysql =========================
    function __construct(){
        $this->mongo = Config::get('mongo');
    }
    // ============================= End __construct Save To Mongo===========================

    /**
     * @author [Prayugo]
     * @create date 2020-06-02 00:44:09
     * @modify date 2020-06-02 00:44:09
     * @desc [__construct]
     */
    // ================================== GenreListMangaScrap Save to Mysql =========================
    public function GenreListMangaScrap(Request $request = Null, $params = Null){
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
            return $this->GenreListMangaScrapValue($BASE_URL, $SLUG_MANGA_LIST, $awal);
        }
    }

    public function GenreListMangaScrapValue($BASE_URL, $SLUG_MANGA_LIST, $awal){
        $BASE_URL_LIST = $BASE_URL.$SLUG_MANGA_LIST;
        $client = new Client(['cookies' => new FileCookieJar('cookies.txt')]);
        $client->getConfig('handler')->push(CloudflareMiddleware::create());
        $goutteClient = new GoutteClient();
        $goutteClient->setClient($client);
        $crawler = $goutteClient->request('GET', $BASE_URL_LIST);
        $response = $goutteClient->getResponse();
        $status = $response->getStatusCode();
        if($status === 200){
            $GenreManga = $crawler->filter('.list-category')->each(function ($node,$i) {
                $SubGenre = $node->filter('li')->each(function ($node,$i) {
                    $Genre = $node->filter('a')->text('Default text content');
                    
                    return $Genre;
                });
                return $SubGenre;
            });

            if($GenreManga){
                for($i = 0; $i < count($GenreManga[0]); $i++){
                    $nameGenre = $GenreManga[0][$i];
                    $nameIndexGenre = substr($nameGenre, 0,1); 
                    {#Save Data List Genre
                        $slugGenre = Str::slug($nameGenre);;
                        $code = md5($slugGenre);
                        $paramCheck['code'] = $code;
                        $checkExist = MainModel::getDataListGenreManga($paramCheck);
                        if(empty($checkExist)){
                            $Input = array(
                                "code" => $code,
                                'slug' => $slugGenre,
                                "name_index" => $nameIndexGenre,
                                "genre" => $nameGenre,
                                'cron_at' => Carbon::now()->format('Y-m-d H:i:s')
                            );
                            $LogSave [] = "Data Save - ".$nameGenre."-".Carbon::now()->format('Y-m-d H:i:s');
                            
                            $save = MainModel::insertGenreListMangaMysql($Input);
                        }else{
                            $conditions['id'] = $checkExist[0]['id'];
                            $Update = array(
                                "code" => $code,
                                'slug' => $slugGenre,
                                "name_index" => $nameIndexGenre,
                                "genre" => $nameGenre,
                                'cron_at' => Carbon::now()->format('Y-m-d H:i:s')
                            );
                            $LogSave [] = "Data Update - ".$nameGenre."-".Carbon::now()->format('Y-m-d H:i:s');
                            $save = MainModel::updateGenreListMangaMysql($Update,$conditions);
                        }
                    }
                    
                }
                return ResponseConnected::Success("Genre List Manga", $save, $LogSave, $awal);
            }else{
                return ResponseConnected::PageNotFound("Genre List Manga","Page Not Found.", $awal);    
            }
        }else{
            return ResponseConnected::PageNotFound("Genre List Manga","Page Not Found.", $awal);
        }
    }
    // ============================= End GenerateGenreListManga Save To Mongo===========================

    /**
     * @author [Prayugo]
     * @create date 2020-06-02 00:44:09
     * @modify date 2020-06-02 00:44:09
     * @desc [__construct]
     */
    // ============================= GenerateGenreListManga Save To Mongo ===========================
    public function GenerateGenreListManga(Request $request = NULL, $params = NULL){

        $param = $params; # get param dari populartopiclist atau dari cron
        if(is_null($params)) $param = $request->all();

        $id = (isset($param['params']['id']) ? $param['params']['id'] : '');
        $code = (isset($param['params']['code']) ? $param['params']['code'] : '');
        $slug = (isset($param['params']['slug']) ? $param['params']['slug'] : '');
        $startNameIndex = (isset($param['params']['start_name_index']) ? $param['params']['start_name_index'] : '');
        $endNameIndex = (isset($param['params']['end_name_index']) ? $param['params']['end_name_index'] : '');
        $title = (isset($param['params']['genre']) ? $param['params']['genre'] : '');

        $isUpdated = (isset($param['params']['is_updated']) ? filter_var($param['params']['is_updated'], FILTER_VALIDATE_BOOLEAN) : FALSE);
        
        #jika pakai range date
        $showLog = (isset($param['params']['show_log']) ? $param['params']['show_log'] : FALSE);
        $parameter = [
            'id' => $id,
            'code' => $code,
            'slug' => $slug,
            'genre' => $title,
            'start_by_index' => $startNameIndex,
            'end_by_index' => $endNameIndex,
            'is_updated' => $isUpdated
        ];
        
        $genreList = MainModel::getDataListGenreManga($parameter);
        
        $errorCount = 0;
        $successCount = 0;
        if(count($genreList)){
            foreach($genreList as $genreList){
                    $conditions = [
                        'id_auto' => $genreList['id'].'-genreListManga',
                    ];
                    $MappingMongo = array(
                        'id_auto' => $genreList['id'].'-genreListManga',
                        'source_type' => 'genreList-Manga',
                        'slug' => $genreList['slug'],
                        'code' => $genreList['code'],
                        'genre' => Converter::__normalizeSummary($genreList['genre']),
                        'name_index' => $genreList['name_index'],
                        'keyword' => explode('-',$genreList['slug']),
                        'meta_title' => (Converter::__normalizeSummary(strtolower($genreList['genre']))),
                        'meta_keywords' => explode('-',$genreList['slug']),
                        'meta_tags' => explode('-',$genreList['slug']),
                        'cron_at' => $genreList['cron_at']
                    );
                    
                    $updateMongo = MainModelMongo::updateGenreListManga($MappingMongo, $this->mongo['collections_genre_list_manga'], $conditions, TRUE);
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
                            $error_id['response']['id'][$key] = $genreList['id']; #set id error generate
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
    
    // ============================= End GenerateGenreListManga Save To Mongo===========================
}