<?php

#MYSQL
$config['CronDetailMangaGenerateByAlfabet'] = env('CRON_DETAIL_MANGA_GENERATE_BYALFABET', FALSE);
$config['CronDetailMangaGenerateByDate'] = env('CRON_DETAIL_MANGA_GENERATE_BYDATE', FALSE);
$config['CronDetailLastUpdateByDate'] = env('CRON_DETAIL_LAST_UPDATE_BYDATE', FALSE);
$config['CronDetalIamageChapterLastUpdateByDate'] = env('CRON_DETAIL_IMAGE_CHAPTER_LAST_UPDATE_BYDATE', FALSE);
$config['CronLastUpdateChapterMangaGenerateAsc'] = env('CRON_LASTUPDATE_CHAPTER_MANGA_GENERATEASC', FALSE);
$config['CronLastUpdateChapterMangaGenerateDsc'] = env('CRON_LASTUPDATE_CHAPTER_MANGA_GENERATEDSC', FALSE);
$config['CronListMangaGenerate'] = env('CRON_LIST_MANGA_GENERATE', FALSE);
$config['CronImageChapterMangaByDate'] = env('CRON_IMAGE_CHAPTER_MANGA_BYDATE', FALSE);
$config['CronImageChapterMangaById'] = env('CRON_IMAGE_CHAPTER_MANGA_BYID', FALSE);

#MONGO
$config['CronDetailMangaGenerateByAlfabetMG'] = env('CRON_DETAIL_MANGA_GENERATE_BYALFABETMG', FALSE);
$config['CronDetailMangaGenerateByDateMG'] = env('CRON_DETAIL_MANGA_GENERATE_BYDATEMG', FALSE);
$config['CronGenreMangaGenerateByAlfabetMG'] = env('CRON_GENREMANGA_GENERATE_ByALFABETMG', FALSE);
$config['CronListMangaGenerateByAlfabetMG'] = env('CRON_LIST_MANGA_GENERATE_BYALFABETMG', FALSE);
$config['CronListMangaGenerateByDateMG'] = env('CRON_LIST_MANGA_GENERATE_BYDATEMG', FALSE);
$config['CronLastUpdateMangaGenerateByDateMG'] = env('CRON_LAST_UPDATE_MANGA_GENERATE_BYDATEMG', FALSE);
$config['CronImageChapterMangaByDateMG'] = env('CRON_IMAGE_CHAPTER_MANGA_BYDATEMG', FALSE);
$config['CronImageChapterMangaByIdMG'] = env('CRON_IMAGE_CHAPTER_MANGA_BYIDMG', FALSE);

return $config;