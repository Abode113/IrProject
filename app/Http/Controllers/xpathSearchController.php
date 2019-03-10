<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use DB;

class xpathSearchController extends Controller
{
    public function __construct(){

    }

    public function search(Request $request){

        $Data = [
            'Content' => $request['q_Query']
        ];

        return view('xpathResult', compact('Data'));
    }
}


?>
