<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class docsimilarity extends Model {

    public static $tableName = 'docsimilarities';
    public static $tb_doc_left_id = 'doc_left_id';
    public static $tb_doc_id_right = 'doc_id_right';
    public static $tb_similarity_value = 'similarity_value';

    function __construct(){

    }

    public function findSimilartiy($doc_id){

        $conn = mysqli_connect('localhost', 'root', '', 'search_engine');
        if( mysqli_connect_errno() ){
            throw new exception('Could not connect to DB');
        }

        $sql = "SELECT COUNT(*) as 'N' FROM `documents`";
        $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
        $total_documents = mysqli_fetch_assoc($result)['N'];

        $tf_idf = self::tf_idf_length($conn, $doc_id, $total_documents);


        //echo "<pre>"; print_r($tf_idf); echo "</pre>";

        $sql = "SELECT `document_id` FROM `documents` WHERE `document_id` != ".$doc_id;
        $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

        $tf_idf_rest = array();
        while( $row = mysqli_fetch_assoc($result) ){
            $tf_idf_rest[$row['document_id']] = self::tf_idf_length($conn, $row['document_id'], $total_documents) ;
        }

        $sim = self::find_similarity($tf_idf,$tf_idf_rest);

        arsort($sim);


        $data = DB::table('documents')
            ->select('document_title')
            ->where('document_id', $doc_id)
            ->get();
        $FinalData = [[$doc_id, $data[0]->document_title], array()];

        foreach ($sim as $index => $elem){

            $data = DB::table('documents')
                ->select('document_title', 'document_id')
                ->where('document_id', $index)
                ->get();
            array_push($FinalData[1], [$data[0]->document_title, $elem, $data[0]->document_id]);
        }
        return $FinalData;

    }

    public function tf_idf_length($conn, $doc, $total_documents){

        $data = DB::table('term_documents')
            ->join('terms', 'term_documents.term_id', '=', 'terms.term_id')
            ->select('terms.term_id', 'term_frequently', 'term_documents.document_id', 'document_frequently')
            ->where('term_documents.document_id', (int)$doc)
            ->get();

        $tf_idf = array();
        foreach($data as $term){
            $tf_idf[$term->term_id] =
                ($term->term_frequently) * log($total_documents / $term->document_frequently, 2);
        }

        return $tf_idf;

    }

    public function square($n)
    {
        return($n * $n);
    }

    public function find_similarity($tf_idf,$tf_idf_rest){
        $sim = array();
        //dd(array_sum(array_map('self::square', $tf_idf)));
        $doc_base = array_sum(array_map('self::square', $tf_idf));
        foreach($tf_idf_rest as $i => $tf_idf_single_other){
            $a = array();
            foreach($tf_idf as $key => $value){
                if(array_key_exists($key, $tf_idf_single_other))
                    $a[$key] = $tf_idf_single_other[$key] * $value;
            }
            $sum = array_sum($a);
            $other_doc_base = array_sum(array_map('self::square', $tf_idf_single_other));
            $base = sqrt($doc_base * $other_doc_base);
            $sim[$i] = $sum/$base;
        }
        return $sim;
    }

    public function add($doc_id_left, $doc_id_right, $Similarity_value){
//        var_dump($doc_id_left);
//        var_dump($doc_id_right);
//        dd($Similarity_value);
        $this->doc_left_id = $doc_id_left;
        $this->doc_id_right = $doc_id_right;
        $this->Similarity_value = $Similarity_value;
        $id = $this->save();

        return $id;
    }
}

?>
