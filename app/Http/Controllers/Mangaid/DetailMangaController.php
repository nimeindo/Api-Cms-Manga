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

class DetailMangaController extends Controller
{
    function __construct(){
        $this->mongo = Config::get('mongo');
    }


    public function DetailMangaScrap(Request $request = NULL, $params = NULL){
        $awal = microtime(true);
        if(!empty($request) || $request != NULL){
            $param = $params; # get param dari populartopiclist atau dari cron
            if(is_null($params)) $param = $request->all();
            $ApiKey = $request->header("X-API-KEY");
            $detailHeref = (isset($param['params']['detail_href']) ? ($param['params']['detail_href']) : '');
        }
        if(!empty($params) || $params != NULL){
            $detailHeref = (isset($params['params']['detail_href']) ? ($params['params']['detail_href']) : '');
            $ApiKey = (isset($params['params']['X-API-KEY']) ? ($params['params']['X-API-KEY']) : '');
        }
        $Users = MainModel::getUser($ApiKey);
        if(!empty($Users)){
            $ConfigController = new ConfigController();
            $BASE_URL = $ConfigController->BASE_URL_MANGA;
            $DETAIL_HREF = $detailHeref;
            return $this->DetailMangaScrapValue($BASE_URL, $DETAIL_HREF, $awal);
        }
    }
    public function DetailMangaScrapValue($BASE_URL, $DETAIL_HREF, $awal){
        $BASE_URL_LIST = $DETAIL_HREF;
        $client = new Client(['cookies' => new FileCookieJar('cookies.txt')]);
        $client->getConfig('handler')->push(CloudflareMiddleware::create());
        $goutteClient = new GoutteClient();
        $goutteClient->setClient($client);
        $crawler = $goutteClient->request('GET', $BASE_URL_LIST);
        $response = $goutteClient->getResponse();
        $status = $response->getStatusCode();
        if($status === 200){
            // Get Detail
            $SubImage = $crawler->filter('.row > .col-sm-3 > .boxed')->each(function ($node,$i) {
                $image = $node->filter('img')->attr('src');
                return $image;

            });

            $SubDetail = $crawler->filter('.row > .col-sm-9 > .dl-horizontal')->each(function ($node,$i) {
                $subListDetail = $node->filter('dt')->each(function ($node,$i) {
                    $subListDetail = $node->filter('dt')->text('Default text content');
                    return $subListDetail;
                });
                $subDetailValue = $node->filter('dd')->each(function ($node,$i) {
                    $subDetailValue = $node->filter('dd')->text('Default text content');
                    return trim($subDetailValue);
                });
                $ListDetail = [];
                for($i = 0; $i <count($subListDetail); $i++){

                    $ListDetail[str_replace(" ","",ucwords(str_replace('(s)','',$subListDetail[$i])))] = $subDetailValue[$i];
                }
                return $ListDetail;
            });

            $SubDeskripsi = $crawler->filter('.row > .col-lg-12 > .well')->each(function ($node,$i) {
                $Detail = $node->filter('p')->text('Default text content');
                return $Detail;
            });

            // Get Chapter
            $SubChapter = $crawler->filter('.row > .col-lg-12 > .chapters > li')->each(function ($node,$i) {
                $chapter = $node->filter('a')->text('Default text content');
                $hrefChapter = $node->filter('a')->attr('href');
                $date = $node->filter('.action')->text('Default text content');
                $slug = str_replace('/','-',substr($hrefChapter, strrpos($hrefChapter, 'manga/' )+6));
                $subchapter = [
                    'chapter' => $chapter,
                    'href' => $hrefChapter,
                    'slug' => $slug,
                    'date' => trim($date)
                ];
                return $subchapter;
            });

            $SubDetail = [
                'Image' => $SubImage,
                'ListDetail' => $SubDetail,
                'Deskripsi' => $SubDeskripsi,
                'Chapter' => $SubChapter
            ];
            // dd($SubDetail['Chapter']);
            if($SubChapter){
                {//Get Detail

                    $deskripsi = isset($SubDetail['Deskripsi'][0]) ? Converter::__normalizeSummary($SubDetail['Deskripsi'][0]) : '';
                    $image = isset($SubDetail['Image'][0]) ? ($SubDetail['Image'][0]) : '';
                    $tipe = isset($SubDetail['ListDetail'][0]['Type']) ? trim($SubDetail['ListDetail'][0]['Type']) : '';
                    $status = isset($SubDetail['ListDetail'][0]['Status']) ? trim($SubDetail['ListDetail'][0]['Status']) : '';
                    $author = isset($SubDetail['ListDetail'][0]['Author']) ? explode(",",(trim(preg_replace('/\s+/','',$SubDetail['ListDetail'][0]['Author'])))) : '';
                    $released = isset($SubDetail['ListDetail'][0]['Released']) ? trim($SubDetail['ListDetail'][0]['Released']) : '';
                    $subCategories = isset($SubDetail['ListDetail'][0]['Categories']) ? explode(",",(trim(preg_replace('/\s+/','',$SubDetail['ListDetail'][0]['Categories'])))) : '';
                    $view = isset($SubDetail['ListDetail'][0]['Views']) ? $SubDetail['ListDetail'][0]['Views'] : null;
                    $rating = isset($SubDetail['ListDetail'][0]['Rating']) ? Converter::__trimRating($SubDetail['ListDetail'][0]['Rating']) : '';
                    $categorie = "";
                    $authors = "";
                    if(!empty($subCategories)){
                        for($i = 0; $i < count($subCategories) ; $i++){
                            $categorie .= $subCategories[$i].'|';
                        }
                    }
                    if(!empty($author)){
                        for($i = 0; $i < count($author) ; $i++){
                            $authors .= $author[$i].'|';
                        }
                    }


                    $genre = Converter::__normalizeSummary($categorie);
                    $slugDetail = Str::slug(substr($DETAIL_HREF, strrpos($DETAIL_HREF, 'manga/' )+6));
                    $title      = str_replace('-',' ',ucwords($slugDetail));
                    $codeDetail = md5($slugDetail);
                    $paramCodeDetail['code'] = $codeDetail;
                    $paramCodeListManga['code'] = $codeDetail;
                    $NameIndexVal = substr($title, 0,1);
                    $NameIndexVal = !ctype_alpha($NameIndexVal) ? '##' : '#'.strtoupper($NameIndexVal);

                    {#save to listManga cek jika list manga tidak ada maka akan di tambahkan
                        $listManga = MainModel::getDataListManga($paramCodeListManga);
                        $idListManga = (empty($listManga)) ? 0 : $listManga[0]['id'];
                        if(empty($listManga) || $idListManga == 0){
                            if(empty($listManga)){
                                $paramInput = [
                                    'code' => $codeDetail,
                                    "href" => $DETAIL_HREF,
                                    "slug" => $slugDetail,
                                    "title" => $title,
                                    'name_index' => $NameIndexVal,
                                    'cron_at' => Carbon::now()->format('Y-m-d H:i:s')
                                ];
                                $LogSave [] = "Data Save - ".$title;
                                $save = MainModel::insertListMangaMysql($paramInput);
                            }
                        }
                        $listManga = MainModel::getDataListManga($paramCodeListManga);
                        $idListManga = (empty($listManga)) ? 0 : $listManga[0]['id'];
                    }# end save to listManga

                    {#save to Detail Manga
                        $checkExist = MainModel::getDataDetailManga($paramCodeDetail);
                        $LogSave = array();
                        if(empty($checkExist)){
                            $Input = array(
                                'code' => $codeDetail,
                                'id_list_manga' => $idListManga,
                                'href' => $DETAIL_HREF,
                                'code' => $codeDetail,
                                'slug' => $slugDetail,
                                'title' => $title,
                                'image' => $image,
                                'tipe' => $tipe,
                                'genre' => $genre,
                                'status' => $status,
                                "author" => $authors,
                                'release_date' => $released,
                                'views' => $view,
                                'rating' => $rating,
                                'synopsis' => $deskripsi,
                                'cron_at' => Carbon::now()->format('Y-m-d H:i:s')
                            );

                            $save = MainModel::insertDetailMangaMysql($Input);
                            $detailManga = MainModel::getDataDetailManga($paramCodeDetail);
                            $idDetailManga = (empty($detailManga)) ? 0 : $detailManga[0]['id'];
                            if(count($SubDetail['Chapter']) > 0 ){
                                $LogSave = $this->saveChapter($SubDetail,$idDetailManga,$idListManga);
                            }else{
                                $LogSave = 'Tidak ada Chapter Yang di simpan hanya data detail yang di simpan';
                            }
                        }else{
                            $conditions['id'] = $checkExist[0]['id'];
                            $Update = array(
                                'code' => $codeDetail,
                                'id_list_manga' => $idListManga,
                                'href' => $DETAIL_HREF,
                                'code' => $codeDetail,
                                'slug' => $slugDetail,
                                'title' => $title,
                                'image' => $image,
                                'tipe' => $tipe,
                                'genre' => $genre,
                                'status' => $status,
                                "author" => $authors,
                                'release_date' => $released,
                                'views' => $view,
                                'rating' => $rating,
                                'synopsis' => $deskripsi,
                                'cron_at' => Carbon::now()->format('Y-m-d H:i:s')
                            );
                            // dd($Update);
                            $save = MainModel::updateDetailMangaMysql($Update,$conditions);
                            $detailManga = MainModel::getDataDetailManga($paramCodeDetail);
                            $idDetailManga = (empty($detailManga)) ? 0 : $detailManga[0]['id'];
                            if(count($SubDetail['Chapter']) > 0 ){
                                $LogSave = $this->saveChapter($SubDetail,$idDetailManga,$idListManga);
                            }else{
                                $LogSave = 'Tidak ada Chapter Yang di simpan hanya data detail yang di simpan';
                            }
                        }
                    }#end save detail manga
                }
                return ResponseConnected::Success("Detail Manga", $save, $LogSave, $awal);
            }else{
                return ResponseConnected::PageNotFound("Detail Manga","Page Not Found.", $awal);
            }

        }else{
            return ResponseConnected::PageNotFound("Detail Manga","Page Not Found.", $awal);
        }
    }

