<?php

namespace App\Console\Commands\Mongo;

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


class CronImageChapterMangaByDateMG extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CronImageChapterMangaByDateMG:CronImageChapterMangaByDateMGV1  {start_date} {end_date} {is_update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron untuk generate data CronImageChapterMangaByDateMG';

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
        $startDate = $this->argument('start_date');
        $EndDate = $this->argument('end_date');
        $isUpdate = filter_var($this->argument('is_update'), FILTER_VALIDATE_BOOLEAN);

        $path_log = base_path('storage/logs/generate/mysql');
        $filename = $path_log.'/CronImageChapterMangaByDateMG.json';
        #get file log last date generate
        if(file_exists($filename)) $content = file_get_contents($filename);

        if($isUpdate){
            $date = date('Y-m-d H:i:s');
            $newtimestamp = strtotime($date.'-'.'3 hours');
            $startDate = date('Y-m-d H:i:s', $newtimestamp);
            $EndDate = '';
        }
         
        $response = [];
        $param = [
            'code' => '',
            'start_date' => $startDate,
            'end_date' => $EndDate
        ];
        $ChapterManga = MainModel::getDataChapterManga($param);
        // dd($ChapterManga);
        $status = "Complete";
        $i = 0;
        $dataNotSave = array();
        $TotalHit = (count($ChapterManga));
        foreach($ChapterManga as $ChapterManga){
            $listDataChapter = [
                'params' => [
                    'X-API-KEY' => env('X_API_KEY',''),
                    'id_chapter' => $ChapterManga['id'],
                    'show_log' => TRUE
                ]
            ];
            try{
                $data = $this->ImageChapterMangaController->GenerateChapterMangaAndImage(NULL,$listDataChapter);
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