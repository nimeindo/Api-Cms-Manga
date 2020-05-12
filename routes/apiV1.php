<?php

// MangaId
$router->group(['prefix' => 'M/1', 'namespace' => 'Mangaid'], function () use ($router){

    // Scrap to Mysql
    $router->post('ListMangaScrap', 'ListMangaController@ListMangaScrap');
    $router->post('DetailMangaScrap', 'DetailMangaController@DetailMangaScrap');
    $router->post('ImageChapterMangaScrap', 'ImageChapterMangaController@ImageChapterMangaScrap');
    $router->post('LastUpdateChapterMangaScrap', 'LastUpdateChapterManga@LastUpdateChapterMangaScrap');
});
