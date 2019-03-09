<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;
use App\Indexing;

class deleteDocument extends Model {

    function __construct(){

    }

    public function DeleteDocument($id){
        if($id != null){

            $indexing = new Indexing();
            $indexing->deleteDocument($id);

        }else{
            var_dump('id is undefined !');
        }
    }
}

?>
