<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use DB;
use App\Term;

class TermConroller extends Controller
{
    public function __construct(){

    }

    function browse(){

        $term = new Term();

        $Data = $term->getAllTerms();

        $Data = [
            'Content' => ['Data' => $Data]
        ];

        return view('browseTerm', compact('Data'));
    }

    function BrowseTermsByDocumentID(Request $request, $term_id){
        //dd($term_id);
        $term = new Term();
        $terms = $term->getTermsByDocumentID($term_id);

        $Data = [
            'Content' => ['Data' => $terms]
        ];

        return view('browseTerm', compact('Data'));

    }
}


?>





