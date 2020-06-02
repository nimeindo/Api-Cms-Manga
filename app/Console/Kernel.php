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
        if($config['CronDetailMangaGenerateByAlfabet']) {
            $schedule->command('CronDetailMangaGenerateByAlfabet:CronDetailMangaGenerateByAlfabetV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/CronDetailMangaGenerateByAlfabet.log');
        }

        if($config['CronDetailMangaGenerateByDate']) {
            $schedule->command('CronDetailMangaGenerateByDate:CronDetailMangaGenerateByDateV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/CronDetailMangaGenerateByDate.log');
        }

        if($config['CronLastUpdateChapterMangaGenerateAsc']) {
            $schedule->command('CronLastUpdateChapterMangaGenerateAsc:CronLastUpdateChapterMangaGenerateAscV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/CronLastUpdateChapterMangaGenerateAsc.log');
        }

        if($config['CronLastUpdateChapterMangaGenerateDsc']) {
            $schedule->command('CronLastUpdateChapterMangaGenerateDsc:CronLastUpdateChapterMangaGenerateDscV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/CronLastUpdateChapterMangaGenerateDsc.log');
        }

        if($config['CronListMangaGenerate']) {
            $schedule->command('CronListMangaGenerate:CronListMangaGenerateV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/CronListMangaGenerate.log');
        }

        if($config['CronImageChapterMangaByDate']) {
            $schedule->command('CronImageChapterMangaByDate:CronImageChapterMangaByDateV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/CronImageChapterMangaByDate.log');
        }

        if($config['CronImageChapterMangaById']) {
            $schedule->command('CronImageChapterMangaById:CronImageChapterMangaByIdV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/CronImageChapterMangaById.log');
        }

        #MONGO
        if($config['CronDetailMangaGenerateByAlfabetMG']) {
            $schedule->command('CronDetailMangaGenerateByAlfabetMG:CronDetailMangaGenerateByAlfabetMGV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/CronDetailMangaGenerateByAlfabetMG.log');
        }
        if($config['CronDetailMangaGenerateByDateMG']) {
            $schedule->command('CronDetailMangaGenerateByDateMG:CronDetailMangaGenerateByDateMGV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/CronDetailMangaGenerateByDateMG.log');
        }
        if($config['CronGenreMangaGenerateByAlfabetMG']) {
            $schedule->command('CronGenreMangaGenerateByAlfabetMG:CronGenreMangaGenerateByAlfabetMGV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/CronGenreMangaGenerateByAlfabetMG.log');
        }

        if($config['CronListMangaGenerateByAlfabetMG']) {
            $schedule->command('CronListMangaGenerateByAlfabetMG:CronListMangaGenerateByAlfabetMGV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/CronListMangaGenerateByAlfabetMG.log');
        }

        if($config['CronListMangaGenerateByDateMG']) {
            $schedule->command('CronListMangaGenerateByDateMG:CronListMangaGenerateByDateMGV1')
                ->everyMinute() #setiap menit
                ->appendOutputTo('/tmp/CronListMangaGenerateByDateMG.log');
        }
    }
}
