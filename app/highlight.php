<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;
use App\PhrasePorterStemmer;
use App\PorterStemmer;

class highlight extends Model {

    function __construct(){

    }

    public function highlight($query, $file){

        $phrasePorterStemmer = new PhrasePorterStemmer();
        $porterStemmer = new PorterStemmer();

        $q = explode(' ', $query);
        $text = file_get_contents($file);
        $text_array = $phrasePorterStemmer->StemPhrase(strtolower($text));

        //echo "<pre>";
        //print_r($text_array);
        //echo "query-----------------------------";
        //print_r($q);
        //echo "</pre>";
        $keys = array();
        foreach($q as $term){
            $keys = array_merge($keys, array_keys($text_array, $term));
            //print_r($keys)."</br>";
        }
        //print_r($keys);
        $keys = array_unique($keys);

        //echo "keys -----------------------------";
        //print_r($keys);
        $text_output = explode(' ', $text);
        dd($text_output);
        foreach($keys as $k){
            //echo "hello"."</br>";
            $text_output[$k] = "<span class='highlight'>".$text_output[$k]."</span>";
        }
        //echo "************************";
        //print_r($text_output);
        $text_highlighted = implode(' ', $text_output);
        //echo $text_highlighted;

    }
}

?>
