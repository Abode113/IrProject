<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use DB;
use App\Stop_words;
use App\PhrasePorterStemmer;
use App\Indexing;
use App\LangDetector;
use App\Search;

class searchController extends Controller
{
    public function __construct(){

    }

    public function search(Request $request){

        $search = new Search();

        // initializing the semantic variable
        $semantic = false;
        if(isset($request['s'])){
            $semantic = true;
        }
        $FinalData = $search->SearchOn($request['Query'], $semantic);

        $Data = [
            'Content' => ['Data' => $FinalData, 'query' => $request['Query']]
        ];

        return view('result', compact('Data'));
    }

//    function test(Request $request){
//
//        $search = new Search();
//
//        $FinalData = $search->SearchOn($request['Query'], false);
//
//        $Data = [
//            'Content' => ['Data' => $FinalData, 'query' => $request['Query']]
//        ];
//
//        return view('result', compact('Data'));
//
//    }
}


?>
