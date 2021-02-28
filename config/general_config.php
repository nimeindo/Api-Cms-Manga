<?php

$config['environtment'] = strtolower(env('APP_ENV', 'local'));
$config['enable_sentry'] = env('SENTRY_ENABLE',FALSE);

$config['mongo']['query_timeout'] = (int) env('DB_TIMEOUT', 500000);
$config['mongo']['use_collection'] = env('DB_COLLECTIONS', 'contents');

$config['mongo']['use_collection_slider_manga'] = env('DB_COLLECTIONS_SLIDER_MANGA', 'contents');
$config['mongo']['use_collections_chapter_manga'] = env('DB_COLLECTIONS_CHAPTER_MANGA', 'contents');
$config['mongo']['use_collection_genrelist_manga'] = env('DB_COLLECTIONS_GENRE_LIST_MANGA', 'contents');
$config['mongo']['use_collection_detail_manga'] = env('DB_COLLECTIONS_DETAIL_MANGA', 'contents');
$config['mongo']['use_collection_last_update_chapter'] = env('DB_COLLECTIONS_LAST_UPDATE_CHAPTER', 'contents');
$config['mongo']['use_collections_list_manga'] = env('DB_COLLECTIONS_LIST_MANGA', 'contents');
$config['mongo']['use_collection_recomendation_manga'] = env('DB_COLLECTIONS_RECOMENDATION_MANGA', 'contents');
$config['mongo']['use_collection_detail_top_manga'] = env('DB_COLLECTIONS_DETAIL_TOP_MANGA', 'contents');

return $config;
