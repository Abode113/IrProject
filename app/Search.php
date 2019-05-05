<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class Search extends Model {

    function __construct(){

    }
    //Match Function
    function SearchOn($query, $Semantic){
        
        $phrasePorterStemmer = new PhrasePorterStemmer();
        $stop_words = new Stop_words();
        $indexing = new Indexing();

        //remove stop words
        $Query_stopWords_removed = $stop_words->remove_stop_words($query);
        $Query_without_stopWords_arr = explode(' ', $Query_stopWords_removed);

        // to check date token
        $text_without_stopWords_arr = $indexing->includeDate($Query_without_stopWords_arr);

        // stemming process
        //$Query_steamed = $phrasePorterStemmer->StemPhrase($Query_stopWords_removed);

        //------------------------------------
        // query expansion
        $expanded_query = array();
        //get_synonyms
        foreach($text_without_stopWords_arr as $location => $term) {
            $temp = $indexing->get_synonim($term);
            if($temp != null) {
                foreach ($temp as $elem) {
                    array_push($expanded_query, $elem);
                }
            }
        }
        //get_hypernyms
        foreach($text_without_stopWords_arr as $location => $term) {
            $temp = $indexing->get_hypernyms($term);
            if($temp != null) {
                foreach ($temp as $elem) {
                    array_push($expanded_query, $elem);
                }
            }
        }

        // remove repeated element
        $expanded_query = array_unique($expanded_query);
        //------------------------------------

        // limitization
        $text_after_limitization = array();
        $soundex_arr = array();

//        $global_delay = 0;
//        $i = 0;
        foreach($text_without_stopWords_arr as $location => $term) {

            $arr = $indexing->Process_the_word($term);

//            $local_delay = 0;
//            foreach ($arr as $elem){
//                $text_after_limitization[$global_delay + $location + $local_delay] = $elem;
//                $local_delay++;
//            }
            if(count($arr) >= 0) {
                array_push($text_after_limitization, $arr[0]);
            }
            for ($j = 1; $j < count($arr); $j++){
                array_push($soundex_arr, $arr[$j]);
            }

            //$global_delay += count($arr) - 1;
        }

        $text_without_stopWords_arr = self::deleteUnneededWord($text_without_stopWords_arr);

        if($Semantic){
            $FinalData = Indexing::submit_query($query, $text_without_stopWords_arr, $text_after_limitization, $expanded_query);
        }else{
            $FinalData = Indexing::submit_query($query, $text_without_stopWords_arr, $text_after_limitization, null);
        }


        array_push($FinalData, $Query_stopWords_removed);

        $FinalData = Indexing::Personalization($FinalData);

        return $FinalData;
    }

    function deleteUnneededWord($arr){
        foreach ($arr as $index => $elem){
            if($elem == '.'){
                unset($arr[$index]);
            }
        }
            return array_values($arr);
    }
}

?>
