<?php

// MangaId
$router->group(['prefix' => 'M/1', 'namespace' => 'Mangaid'], function () use ($router){

    // Scrap to Mysql
    $router->post('ListMangaScrap', 'ListMangaController@ListMangaScrap');
    $router->post('GenreListMangaScrap', 'GenreListMangaController@GenreListMangaScrap');
    $router->post('DetailMangaScrap', 'DetailMangaController@DetailMangaScrap');
    $router->post('ImageChapterMangaScrap', 'ImageChapterMangaController@ImageChapterMangaScrap');
    $router->get('checkExistImageChapter', 'ImageChapterMangaController@checkExistImageChapter');
    $router->get('checkIdChapterOnImageChapter', 'ImageChapterMangaController@checkIdChapterOnImageChapter');
    $router->post('LastUpdateChapterMangaScrap', 'LastUpdateChapterManga@LastUpdateChapterMangaScrap');
    $router->post('addNotificationList', 'NotificationController@addNotificationList');
    // generate data Error Scrap
    $router->get('GenerateImageScrapById', 'GenerateErrorScrapImageManga@generateImageScrapById');


    // generate data to mongo
    $router->post('GenerateDetailManga', 'DetailMangaController@generateDetailManga');
    $router->post('GenerateLastUpdateChapter', 'LastUpdateChapterManga@generateLastUpdateChapter');
    $router->post('ListMangaGenerate', 'ListMangaController@ListMangaGenerate');
    $router->post('GenerateGenreListManga', 'GenreListMangaController@GenerateGenreListManga');
    $router->post('GenerateChapterMangaAndImage', 'ImageChapterMangaController@GenerateChapterMangaAndImage');
    $router->post('GenerateTopDetailManga', 'MangaTopController@generateTopDetailManga');
    $router->post('UnPublishTopDetailManga', 'MangaTopController@unPublishTopDetailManga');
    $router->post('GenerateRecomendationManga', 'MangaRecomendationController@generateRecomendationManga');
    $router->post('UnPublishRecomendatinManga', 'MangaRecomendationController@unPublishRecomendatinManga');
    $router->post('GenerateSliderDetailManga', 'MangaSliderController@generateSliderDetailManga');
    $router->post('UnPublishSliderDetailManga', 'MangaSliderController@unPublishSliderDetailManga');
});
