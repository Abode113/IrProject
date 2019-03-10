<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;
use App\Indexing;

class resetIndex extends Model {

    function __construct(){

    }

    function resetIndex(){
        $indexing = new Indexing();
        $indexing->resetIndex();
    }
}

?>

include_once "indexing.php";

Index::resetIndex();
header("Location: index.php");
