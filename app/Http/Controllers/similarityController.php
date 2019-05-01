<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use DB;

class similarityController extends Controller
{
    public function __construct(){

    }

    public function browse($doc_id, $arr_ofSimilarity){

        //dd($doc_id);
        return view('similarity');
    }

    public function findSimilartiy(Request $request, $document_id){
        $doc_id = (int)$document_id;

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
        $FinalData = [$data[0]->document_title, array()];

        foreach ($sim as $index => $elem){

            $data = DB::table('documents')
                ->select('document_title')
                ->where('document_id', $index)
                ->get();
            array_push($FinalData[1], [$data[0]->document_title, $elem]);
        }

        $Data = [
            'Content' => ['Data' => $FinalData]
        ];

        return view('similarity', compact('Data'));
        //self::browse($doc_id, $sim);

    }

    public function tf_idf_length($conn, $doc, $total_documents){
        $sql = "SELECT `terms`.`term_id`, `term_frequently`, `term_documents`.`document_id`,
						`document_frequently`
						FROM `term_documents`,`terms`
						WHERE `term_documents`.`document_id` = ".(int)$doc." AND
						`term_documents`.`term_id` = `terms`.`term_id`";
        $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
        while( $row = mysqli_fetch_assoc($result) ){
            $data[] = $row;
        }
        /*echo "<pre>";
        print_r($data);
        echo "</pre>";*/
        $tf_idf = array();
        foreach($data as $term){
            $tf_idf[$term['term_id']] =
                ($term['term_frequently']) * log($total_documents / $term['document_frequently'], 2);
        }
        //$sum = array_sum($tf_idf);
        //$tf_idf['-1'] = $sum;
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
}


?>
