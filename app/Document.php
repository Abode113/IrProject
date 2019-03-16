<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class Document extends Model {

    public static $tableName = 'documents';
    public static $tb_id = 'document_id';
    public static $tb_document_title = 'document_title';
    public static $tb_terms_count = 'terms_count';


    function __construct(){

    }

    function insert($docName, $termCount){

        $this->document_title = $docName;
        $this->terms_count = $termCount;
        $id = $this->save();

        return $id;
    }
}

?>
