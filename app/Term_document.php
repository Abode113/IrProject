<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class Term_document extends Model {

    public static $tableName = 'term_document';
    public static $tb_term_id = 'term_id';
    public static $tb_document_id = 'document_id';
    public static $tb_term_frequently = 'term_frequently';
    public static $tb_locations = 'locations';
    public static $tb_updated_at = 'updated_at';
    public static $tb_created_at = 'created_at';


    function __construct(){

    }

    function insert_conn($sqlStatement, $conn){

        $sql2 = "INSERT INTO `term_document`
			(`term_id`, `document_id`, `term_frequently`, `locations`) 
			VALUES ";

        $sql2 .= $sqlStatement;

        $sql2 = substr($sql2, 0, -1);

        $result = mysqli_query($conn, $sql2) or die(mysqli_error(self::$conn));

        return $result;
    }
}

?>
