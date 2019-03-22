<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class Soundex extends Model {

    function __construct(){

    }

    public function getsoundex($term){
//        var_dump('soundex...');
//        var_dump($term);
        $soundex = soundex($term);
//        var_dump($soundex);
        return $soundex;
    }
}

?>
