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

class ImageChapterMangaController extends Controller
{
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
        $goutteClient = new GoutteClient();
        $goutteClient->setClient($client);
        $crawler = $goutteClient->request('GET', $BASE_URL_LIST);
        $response = $goutteClient->getResponse();
        $status = $response->getStatusCode();
        
        if($status === 200){
            $SubImage = $crawler->filter('#all > .img-responsive')->each(function ($node,$i) {
                $image = $node->filter('img')->attr('src');
                $altSub = $node->filter('img')->attr('alt');
                $page = substr($altSub, strrpos($altSub, '-' )+1);
                $SubImage = [
                    'image' => $image,
                    'page' => $page,
                    'alt' => $altSub
                ];
                return $SubImage; 
            });
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
                        
                    }else{
                        $chapterManga = MainModel::getDataChapterManga($paramIdListChapter);
                        $idChapterManga = (empty($chapterManga)) ? 0 : $chapterManga[0]['id'];
                        $paramIdImageChapter['code'] = $codeImageChapter;
                        $checkExistImage = MainModel::getDataChapterManga($paramIdImageChapter);
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
                            $conditions['id'] = $checkExist[0]['id'];
                            $Update = array(
                                'id_chapter' => $idChapterManga,
                                'code' => $codeImageChapter,
                                'slug' => $slugImageChapter,
                                'image' => $image,
                                "no_frame" => $no_frame,
                                'page' => $page,
                                'cron_at' => Carbon::now()->format('Y-m-d H:i:s')
                            );
                            $LogSave [] =  "Data Update - ".$slugChapter;
                            $save = MainModel::updateImageChapterMangaMysql($Update,$conditions);
                            
                        }
                    }
                    $no_frame++;
                }
                return ResponseConnected::Success("Image Chapter Manga", $save, $LogSave, $awal);
            }else{
                return ResponseConnected::PageNotFound("Image Chapter Manga","Page Not Found.", $awal);
            }
        }else{
            return ResponseConnected::PageNotFound("Image Chapter Manga","Page Not Found.", $awal);
        }
    }
}