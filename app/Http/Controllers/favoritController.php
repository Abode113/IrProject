<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\favorit;
use Session;
use DB;

class favoritController extends Controller
{
    public function __construct(){

    }

    public function addtofavorit(Request $request){
        $DocList = $request['DocList'];

        $arr = array();
        foreach ($DocList as $elem){
            $obj = [
                'doc_id' => $elem
            ];
            array_push($arr, $obj);
        }

        favorit::insert($arr);

        return Redirect::to("http://127.0.0.1:8000/seach_engine/");
    }
}


?>
