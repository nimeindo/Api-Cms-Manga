<?php

namespace App\Console\Commands\Mongo;

use Illuminate\Console\Command;
use \Illuminate\Http\Request;
use \Illuminate\Http\Response;

#Load Controller
use App\Http\Controllers\Mangaid\ListMangaController;

/*Load Component*/
use Cache;
use Config;
use Carbon\Carbon;

#Load Models V1
use App\Models\V1\MainModel as MainModel;


class CronListMangaGenerateByAlfabetMG extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CronListMangaGenerateByAlfabetMG:CronListMangaGenerateByAlfabetMGV1 {start_by_index} {end_by_index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron untuk generate data CronListMangaGenerateByAlfabetMG';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->ListMangaController = new ListMangaController();
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
        $filename = $path_log.'/CronListMangaGenerateByAlfabetMG.json';
        #get file log last date generate
        if(file_exists($filename)) $content = file_get_contents($filename);
        
        $response = [];
        $status = "Complete";
        $ListAnime = [
            'params' => [
                'start_name_index' => $startByIndex,
                'end_name_index' => $EndByIndex,
                'show_log' => TRUE
            ]
        ];
        try{
            $data = $this->ListMangaController->ListMangaGenerate(NULL,$ListAnime);
            echo json_encode($data)."\n\n";
        }catch(\Exception $e){
            $status = 'Not Complete';
        }
        $response['Hit_date'] =  Carbon::now()->format(DATE_ATOM);
        $response['status'] = $status;
        echo json_encode($response)."\n\n";
    }
}