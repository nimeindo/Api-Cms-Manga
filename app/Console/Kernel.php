<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use Config;


class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        #MYSQL
        '\App\Console\Commands\Mysql\CronDetailMangaGenerateByAlfabet',
        '\App\Console\Commands\Mysql\CronDetailMangaGenerateByDate',
        '\App\Console\Commands\Mysql\CronDetailLastUpdateByDate',
        '\App\Console\Commands\Mysql\CronDetalIamageChapterLastUpdateByDate',
        '\App\Console\Commands\Mysql\CronLastUpdateChapterMangaGenerateAsc',
        '\App\Console\Commands\Mysql\CronLastUpdateChapterMangaGenerateDsc',
        '\App\Console\Commands\Mysql\CronListMangaGenerate',
        '\App\Console\Commands\Mysql\CronImageChapterMangaByDate',
        '\App\Console\Commands\Mysql\CronImageChapterMangaById',

        #MONGO
        '\App\Console\Commands\Mongo\CronDetailMangaGenerateByAlfabetMG',
        '\App\Console\Commands\Mongo\CronDetailMangaGenerateByDateMG',
        '\App\Console\Commands\Mongo\CronGenreMangaGenerateByAlfabetMG',
        '\App\Console\Commands\Mongo\CronListMangaGenerateByAlfabetMG',
        '\App\Console\Commands\Mongo\CronListMangaGenerateByDateMG',
        '\App\Console\Commands\Mongo\CronLastUpdateMangaGenerateByDateMG',
        '\App\Console\Commands\Mongo\CronImageChapterMangaByDateMG',
        '\App\Console\Commands\Mongo\CronImageChapterMangaByIdMG',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $config = Config::get('cron/cron_config');
        
        #START MYSQL
        #Cron Mangaid
        { #CRON ACTIVE
            #Mysql
            if($config['CronLastUpdateChapterMangaGenerateDsc']) {
                $schedule->command('CronLastUpdateChapterMangaGenerateDsc:CronLastUpdateChapterMangaGenerateDscV1 "2" "1" ""')
                    ->dailyAt("10:00")
                    ->appendOutputTo('/tmp/manga/CronLastUpdateChapterMangaGenerateDsc.log');
                $schedule->command('CronLastUpdateChapterMangaGenerateDsc:CronLastUpdateChapterMangaGenerateDscV1 "2" "1" ""')
                    ->dailyAt("13:00")
                    ->appendOutputTo('/tmp/manga/CronLastUpdateChapterMangaGenerateDsc.log');
                $schedule->command('CronLastUpdateChapterMangaGenerateDsc:CronLastUpdateChapterMangaGenerateDscV1 "2" "1" ""')
                    ->dailyAt("17:00")
                    ->appendOutputTo('/tmp/manga/CronLastUpdateChapterMangaGenerateDsc.log');
            }
            if($config['CronDetailLastUpdateByDate']) {
                $schedule->command('CronDetailLastUpdateByDate:CronDetailLastUpdateByDateV1  "" "" "TRUE"')
                    ->dailyAt("10:05")
                    ->appendOutputTo('/tmp/manga/CronDetailLastUpdateByDate.log');
                $schedule->command('CronDetailLastUpdateByDate:CronDetailLastUpdateByDateV1  "" "" "TRUE"')
                    ->dailyAt("13:05")
                    ->appendOutputTo('/tmp/manga/CronDetailLastUpdateByDate.log');
                $schedule->command('CronDetailLastUpdateByDate:CronDetailLastUpdateByDateV1  "" "" "TRUE"')
                    ->dailyAt("17:05")
                    ->appendOutputTo('/tmp/manga/CronDetailLastUpdateByDate.log');
            }
            if($config['CronImageChapterMangaByDate']) {
                $schedule->command('CronImageChapterMangaByDate:CronImageChapterMangaByDateV1  "" "" "TRUE"')
                    ->dailyAt("10:15")
                    ->appendOutputTo('/tmp/manga/CronImageChapterMangaByDate.log');
                $schedule->command('CronImageChapterMangaByDate:CronImageChapterMangaByDateV1  "" "" "TRUE"')
                    ->dailyAt("13:15")
                    ->appendOutputTo('/tmp/manga/CronImageChapterMangaByDate.log');
                $schedule->command('CronImageChapterMangaByDate:CronImageChapterMangaByDateV1  "" "" "TRUE"')
                    ->dailyAt("17:15")
                    ->appendOutputTo('/tmp/manga/CronImageChapterMangaByDate.log');
            }

            #Mongo
            if($config['CronDetailMangaGenerateByDateMG']) {
                $schedule->command('CronDetailMangaGenerateByDateMG:CronDetailMangaGenerateByDateMGV1  "" "" "TRUE"')
                    ->dailyAt("11:00")
                    ->appendOutputTo('/tmp/manga/CronDetailMangaGenerateByDateMG.log');
                $schedule->command('CronDetailMangaGenerateByDateMG:CronDetailMangaGenerateByDateMGV1  "" "" "TRUE"')
                    ->dailyAt("14:00")
                    ->appendOutputTo('/tmp/manga/CronDetailMangaGenerateByDateMG.log');
                $schedule->command('CronDetailMangaGenerateByDateMG:CronDetailMangaGenerateByDateMGV1  "" "" "TRUE"')
                    ->dailyAt("18:00")
                    ->appendOutputTo('/tmp/manga/CronDetailMangaGenerateByDateMG.log');
            }
            if($config['CronListMangaGenerateByDateMG']) {
                $schedule->command('CronListMangaGenerateByDateMG:CronListMangaGenerateByDateMGV1 "" "" "TRUE"')
                    ->dailyAt("11:03")
                    ->appendOutputTo('/tmp/manga/CronListMangaGenerateByDateMG.log');
                $schedule->command('CronListMangaGenerateByDateMG:CronListMangaGenerateByDateMGV1 "" "" "TRUE"')
                    ->dailyAt("14:03")
                    ->appendOutputTo('/tmp/manga/CronListMangaGenerateByDateMG.log');
                $schedule->command('CronListMangaGenerateByDateMG:CronListMangaGenerateByDateMGV1 "" "" "TRUE"')
                    ->dailyAt("18:03")
                    ->appendOutputTo('/tmp/manga/CronListMangaGenerateByDateMG.log');
            }
            if($config['CronImageChapterMangaByDateMG']) {
                $schedule->command('CronImageChapterMangaByDateMG:CronImageChapterMangaByDateMGV1  "" "" "TRUE"')
                    ->dailyAt("11:05")
                    ->appendOutputTo('/tmp/manga/CronImageChapterMangaByDateMG.log');
                $schedule->command('CronImageChapterMangaByDateMG:CronImageChapterMangaByDateMGV1  "" "" "TRUE"')
                    ->dailyAt("14:05")
                    ->appendOutputTo('/tmp/manga/CronImageChapterMangaByDateMG.log');
                $schedule->command('CronImageChapterMangaByDateMG:CronImageChapterMangaByDateMGV1  "" "" "TRUE"')
                    ->dailyAt("18:05")
                    ->appendOutputTo('/tmp/manga/CronImageChapterMangaByDateMG.log');
            }
            if($config['CronLastUpdateMangaGenerateByDateMG']) {
                $schedule->command('CronLastUpdateMangaGenerateByDateMG:CronLastUpdateMangaGenerateByDateMGV1 "" "" "TRUE"')
                    ->dailyAt("11:10")
                    ->appendOutputTo('/tmp/manga/CronLastUpdateMangaGenerateByDateMG.log');
                $schedule->command('CronLastUpdateMangaGenerateByDateMG:CronLastUpdateMangaGenerateByDateMGV1 "" "" "TRUE"')
                    ->dailyAt("14:10")
                    ->appendOutputTo('/tmp/manga/CronLastUpdateMangaGenerateByDateMG.log');
                $schedule->command('CronLastUpdateMangaGenerateByDateMG:CronLastUpdateMangaGenerateByDateMGV1 "" "" "TRUE"')
                    ->dailyAt("18:10")
                    ->appendOutputTo('/tmp/manga/CronLastUpdateMangaGenerateByDateMG.log');
            }



        }# End CRON ACTIVE


        if($config['CronDetailMangaGenerateByAlfabet']) {
            $schedule->command('CronDetailMangaGenerateByAlfabet:CronDetailMangaGenerateByAlfabetV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/manga/CronDetailMangaGenerateByAlfabet.log');
        }

        if($config['CronDetailMangaGenerateByDate']) {
            $schedule->command('CronDetailMangaGenerateByDate:CronDetailMangaGenerateByDateV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/manga/CronDetailMangaGenerateByDate.log');
        }
        if($config['CronDetalIamageChapterLastUpdateByDate']) {
            $schedule->command('CronDetalIamageChapterLastUpdateByDate:CronDetalIamageChapterLastUpdateByDateV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/manga/CronDetalIamageChapterLastUpdateByDate.log');
        }

        if($config['CronLastUpdateChapterMangaGenerateAsc']) {
            $schedule->command('CronLastUpdateChapterMangaGenerateAsc:CronLastUpdateChapterMangaGenerateAscV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/manga/CronLastUpdateChapterMangaGenerateAsc.log');
        }

        

        if($config['CronListMangaGenerate']) {
            $schedule->command('CronListMangaGenerate:CronListMangaGenerateV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/manga/CronListMangaGenerate.log');
        }

        

        if($config['CronImageChapterMangaById']) {
            $schedule->command('CronImageChapterMangaById:CronImageChapterMangaByIdV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/manga/CronImageChapterMangaById.log');
        }

        #MONGO
        if($config['CronDetailMangaGenerateByAlfabetMG']) {
            $schedule->command('CronDetailMangaGenerateByAlfabetMG:CronDetailMangaGenerateByAlfabetMGV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/manga/CronDetailMangaGenerateByAlfabetMG.log');
        }
        
        if($config['CronGenreMangaGenerateByAlfabetMG']) {
            $schedule->command('CronGenreMangaGenerateByAlfabetMG:CronGenreMangaGenerateByAlfabetMGV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/manga/CronGenreMangaGenerateByAlfabetMG.log');
        }

        if($config['CronListMangaGenerateByAlfabetMG']) {
            $schedule->command('CronListMangaGenerateByAlfabetMG:CronListMangaGenerateByAlfabetMGV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/manga/CronListMangaGenerateByAlfabetMG.log');
        }

        if($config['CronImageChapterMangaByIdMG']) {
            $schedule->command('CronImageChapterMangaByIdMG:CronImageChapterMangaByIdMGV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/manga/CronImageChapterMangaByIdMG.log');
        }
    }
}
