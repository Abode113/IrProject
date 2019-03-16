<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class Term extends Model {

    public static $tableName = 'terms';
    public static $tb_term_id = 'term_id';
    public static $tb_document_frequently = 'document_frequently';
    public static $tb_term = 'term';
    public static $tb_updated_at = 'updated_at';
    public static $tb_created_at = 'created_at';


    function __construct(){

    }

    function insert_conn($term, $document_frequently, $conn){

        $sql = "INSERT INTO `terms`
				(`term`, `document_frequently`) 
				VALUES ('".$term."',".$document_frequently.") 
				ON DUPLICATE KEY UPDATE 
				`term`='".mysqli_escape_string($conn, $term)."', `document_frequently`=`document_frequently`+".$document_frequently.", 
				`term_id` = LAST_INSERT_ID(`term_id`)";
        $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
        $termID = mysqli_insert_id($conn);

        return $termID;
    }
}

?>
