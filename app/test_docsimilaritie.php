<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class test_docsimilaritie extends Model {

    public static $tableName = 'test_docsimilarities';
    public static $tb_doc_left_id = 'doc_left_id';
    public static $tb_doc_id_right = 'doc_id_right';
    public static $tb_similarity_value = 'similarity_value';
    public static $tb_BackUpId = 'BackUpId';

    function __construct(){

    }
}

?>
