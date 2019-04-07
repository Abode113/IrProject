<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;
use App\Document;

class test_terms extends Model
{
    public static $tableName = 'test_terms';
    public static $corpus_id = 'corpus_id';
    public static $tb_term_id = 'term_id';
    public static $tb_document_frequently = 'document_frequently';
    public static $tb_term = 'term';
    public static $tb_updated_at = 'updated_at';
    public static $tb_created_at = 'created_at';


    function __construct()
    {

    }

}

?>