    public static function saveChapter($SubDetail,$idDetailManga,$idListManga){
        {//Get chapter
            foreach($SubDetail['Chapter']  as $valueChapter){
                $chapter = trim($valueChapter['chapter']);
                $href = ($valueChapter['href']);
                $slugChapter = ($valueChapter['slug']);
                $codeChapter = md5($slugChapter);
                $datePublish = date('Y-m-d',strtotime($valueChapter['date']));
                $paramIdListChapter['code'] = $codeChapter;
                $checkExist = MainModel::getDataChapterManga($paramIdListChapter);

                if(empty($checkExist)){
                    $Input = array(
                        'id_detail_manga' => $idDetailManga,
                        'id_list_manga' => $idListManga,
                        "code" => $codeChapter,
                        'slug' => $slugChapter,
                        'chapter' => $chapter,
                        'date_publish' => $datePublish,
                        'chapter_href' => $href,
                        'cron_at' => Carbon::now()->format('Y-m-d H:i:s')
                    );
                    $LogSave [] = "Data Save - ".$slugChapter;
                    $save = MainModel::insertChapterMangaMysql($Input);
                }else{
                    $conditions['id'] = $checkExist[0]['id'];
                    $Update = array(
                        'id_detail_manga' => $idDetailManga,
                        'id_list_manga' => $idListManga,
                        "code" => $codeChapter,
                        'slug' => $slugChapter,
                        'chapter' => $chapter,
                        'date_publish' => $datePublish,
                        'chapter_href' => $href,
                        // 'cron_at' => Carbon::now()->format('Y-m-d H:i:s')
                    );

                    $LogSave [] =  "Data Update - ".$slugChapter;
                    $save = MainModel::updateChapterMangaMysql($Update,$conditions);
                }
            }
        }
        return $LogSave;
    }



    // ================================== generateDetailManga Save to Mongo =========================
    public function generateDetailManga(Request $request = NULL, $params = NULL){
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

        #jika pakai log
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
                $ListChapterInDetail = MainModel::getDataChapterManga($param);
                $dataChp = [];
                foreach($ListChapterInDetail as $ListChapter){
                    $dataChp[] = array(
                        'id_chapter' => $ListChapter['id'],
                        'slug_chapter' => $ListChapter['slug'],
                        'chapter' => $ListChapter['chapter'],
                        'page' => filter_var($ListChapter['chapter'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                        'date_publish' => $ListChapter['date_publish'],
                    );
                }
                // menyortir dari besar ke kecil
                usort($dataChp, function($a, $b) {
                    return $b['page'] <=> $a['page'];
                });

                // dd($dataChp);
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
                    'chapter' => $dataChp,
                    'image' => $valueDetailManga['image'],
                    'status' => $valueDetailManga['status'],
                    'chapter_total' => count($dataChp),
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

                $updateMongo = MainModelMongo::updateDetailListManga($MappingMongo, $this->mongo['use_collection_detail_manga'], $conditions, TRUE);

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

}
