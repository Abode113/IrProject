<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use DB;

class FileManagerController extends Controller
{
    public function __construct(){

    }

    public function uploade(Request $request){
        //dd($request->all());

        //include_once "indexing.php";
        $content = array();
        if(isset($request['document'])){
//            echo "<pre>";
//            print_r($request["document"]);
//            echo "</pre>";exit();
//            foreach ($_FILES["document"]["type"] as $num => $file){
//                if($file == "text/plain" && $_FILES["document"]["size"][$num]){
//                    $name = $_FILES["document"]["name"][$num];
//                    $content[$name] = file_get_contents($_FILES["document"]["tmp_name"][$num]);
//                    move_uploaded_file($_FILES["document"]["tmp_name"][$num], dirname(__FILE__)."/documents/$name");
//                }
//            }
            Index::updateIndex($content);
//		echo "<pre>";
//		print_r($content);
//		echo "</pre>";exit();
        }else{
             // no uploaded document
        }
    }
}


?>
