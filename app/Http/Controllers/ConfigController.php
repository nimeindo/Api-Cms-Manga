<?php

namespace App\Http\Controllers;
use \Illuminate\Http\Request;
use \Illuminate\Http\Response;
use \App\Http\Controllers\Controller;
use \GuzzleHttp\Exception\GuzzleException;
use \GuzzleHttp\Client;
use \Carbon\Carbon;


class ConfigController 
{
    public function __construct(){
        $this->BASE_URL_MANGA="https://mangaid.click/";
        $this->SLUG_MANGA_LIST="manga-list";
        $this->LAST_UPDATE_MANGA="latest-release";
    }
}