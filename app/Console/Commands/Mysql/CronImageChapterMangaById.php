<?php

namespace App\Console\Commands\Mysql;

use Illuminate\Console\Command;
use \Illuminate\Http\Request;
use \Illuminate\Http\Response;

#Load Controller
use App\Http\Controllers\Mangaid\ImageChapterMangaController;

/*Load Component*/
use Cache;
use Config;
use Carbon\Carbon;

#Load Models V1
use App\Models\V1\MainModel as MainModel;


class CronImageChapterMangaById extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CronImageChapterMangaById:CronImageChapterMangaByIdV1 {ID_Chapter_First} {ID_Chapter_End} {ID_Chapter_Custom}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron untuk generate data CronImageChapterMangaById';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->ImageChapterMangaController = new ImageChapterMangaController();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){
        $IDChapterFirst = ($this->argument('ID_Chapter_First'));
        $IDChapterEnd = ($this->argument('ID_Chapter_End')) ? $this->argument('ID_Chapter_End') : $IDChapterFirst;
        $IDChapter = ($this->argument('ID_Chapter_Custom')) ? explode(',',$this->argument('ID_Chapter_Custom')) : False ;
        // $showLog = filter_var($this->argument('show_log'), FILTER_VALIDATE_BOOLEAN);
        $path_log = base_path('storage/logs/generate/mysql');
        $filename = $path_log.'/CronImageChapterMangaById.json';
        #get file log last date generate
        if(file_exists($filename)) $content = file_get_contents($filename);
        
        $response = [];
        $status = "Complete";
        $i = 0;
        $dataNotSave = array();
        $TotalHit = 0;
        $hit = 0;
        if($IDChapter){
            $TotalHit = count($IDChapter);
            for($j = 0 ;$j <count($IDChapter) ;$j++ ){
                $param = [
                    'id_chapter' => $IDChapter[$j],
                ];
                $ChapterManga = MainModel::getDataChapterManga($param);
                // dd($ChapterManga);
                foreach($ChapterManga as $ChapterManga){
                    $getDataChapterManga = [
                        'params' => [
                            'X-API-KEY' => env('X_API_KEY',''),
                            'chapter_href' => $ChapterManga['chapter_href']
                        ]
                    ];
                    try{
                        $data = $this->ImageChapterMangaController->ImageChapterMangaScrap(NULL,$getDataChapterManga);
                        echo json_encode($data)."\n\n";
                        $i++;
                    }catch(\Exception $e){
                        $dataNotSave[] = array(
                            'chapter' => $ChapterManga['chapter'],
                            'chapter_href' => $ChapterManga['chapter_href'],
                            'id_chapter' => $ChapterManga['id']
                        );
                        $status = 'Not Complete';
                    }
                }
            }
        }else{
            for($j = $IDChapterFirst; $j <= $IDChapterEnd ; $j++){
                $TotalHit = $hit++;
                $param = [
                    'id_chapter' => $j,
                ];
                $ChapterManga = MainModel::getDataChapterManga($param);
                // dd($ChapterManga);
                foreach($ChapterManga as $ChapterManga){
                    $getDataChapterManga = [
                        'params' => [
                            'X-API-KEY' => env('X_API_KEY',''),
                            'chapter_href' => $ChapterManga['chapter_href']
                        ]
                    ];
                    try{
                        $data = $this->ImageChapterMangaController->ImageChapterMangaScrap(NULL,$getDataChapterManga);
                        echo json_encode($data)."\n\n";
                        $i++;
                    }catch(\Exception $e){
                        $dataNotSave[] = array(
                            'chapter' => $ChapterManga['chapter'],
                            'chapter_href' => $ChapterManga['chapter_href'],
                            'id' => $ChapterManga['id']
                        );
                        $status = 'Not Complete';
                    }
                }
            }
        }
        
        $notSave = $TotalHit - $i;
        
        $response['Total_hit'] = $TotalHit;
        $response['Hit_date'] =  Carbon::now()->format(DATE_ATOM);
        $response['Total_Data_Save'] = $i;
        $response['Total_Data_Not_Save'] = $notSave;
        $response['Data_Not_Save'] = $dataNotSave;
        $response['status'] = $status;
        echo json_encode($response)."\n\n";
    }
}