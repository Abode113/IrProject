<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class xpath extends Model {

    function __construct(){

    }

    function submit_query($query){

        $conn = mysqli_connect('localhost', 'root', '', 'search_engine_2');
        if( mysqli_connect_errno() ){
            throw new exception('Could not connect to DB');
        }

        $sql = "SELECT `term`, `document_frequently`, 
					   `documents`.`document_id`, `term_frequently`, `terms_count`
				FROM `terms`, `documents`, `term_documents`
				WHERE `terms`.`term_id` = `term_documents`.`term_id` 
				AND `documents`.`document_id` = `term_documents`.`document_id`
				AND (`term` = '".preg_replace('/\s+/', "' OR `term` = '", $query)."') ";
        $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
        $data = array();
        if (mysqli_num_rows($result) == 0){
            echo "<p style='color:red;'>There is no relevant documents in the corpus .</p>";
            exit;
        }
        while( $row = mysqli_fetch_assoc($result) ){
            $data[] = $row;
        }
        $sql = "SELECT COUNT(*) as 'N' FROM `documents`";
        $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
        $total_documents = mysqli_fetch_assoc($result)['N'];

        $relevence_docs = array();

        foreach($data as $doc_item){
            if(!isset($relevence_docs[$doc_item['document_id']])){
                $relevence_docs[$doc_item['document_id']]=0;
                $matched_terms_in_docs[$doc_item['document_id']]=1;
            }
            $relevence_docs[$doc_item['document_id']]+=
                ($doc_item['term_frequently']/$doc_item['terms_count']) *
                log($total_documents / $doc_item['document_frequently']);
        }
        $sql = "SELECT * FROM `documents`
				WHERE `document_id` = ".implode(" OR `document_id` = ", array_keys($relevence_docs));
        $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
        $data = array();
        while( $row = mysqli_fetch_assoc($result) ){
            $data[] = $row;
        }

        $links = array();
        foreach ($data as $doc){
            $links[$doc['document_id']] = $doc['document_title'];
        }

        arsort($relevence_docs); // desending

        $i=1;
        echo '<div class="card">
  				<ul class="list-group list-group-flush">';
        foreach($relevence_docs as $docID => $s){
            if($i <= 15){
                echo "
				<li class='list-group-item'>
					relevant doc number $i : <a href='".$links[$docID]."'>".$links[$docID]."</a></br>
				</li>
				";
                $i++;
            }
            else{
                break;
            }
        }
        echo "</ul>
			</div>";

        //echo "---------------<br/><pre>";
        //print_r($relevence_docs);
        //print_r($links);
        //echo "</pre>";

    }
}

?>
