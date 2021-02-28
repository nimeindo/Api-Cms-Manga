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

class ImageChapterMangaController extends Controller
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
     * @desc [ImageChapterMangaScrap]
     */
    // ================================== ImageChapterMangaScrap Save to Mysql =========================
    public function ImageChapterMangaScrap(Request $request = NULL, $params = NULL){
        $awal = microtime(true);
        if(!empty($request) || $request != NULL){
            $param = $params; # get param dari populartopiclist atau dari cron
            if(is_null($params)) $param = $request->all();
            $ApiKey = $request->header("X-API-KEY");
            $chapterHref = (isset($param['params']['chapter_href']) ? ($param['params']['chapter_href']) : '');
        }
        if(!empty($params) || $params != NULL){
            $chapterHref = (isset($params['params']['chapter_href']) ? ($params['params']['chapter_href']) : '');
            $ApiKey = (isset($params['params']['X-API-KEY']) ? ($params['params']['X-API-KEY']) : '');
        }
        $Users = MainModel::getUser($ApiKey);
        if(!empty($Users)){
            $ConfigController = new ConfigController();
            $BASE_URL = $ConfigController->BASE_URL_MANGA;
            $CHAPTER_HREF = $chapterHref;
            return $this->ImageChapterMangaScrapValue($BASE_URL, $CHAPTER_HREF, $awal);
        }
    }
    
    public function ImageChapterMangaScrapValue($BASE_URL, $CHAPTER_HREF, $awal){
        $BASE_URL_LIST = $CHAPTER_HREF;
        $client = new Client(['cookies' => new FileCookieJar('cookies.txt')]);
        $client->getConfig('handler')->push(CloudflareMiddleware::create());
        // $client->followRedirects(true);
        $goutteClient = new GoutteClient(['allow_redirects' => true]);
        $goutteClient->setClient($client);
        $crawler = $goutteClient->request('GET', $BASE_URL_LIST,['allow_redirects' => true]);
        $response = $goutteClient->getResponse();
        
        $status = $response->getStatusCode();
        
        if($status === 200){
            // for($i = 0; $i < 100 ; $i++){
                $SubImage = $crawler->filter('#all > .img-responsive')->each(function ($node,$i) {
                    $image = $node->filter('img')->attr('src');
                    $altSub = $node->filter('img')->attr('alt');
                    $page = substr($altSub, strrpos($altSub, '-' )+1);
                    $getHttps = substr($image, 0,1); 
                    if($getHttps === '/'){
                        $image = 'https:'.$image;
                    }
                    
                    $SubImage = [
                        'image' => $image,
                        'page' => $page,
                        'alt' => $altSub
                    ];
                    return $SubImage; 
                });
            // }
            
            
            if($SubImage){
                $no_frame = 1;
                foreach($SubImage as $valueImage){
                    $slugChapter = str_replace('/','-',substr($CHAPTER_HREF, strrpos($CHAPTER_HREF, 'manga/' )+6));
                    $codeChapter = md5($slugChapter);
                    $image       = isset($valueImage['image']) ? $valueImage['image'] : '' ;
                    $page        = isset($valueImage['page']) ? $valueImage['page'] : '' ;
                    $slugImageChapter = isset($valueImage['alt']) ? Str::slug($valueImage['alt']) : '' ;
                    
                    $codeImageChapter = md5($slugImageChapter);
                    $paramIdListChapter['code'] = $codeChapter;
                    $checkExist = MainModel::getDataChapterManga($paramIdListChapter);
                    if(empty($checkExist)){
                        $LogSave [] = "Data Not Exist Please Chek Your Slug Chapter - ".$slugChapter;
                        $save = "Data Empty";
                    }else{
                        $chapterManga = MainModel::getDataChapterManga($paramIdListChapter);
                        $idChapterManga = (empty($chapterManga)) ? 0 : $chapterManga[0]['id'];
                        $paramIdImageChapter['code'] = $codeImageChapter;
                        $checkExistImage = MainModel::getDataImageChapterManga($paramIdImageChapter);
                        // if($slugImageChapter=='blush-dc-himitsu-chapter-34-page-8'){
                        //     dd($codeImageChapter);
                        // }
                        
                        if(empty($checkExistImage)){
                            $Input = array(
                                'id_chapter' => $idChapterManga,
                                'code' => $codeImageChapter,
                                'slug' => $slugImageChapter,
                                'image' => $image,
                                "no_frame" => $no_frame,
                                'page' => $page,
                                'cron_at' => Carbon::now()->format('Y-m-d H:i:s')
                            );
                            $LogSave [] = "Data Save - ".$slugImageChapter;
                            $save = MainModel::insertImageChapterMangaMysql($Input);
                        }else{
                            $conditions['id'] = $checkExistImage[0]['id'];
                            $Update = array(
                                'id_chapter' => $idChapterManga,
                                'code' => $codeImageChapter,
                                'slug' => $slugImageChapter,
                                'image' => $image,
                                "no_frame" => $no_frame,
                                'page' => $page,
                                // 'cron_at' => Carbon::now()->format('Y-m-d H:i:s')
                            );
                            
                            $LogSave [] =  "Data Update - ".$slugImageChapter;
                            $dataUpdate[] = $Update;
                            $save = MainModel::updateImageChapterMangaMysql($Update,$conditions);
                            
                        }
                    }

                    $no_frame++;
                    
                }
                
                return ResponseConnected::Success("Image Chapter Manga", $save, $LogSave, $awal);
            }else{
                echo "Image Chapter Manga Page Not Found.";
                dd($SubImage);
                return ResponseConnected::PageNotFound("Image Chapter Manga","Page Not Found.", $awal);
            }
        }else{
            echo "Image Chapter Manga Page Not Found.";
            dd($BASE_URL_LIST);
            return ResponseConnected::PageNotFound("Image Chapter Manga","Page Not Found.", $awal);
        }
    }
    // ============================= End ImageChapterMangaScrap Save To Mongo===========================

    /**
     * @author [Prayugo]
     * @create date 2020-06-02 00:44:09
     * @modify date 2020-06-02 00:44:09
     * @desc [CekExisData]
     */
    // ================================== CekExisData Save to Mysql =========================
    public function checkExistImageChapter(){
        $checkExist = MainModel::checkExistImageChapter($param=[]);
        dd($checkExist);
        $notExis ='';
        foreach($checkExist as $dataExis){
            $notExis .=$dataExis['id'].',';
        }
        echo $notExis;
    }
    public function checkIdChapterOnImageChapter(){
        $checkExist = MainModel::checkIdChapterOnImageChapter($param=[]);
        // dd($checkExist);
        $notExis1 ='';
        $notExis2 ='';
        $notExis3 ='';
        foreach($checkExist as $key => $dataExis){
            if($key < 1000){
                $notExis1 .=$dataExis['id_chapter'].',';
            }elseif($key > 1000 && $key < 2000 ){
                $notExis2 .=$dataExis['id_chapter'].',';
            }else{
                $notExis3 .=$dataExis['id_chapter'].',';
            }
            
        }
        echo $notExis1.'<br>';
        echo $notExis2.'<br>';
        echo $notExis3.'<br>';
    }
    // ============================= End CekExisData Save To Mongo===========================

    public function GenerateChapterMangaAndImage(Request $request = NULL, $params = NULL){

        $param = $params; # get param dari populartopiclist atau dari cron
        if(is_null($params)) $param = $request->all();

        $id = (isset($param['params']['id']) ? $param['params']['id'] : NULL);
        $idListManga = (isset($param['params']['id_list_manga']) ? $param['params']['id_list_manga'] : NULL);
        $idChapter = (isset($param['params']['id_chapter']) ? $param['params']['id_chapter'] : NULL);
        $idDetailManga = (isset($param['params']['id_detail_manga']) ? $param['params']['id_detail_manga'] : NULL);
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
            'id_chapter' => $idChapter,
            'id_detail_manga' => $idDetailManga,
            'code' => $code,
            'slug' => $slug,
            'title' => $title,
            'start_date' => $startDate, 
            'end_date' => $endDate,
            'is_updated' => $isUpdated
        ];
        $getChapterData = MainModel::getDataChapterManga($parameter);
        $errorCount = 0;
        $successCount = 0;
        $totalChapetr = count($getChapterData);
        if(count($getChapterData)){
            foreach($getChapterData as $valueGetChapter){
                $conditions = [
                    'id_auto' => $valueGetChapter['id'].'-chapterManga',
                ];
                $param = [
                    'id_chapter' => $valueGetChapter['id']
                ];
                $getImageChapter = MainModel::getDataImageChapterManga($param);
                $dataImage = [];
                foreach($getImageChapter as $valueImageChapter){
                    $dataImage[] = [
                        'id' => $valueImageChapter['id'],
                        'slug' => $valueImageChapter['slug'],
                        'image' => $valueImageChapter['image'],
                        'page' => $valueImageChapter['page'],
                        'no_frame' => $valueImageChapter['no_frame'],
                    ];
                }
                
                $paramDetail = [
                    'id' => $valueGetChapter['id_detail_manga']
                ];
            
                $getDetailManga = MainModel::getDataDetailManga($paramDetail);
                $status = '';
                $rating = '';
                $synopsis = '';
                $genre = '';
                foreach($getDetailManga as $valueDetail){
                    $status = $valueDetail['status'];
                    $rating = $valueDetail['rating'];
                    $synopsis = $valueDetail['synopsis'];
                    $genre = $valueDetail['genre'];
                }
                $title = str_replace(" ",'-',$valueGetChapter['slug']);
                $MappingMongo = array(
                    'id_auto' => $valueGetChapter['id'].'-chapterManga',
                    'id_chapter' => $valueGetChapter['id'],
                    'id_list_manga' => $valueGetChapter['id_list_manga'],
                    'id_detail_manga' => $valueGetChapter['id_detail_manga'],
                    'source_type' => 'chapter-Manga',
                    'code' => $valueGetChapter['code'],
                    'slug' => $valueGetChapter['slug'],
                    'title' => Converter::__normalizeSummary($title),
                    'synopsis' => $synopsis,
                    'page_image_chapter' => $dataImage,
                    'status' => $status,
                    'rating' => $rating,
                    'chapter_total' => $totalChapetr,
                    'genre' => explode('|',substr(trim($genre),0,-1)),
                    'keyword' => explode('-',$valueGetChapter['slug']),
                    'meta_title' => (Converter::__normalizeSummary(strtolower($title))),
                    'meta_keywords' => explode('-',$valueGetChapter['slug']),
                    'meta_tags' => explode('-',$valueGetChapter['slug']),
                    'cron_at' => $valueGetChapter['cron_at']
                );
                
                $updateMongo = MainModelMongo::updateChapterManga($MappingMongo, $this->mongo['collections_chapter_manga'], $conditions, TRUE);
                
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
                        $error_id['response']['id'][$key] = $detailAnime['id']; #set id error generate
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
    
}
