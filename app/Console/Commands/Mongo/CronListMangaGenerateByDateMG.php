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


class CronListMangaGenerateByDateMG extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CronListMangaGenerateByDateMG:CronListMangaGenerateByDateMGV1 {start_date} {end_date} {is_update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron untuk generate data CronListMangaGenerateByDateMG';

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
        $startDate = $this->argument('start_date');
        $EndDate = $this->argument('end_date');
        $isUpdate = filter_var($this->argument('is_update'), FILTER_VALIDATE_BOOLEAN);

        $path_log = base_path('storage/logs/generate/Mongo');
        $filename = $path_log.'/CronListMangaGenerateByDateMG.json';
        #get file log last date generate
        if(file_exists($filename)) $content = file_get_contents($filename);
        if($isUpdate){
            $Jam =env('TIME_ISUPDATE_MONGO', '1');
            $date = date('Y-m-d H:i:s');
            $starTimestamp = strtotime($date.'-'.$Jam.' hours');
            $startDate = date('Y-m-d H:i:s', $starTimestamp);
            $EndDate = '';
        }
        $response = [];
        $status = "Complete";
        $ListAnime = [
            'params' => [
                'start_date' => $startDate,
                'end_date' => $EndDate,
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