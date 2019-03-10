<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use DB;

class searchController extends Controller
{
    public function __construct(){

    }

    public function search(Request $request){
        $semantic = false;
        if(isset($request['s'])){
            $semantic = true;
        }
        $Data = [
            'Content' => ['Query' => $request['Query'], 'semantic' => $semantic]
        ];

        return view('result', compact('Data'));
    }
}


?>
