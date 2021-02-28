<?php

$config = [
    'use_collection_detail_manga' => env('DB_COLLECTIONS_DETAIL_MANGA', ''),
    'use_collection_last_update_chapter' => env('DB_COLLECTIONS_LAST_UPDATE_CHAPTER', 'sss'),
    'use_collections_list_manga' => env('DB_COLLECTIONS_LIST_MANGA', ''),
    'collections_genre_list_manga' => env('DB_COLLECTIONS_GENRE_LIST_MANGA', ''),
    'collections_chapter_manga' => env('DB_COLLECTIONS_CHAPTER_MANGA', ''),
    'use_collection_detail_top_manga' => env('DB_COLLECTIONS_DETAIL_TOP_MANGA', ''),
    'use_collection_recomendation_manga' => env('DB_COLLECTIONS_RECOMENDATION_MANGA', ''),
    'use_collection_slider_manga' => env('DB_COLLECTIONS_SLIDER_MANGA', ''),
];

return $config;
