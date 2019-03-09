<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use DB;
use App\Indexing;

class viewDocumentController extends Controller
{
    public function __construct(){

    }

    public function browse(){
        $indexing = new Indexing();
        $documents = $indexing->getDocuments();

        $Data = [
            'Content' => $documents
        ];

        return view('browseDocument', compact('Data'));
    }
}


?>
