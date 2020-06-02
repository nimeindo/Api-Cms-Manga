<?php

namespace App\Console\Commands\Mongo;

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


class CronDetailMangaGenerateByDateMG extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CronDetailMangaGenerateByDateMG:CronDetailMangaGenerateByDateMGV1  {start_date} {end_date} {is_update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron untuk generate data CronDetailMangaGenerateByDateMGV1';

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
        $filename = $path_log.'/CronDetailMangaGenerateByDateMG.json';
        #get file log last date generate
        if(file_exists($filename)) $content = file_get_contents($filename);
        if($isUpdate){
            $date = date('Y-m-d H:i:s');
            $starTimestamp = strtotime($date.'-'.'1 hours');
            $startDate = date('Y-m-d H:i:s', $starTimestamp);
            // $endTimestamp = strtotime($date.'+'.'10 hours');
            // $EndDate = date('Y-m-d H:i:s', $endTimestamp);
            $EndDate = '';
            
        }
        
        $response = [];
        $param = [
            'start_date' => $startDate,
            'end_date' => $EndDate
        ];
        
        $listManga = MainModel::getDataListManga($param);
        
        $status = "Complete";
        $i = 0;
        $dataNotSave = array();
        $TotalHit = (count($listManga));
        foreach($listManga as $listManga){
            $listDataManga = [
                'params' => [
                    'id_list_manga' => $listManga['id'],
                    'show_log' => TRUE
                ]
            ];
            // dd($listDataManga);
            try{
                $data = $this->DetailMangaController->generateDetailManga(NULL,$listDataManga);
                
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