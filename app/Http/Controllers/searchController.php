<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use DB;
use App\Stop_words;
use App\PhrasePorterStemmer;
use App\Indexing;

class searchController extends Controller
{
    public function __construct(){

    }

    public function search(Request $request){

        $phrasePorterStemmer = new PhrasePorterStemmer();
        $stop_words = new Stop_words();

        // initializing the semantic variable
        $semantic = false;
        if(isset($request['s'])){
            $semantic = true;
        }

        //remove stop words
        $Query_stopWords_removed = $stop_words->remove_stop_words(strtolower($request['Query']));
        // stemming process
        $Query_steamed = $phrasePorterStemmer->StemPhrase($Query_stopWords_removed);

        if($semantic){
            Index::submit_semantic_query($Query_stopWords_removed);
        }else{
            $imploded_Query = implode(' ', $Query_steamed);
            $FinalData = Indexing::submit_query($imploded_Query);
        }

        $Data = [
            'Content' => ['Data' => $FinalData]
        ];

        return view('result', compact('Data'));
    }
}


?>
