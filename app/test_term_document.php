<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class test_term_document extends Model
{

    public static $tableName = 'test_term_documents';
    public static $corpus_id = 'corpus_id';
    public static $tb_term_id = 'term_id';
    public static $tb_document_id = 'document_id';
    public static $tb_term_frequently = 'term_frequently';
    public static $tb_locations = 'locations';
    public static $tb_updated_at = 'updated_at';
    public static $tb_created_at = 'created_at';


    function __construct()
    {

    }
}

?>
