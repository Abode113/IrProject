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
        //dd($request->all());
        $stop_words = new Stop_words();
        $phrasePorterStemmer = new PhrasePorterStemmer();
        $indexing = new Indexing();

        $semantic = false;
        if(isset($request['s'])){
            $semantic = true;
        }

        $Query_stopWords_removed = $stop_words->remove_stop_words(strtolower($request['Query']));
        $Query_steamed = $phrasePorterStemmer->StemPhrase($Query_stopWords_removed);

        if($semantic){
            Index::submit_semantic_query($Query_stopWords_removed);
        }else{
            $imploded_Query = implode(' ', $Query_steamed);
            Indexing::submit_query($imploded_Query);
        }

        $Data = [
            'Content' => ['Query' => $request['Query'], 'semantic' => $semantic]
        ];

        return view('result', compact('Data'));
    }
}


?>
