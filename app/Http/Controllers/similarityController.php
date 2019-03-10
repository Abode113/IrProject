<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use DB;

class similarityController extends Controller
{
    public function __construct(){

    }

    public function browse(){

        return view('similarity');
    }
}


?>
