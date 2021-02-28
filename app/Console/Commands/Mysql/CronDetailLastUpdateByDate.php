<?php

namespace App\Console\Commands\Mysql;

use Illuminate\Console\Command;
use \Illuminate\Http\Request;
use \Illuminate\Http\Response;

#Load Controller
use App\Http\Controllers\Mangaid\DetailMangaController;

/*Load Component*/
use Cache;
use Config;
use Carbon\Carbon;

#Load Models V1
use App\Models\V1\MainModel as MainModel;


class CronDetailLastUpdateByDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CronDetailLastUpdateByDate:CronDetailLastUpdateByDateV1  {start_date} {end_date} {is_update}' ;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron untuk generate data CronDetailLastUpdateByDateV1';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->DetailMangaController = new DetailMangaController();
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
        $filename = $path_log.'/CronDetailLastUpdateByDateV1.json';
        #get file log last date generate
        if(file_exists($filename)) $content = file_get_contents($filename);
        
        if($isUpdate){
            $Jam = env('TIME_ISUPDATE_MYSQL', '1');
            $date = date('Y-m-d H:i:s');
            $starTimestamp = strtotime($date.'-'.$Jam.' hours');
            $startDate = date('Y-m-d H:i:s', $starTimestamp);
            $EndDate = '';
        }
        
        $response = [];
        $param = [
            'start_date' => $startDate,
            'end_date' => $EndDate
        ];
        $LastChapter = MainModel::getDataLastUpdateChapterManga($param);
        foreach($LastChapter as $LastChapterAs){
            $params = [
                'id' => $LastChapterAs['id_list_manga'],
            ];
            
            $listManga = MainModel::getDataListManga($params);
            
            $status = "Complete";
            $i = 0;
            $dataNotSave = array();
            $TotalHit = (count($listManga));
            foreach($listManga as $listManga){
                $listDataChapter = [
                    'params' => [
                        'X-API-KEY' => env('X_API_KEY',''),
                        'detail_href' => $listManga['href']
                    ]
                ];
                try{
                    $data = $this->DetailMangaController->DetailMangaScrap(NULL,$listDataChapter);
                    
                    echo json_encode($data)."\n\n";
                    $i++;
                }catch(\Exception $e){
                    $dataNotSave[] = array(
                        'Title' => $listManga['title'],
                        'Index' => $listManga['name_index'],
                        'id' => $listManga['id']
                    );
                    $status = 'Not Complete';
                    
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