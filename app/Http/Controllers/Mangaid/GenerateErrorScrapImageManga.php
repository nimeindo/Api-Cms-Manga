<?php
namespace App\Http\Controllers\Mangaid;
use \Illuminate\Http\Request;
use \Illuminate\Http\Response;
use \App\Http\Controllers\Controller;
use \App\Http\Controllers\ConfigController;
use \GuzzleHttp\Client;
use \Goutte\Client as GoutteClient;
use \Tuna\CloudflareMiddleware;
use \GuzzleHttp\Cookie\FileCookieJar;
use \GuzzleHttp\Psr7;
use \Carbon\Carbon;
use Illuminate\Support\Str;
use Config;
use Throwable;

#Load Helper V1
use App\Helpers\V1\ResponseConnected as ResponseConnected;
use App\Helpers\V1\Converter as Converter;

#Load Models
use App\Models\V1\MainModel as MainModel;
use App\Models\V1\Mongo\MainModelMongo as MainModelMongo;


class GenerateErrorScrapImageManga extends Controller
{ 
    public function generateImageScrapById(){
        $data = [
         "Data_Not_Save"=>[
            [
               "chapter"=>"Chapter 124",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/heaven-defying-sword\/124",
               "id"=>65598
            ],
            [
               "chapter"=>"Chapter 64",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/volcanic-age\/64",
               "id"=>65599
            ],
            [
               "chapter"=>"Chapter 41",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/kyou-no-cerberus\/41",
               "id"=>65847
            ],
            [
               "chapter"=>"Chapter 47",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/school-zone-ningiyau\/47",
               "id"=>65850
            ],
            [
               "chapter"=>"Chapter 08",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/otome-bare\/08",
               "id"=>65853
            ],
            [
               "chapter"=>"Chapter 184",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/tomb-raider-king\/184",
               "id"=>65854
            ],
            [
               "chapter"=>"Chapter 22",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/sssclass-suicide-hunter\/22",
               "id"=>65855
            ],
            [
               "chapter"=>"Chapter 11",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/narikawari\/11",
               "id"=>65856
            ],
            [
               "chapter"=>"Chapter 133",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/virtual-world-close-combat-mage\/133",
               "id"=>65857
            ],
            [
               "chapter"=>"Chapter 738",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/martial-peak\/738",
               "id"=>65858
            ],
            [
               "chapter"=>"Chapter 250",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/my-wife-is-a-demon-queen\/250",
               "id"=>65859
            ],
            [
               "chapter"=>"Chapter 29",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/wortenia-senki\/29",
               "id"=>65860
            ],
            [
               "chapter"=>"Chapter 43",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/tokyo-revengers\/43",
               "id"=>65861
            ],
            [
               "chapter"=>"Chapter 368",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/spirit-sword-sovereign\/368",
               "id"=>65862
            ],
            [
               "chapter"=>"Chapter 279",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/silver-gravekeeper\/279",
               "id"=>65863
            ],
            [
               "chapter"=>"Chapter 18",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/return-to-player\/18",
               "id"=>65864
            ],
            [
               "chapter"=>"Chapter 506",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/versatile-mage\/506",
               "id"=>65865
            ],
            [
               "chapter"=>"Chapter 81",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/infection\/81",
               "id"=>65866
            ],
            [
               "chapter"=>"Chapter 61",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/demigods-and-semidevils\/61",
               "id"=>65867
            ],
            [
               "chapter"=>"Chapter 139",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/i-am-an-invincible-genius\/139",
               "id"=>65868
            ],
            [
               "chapter"=>"Chapter 05",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/the-necromancer-maid\/05",
               "id"=>65869
            ],
            [
               "chapter"=>"Chapter 33",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/sensei-wa-koi-o-oshie-rarenai\/33",
               "id"=>65870
            ],
            [
               "chapter"=>"Chapter 185",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/strongest-leveling\/185",
               "id"=>65871
            ],
            [
               "chapter"=>"Chapter 1001",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/one-piece\/1001",
               "id"=>65872
            ],
            [
               "chapter"=>"Chapter 06",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/isekai-majutsushi-wa-mahou-wo-tonaenai\/06",
               "id"=>65873
            ],
            [
               "chapter"=>"Chapter 10",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/kimi-wa-meidosama\/10",
               "id"=>65874
            ],
            [
               "chapter"=>"Chapter 221",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/the-superb-captain-in-the-city\/221",
               "id"=>65875
            ],
            [
               "chapter"=>"Chapter 43",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/new-game\/43",
               "id"=>65876
            ],
            [
               "chapter"=>"Chapter 36",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/kimi-no-koto-ga-dai-dai-dai-dai-daisuki-na-100ri-no-kanojo\/36",
               "id"=>65877
            ],
            [
               "chapter"=>"Chapter 13",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/tensura-nikki-tensei-shitara-slime-datta-ken\/13",
               "id"=>65878
            ],
            [
               "chapter"=>"Chapter 01.3",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/elf-tensei-kara-no-cheat-kenkokuki\/01.3",
               "id"=>65879
            ],
            [
               "chapter"=>"Chapter 20",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/hirasaka-hinako-ga-ero-kawaii-koto-wo-ore-dake-ga-shitteiru\/20",
               "id"=>65880
            ],
            [
               "chapter"=>"Chapter 46.5",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/yuru-camp\/46.5",
               "id"=>65881
            ],
            [
               "chapter"=>"Chapter 29",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/please-go-home-akutsusan\/29",
               "id"=>65882
            ],
            [
               "chapter"=>"Chapter 177",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/gosu\/177",
               "id"=>65883
            ],
            [
               "chapter"=>"Chapter 34",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/moriking\/34",
               "id"=>65884
            ],
            [
               "chapter"=>"Chapter 22.5",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/8kaijuu\/22.5",
               "id"=>65885
            ],
            [
               "chapter"=>"Chapter 183",
               "chapter_href"=>"https=>\/\/mangaid.click\/manga\/tomb-raider-king\/183",
               "id"=>65886
            ]
         ],
         ];
         
         $id ='';
         foreach($data['Data_Not_Save'] as $value){
             $id .= $value['id'].',';
         }
         
         echo $id;
    }
}