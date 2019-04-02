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
        $langDetector = new LangDetector();

        //remove stop words
        $Query_stopWords_removed = $stop_words->remove_stop_words($query);
        $Query_without_stopWords_arr = explode(' ', $Query_stopWords_removed);

        // to check date token
        $text_without_stopWords_arr = $indexing->includeDate($Query_without_stopWords_arr);

        // stemming process
        //$Query_steamed = $phrasePorterStemmer->StemPhrase($Query_stopWords_removed);

        // limitization
        $text_after_limitization = array();
        $global_delay = 0;
        foreach($text_without_stopWords_arr as $location => $term) {

            $arr = $indexing->Process_the_word($term);

            $local_delay = 0;
            foreach ($arr as $elem){
                $text_after_limitization[$global_delay + $location + $local_delay] = $elem;
                $local_delay++;
            }

            $global_delay += count($arr) - 1;

        }


        // applying Ngram
        $Ngram_arr = array();
        $Ngram = array();
        foreach ($text_without_stopWords_arr as $index => $item){
            $Ngram[$index] = $langDetector->getNgrams($item);
        }
        foreach ($Ngram as $index => $item){
            foreach ($item as $index => $elem){
                array_push($Ngram_arr, $elem);
            }
        }

        if($Semantic){
            Index::submit_semantic_query($Query_stopWords_removed);
        }else{
            $FinalData = Indexing::submit_query($query, $text_without_stopWords_arr, $text_after_limitization, $Ngram_arr);
        }
        array_push($FinalData, $Query_stopWords_removed);

        return $FinalData;
    }
}

?>
