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


class CronDetailMangaGenerateByAlfabetMG extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CronDetailMangaGenerateByAlfabetMG:CronDetailMangaGenerateByAlfabetMGV1  {start_by_index} {end_by_index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron untuk generate data CronDetailMangaGenerateByAlfabetMG';

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
        $startByIndex = $this->argument('start_by_index');
        $EndByIndex = $this->argument('end_by_index');

        $path_log = base_path('storage/logs/generate/mongo');
        $filename = $path_log.'/CronDetailMangaGenerateByAlfabetMGV1.json';
        #get file log last date generate
        if(file_exists($filename)) $content = file_get_contents($filename);
        
        $response = [];
        $param = [
            'code' => '',
            'start_by_index' => substr($startByIndex,0,1),
            'end_by_index' => substr($EndByIndex,0,1),
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
            
            try{
                $data = $this->DetailMangaController->generateDetailManga(NULL,$listDataManga);
                echo json_encode($data)."\n\n";
                if($data['success'] == 0){
                    $dataNotSave[] = array(
                        'Title' => $listManga['title'],
                        'Index' => $listManga['name_index'],
                        'id' => $listManga['id']
                    );
                    $status = 'Not Complete';
                }else{
                    $i++;
                }
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
        $response['Total_Data_Save'] = $i.' - Keyword index'.' - '.$startByIndex.':'.$EndByIndex;
        $response['Total_Data_Not_Save'] = $notSave.' - Keyword index'.' - '.$startByIndex.':'.$EndByIndex;
        $response['Data_Not_Save'] = $dataNotSave;
        $response['status'] = $status;
        echo json_encode($response)."\n\n";
    }
}