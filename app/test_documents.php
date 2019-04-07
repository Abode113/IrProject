<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;
use App\Term_document;


class test_documents extends Model
{
    public static $tableName = 'test_documents';
    public static $corpus_id = 'corpus_id';
    public static $tb_id = 'document_id';
    public static $tb_document_title = 'document_title';
    public static $tb_terms_count = 'terms_count';


    function __construct()
    {

    }


}

?>
