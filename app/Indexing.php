<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;
use App\Stop_words;
use App\PhrasePorterStemmer;
use App\highlight;
use App\Document;
use App\Term;
use App\Term_document;

class Indexing extends Model {
	
	public static $conn;
	
	public static $index_array = array();
	
	public function __construct(){
        Indexing::$conn = mysqli_connect('localhost', 'root', '', 'search_engine');
		if( mysqli_connect_errno() ){
			throw new exception('Could not connect to DB');
		}
	}
	
	public static function updateIndex($files_to_be_indexed){
        $phrasePorterStemmer = new PhrasePorterStemmer();
        $stop_words = new Stop_words();
        $termObj = new Term();
        $term_document = new Term_document();

		$dictionary = array();
        //$docCount = array();

		# for each file ( 1400 times for cranfield )
		foreach($files_to_be_indexed as $name => $file){

            // removing stop words from text of the file
			$text_without_stopWords = $stop_words->remove_stop_words(strtolower($file));
            // stemming process to file after removing stop words
            $text_after_stemming = $phrasePorterStemmer->StemPhrase($text_without_stopWords);

            // insert into document table
            //$document = new Document();
            //$docID = $document->insert($name, count($text_after_stemming));
            //////////////////////
            $sql = "INSERT INTO `documents` 
				(`document_title`, `terms_count`) 
				VALUES ('".mysqli_escape_string(self::$conn, $name)."',".count($text_after_stemming).")";
            //echo $sql;
            $result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
            $docID = mysqli_insert_id(self::$conn);
            ////////////////

            foreach($text_after_stemming as $location => $term) {
				if(!isset($dictionary[$term])) {
					$dictionary[$term] = array('df' => 0, 'postings' => array());
				}
				if(!isset($dictionary[$term]['postings'][$docID])) {
					$dictionary[$term]['df']++;
					$dictionary[$term]['postings'][$docID] = array('tf' => 0, 'locations' => array());
				}
				$dictionary[$term]['postings'][$docID]['tf']++;
				$dictionary[$term]['postings'][$docID]['locations'][] = $location;
			}
		}

        $sql2 = '';
		foreach($dictionary as $term => $dict){

            // insert into term table

            $termID = $termObj->insert_conn($term, $dict['df'], self::$conn);

			foreach($dict['postings'] as $docID => $posting){
				$sql2 .= "(".$termID.",".$docID.",".$posting['tf'].",'".implode(",",$posting['locations'])."'),";
			}
		}

		// insert into term document table
        $result = $term_document->insert_conn($sql2, self::$conn);

	}
	
	public static function submit_query($query){

        $QueryWords = preg_split('/\s+/', $query);
        $data = DB::table('term_document')
            ->join('terms', 'terms.term_id', '=', 'term_document.term_id')
            ->join('documents', 'documents.document_id', '=', 'term_document.document_id')
            ->select('term', 'document_frequently', 'documents.document_id', 'term_frequently', 'terms_count')
            ->whereIn('term', $QueryWords)
            ->get();

        $total_documents = DB::table('documents')->count();
		//echo $total_documents;

		$relevence_docs = array();

		foreach($data as $doc_item){

			if(!isset($relevence_docs[$doc_item->document_id])){
				$relevence_docs[$doc_item->document_id] = 0;
			}
//            var_dump($doc_item);
			$relevence_docs[$doc_item->document_id] +=
					($doc_item->term_frequently/$doc_item->terms_count) *
					log($total_documents / $doc_item->document_frequently);

			/*$relevence_docs[$doc_item->document_id]+=
					(1+log10($doc_item->term_frequently)) *
					log10($total_documents / $doc_item->document_frequently);*/
			/*$relevence_docs[$doc_item->document_id]+=
					($doc_item->term_frequently) *
					log($total_documents / $doc_item->document_frequently, 2);*/
			/*$relevence_docs[$doc_item->document_id]+=
					(0.5+0.5*$doc_item->term_frequently) *
					log($total_documents / $doc_item->document_frequently, 2);*/
		}

        $documents = DB::table('documents')
            ->whereIn('document_id', array_keys($relevence_docs))
            ->get();

		$links = array();
		foreach ($documents as $doc){
			$links[$doc->document_id] = $doc->document_title;
		}

		arsort($relevence_docs); // desending
//        dd($documents);
//        dd($links);
//        dd($relevence_docs);

        $Data = array();
		$i=1;
		foreach($relevence_docs as $docID => $s){
			if($i <= 20){ // return top 20 document
			    array_push($Data, ['title' => $links[$docID],
                                          'link' => "http://127.0.0.1:8000/documents/".$links[$docID],
                                          //'content' =>
                                        ]);
				$i++;
			}
			else{
				break;
			}
		}
//		dd($Data);
		return $Data;

//		$i=1;
//		foreach($relevence_docs as $docID => $s){
//			if($i <= 20){ // return top 20 document
//                $highlightedText = $highlight->highlight($query, "documents/".$links[$docID]);
//				$i++;
//			}
//			else{
//				break;
//			}
//		}
		
	}
	
	public static function one_word_get_synonims ($tag){
  		$wn_command = '"C:/Program Files (x86)/WordNet/2.1/bin/wn" "'.$tag.'" "-synsn"';
  		$raw_synonims = shell_exec ($wn_command);
  		if (! $raw_synonims)
    	{
	  		return array($tag);
   		}
  		$matches = array ();
  		preg_match_all ("/\s+(.+)\s+=>/",
						$raw_synonims, $matches, PREG_PATTERN_ORDER);
		//echo "<pre>";
		//print_r($matches);
		//echo "</pre>";
  		$synonims = array ();		
  		# only sense '1' is taken in account
  		if(!isset($matches[1][0])){
			return array($tag);
		}
		$synonims = explode (", ", $matches[1][0]);
  		return $synonims;
	}
	
	public static function wordnet_tags_synonims ($words){
		$words_array = explode(" ", $words);
	  	$synonims = array ();
	  	foreach ($words_array as $tag)
		{
		  	$tag_synonims = self::one_word_get_synonims ($tag);
			# StemArray excutes the stem functions
            $phrasePorterStemmer = new PhrasePorterStemmer();
		  	$synonims[] = $phrasePorterStemmer->StemArray($tag_synonims);
		}
	  	return $synonims;
	}
	
	public static function submit_semantic_query($query){
		$docs = array();
		$relevence_docs = array();
		# get sense 1 of every term in the query 
		$meanings_array = self::wordnet_tags_synonims($query);

		$meanings_array[0] = array_unique($meanings_array[0]);
		# computer : computer, computing machine, computing device, data processor, electronic computer, information processing system
		/*echo "<pre>";
		print_r($meanings_array);
		echo "</pre>";*/
		
		# query is : summit mountain
		# meanings_array is :
		# array (
		# [0] => array( [0] => peak [1] => height ..... )
		# [1] => array( [0] => mountain [1] => mount )
		# )
		
		$relevence_docs = array();
		
		$sql = "SELECT COUNT(*) as 'N' FROM `documents`";
		$result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
		$total_documents = mysqli_fetch_assoc($result)['N'];
		
		
		foreach($meanings_array as $meaning){
			/*
			foreach($meaning as $terms){
				# if the meaning is more than 1 word, make sure of location
				if(str_word_count($terms)>1){
					
					# sql for having the terms together in the same document
					$sql = "SELECT COUNT(*) AS `c`, `document_id`
							FROM `terms`, `term_document`
							WHERE `terms`.`term_id` = `term_document`.`term_id` AND
							(`term` = '".preg_replace('/\s+/', "' OR `term` = '", $terms)."')
							GROUP BY `document_id`
							HAVING `c`= ".str_word_count($terms);
					//echo "*******************************<br/>";
					//echo $sql."<br/>";
					$result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
					$data = array();
					# only document_id
					while( $row = mysqli_fetch_assoc($result) ){
						$data[] = $row['document_id'];
					}
					//echo "----------------------------<br/>";
					//echo "<pre>";
					//print_r($data);
					//echo "</pre>";
					//echo "----------------------------<br/>";

					# if the term(s) exist in db 
					if(isset($data[0])){
					
						$phrase_query = explode(' ', $terms);
						//echo "wwwwwwwwwwwwwwwwwwwwwwwssssssssssssss";
						//print_r($ws);
						$locations = array();
						
						# the information processing processing information information processing system system
						# information processing system :
						# information : (1, 4, 5)
						# processing : (2, 3, 6) -1 => (1, 2, 5)
						# system : (7, 8) -2 => (5, 6)
						# intersect is (5) 
						
						# foreach and not query for all , to maintain the sequence of terms of the meaning
						foreach($phrase_query as $term){
							$sql = "SELECT `term`, `document_frequently`, `document_id`, `term_frequently`, `locations`
								FROM `terms`, `term_document`
								WHERE `terms`.`term_id` = `term_document`.`term_id` AND
								`term` = '".$term."' AND
								(`document_id` = " . implode(' OR `document_id` = ',$data ) . ")
								ORDER BY `document_id`";
							$result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
							$data2 = array();
							while( $row = mysqli_fetch_assoc($result) ){
								$data2[] = $row;
							}
							$locations[] = $data2;
						}
						
						# $locations[0] for information, $locations[1] for processing, $locations[2] for system
						
						$array_of_locations = array();
						$i = 0;
						foreach($locations as &$t){
							$f = function($val, $i){
								return $val-$i;
							};
							$j = 0;
							foreach($t as &$d){
								$d['locations'] = explode(',', $d['locations']);
								$d['locations'] = array_map($f, $d['locations'], array_fill(0,count($d['locations']),$i));
								# j for doc , i for term
								$array_of_locations[$j][$i] = $d['locations'];
								$j++;
							}
							$i++;
						}
						echo "********************************************************<br/>";
						echo "********************************************************<br/>";
						echo "(".$terms.") appear togethe in these docs after fixing location :";
						echo "<pre>";
						print_r($locations);
						echo "</pre>";
						echo "array_of_locations:<br/><pre>";
						print_r($array_of_locations);
						$intersect = array();
						foreach($array_of_locations as $array_of_l){
							$intersect[] = call_user_func_array('array_intersect',$array_of_l);
						}
						$k = 0;
						foreach($intersect as $inter){
							if(isset($inter[0])){
								$docs[] = $locations[0][$k]['document_id'];
							}
							$k++;
						}
						echo "<br/>----*****----*****----*****----*****----*****----*****----*****<br/>";
						print_r($intersect);
						echo "<br/>----*****----*****----*****----*****----*****----*****----*****<br/></pre>";
					}
				}
			}
			*/
			
			# if the meaning is only one word
			
			//echo " THE ONE WORD IS :".$terms;
			$sql = "SELECT  `documents`.`terms_count` , `t`.`tf`, `t`.`document_id`
					FROM `documents` INNER JOIN 
					(SELECT SUM(`term_frequently`) as `tf`, `term_document`.`document_id`
					FROM `terms`, `term_document` 
					WHERE `terms`.`term_id` = `term_document`.`term_id` AND  
					(`term` = '".implode("' OR `term` = '", $meaning)."')
					GROUP BY `document_id`) AS `t` 
					ON `documents`.`document_id` = `t`.`document_id`";
			//echo $sql."<br/>";
			$result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
			$data2 = array();
			$df = mysqli_num_rows($result);
			while( $row = mysqli_fetch_assoc($result) ){
				$data2[] = $row;
			}
			
			//echo "<br/><pre>one word semantic : "; print_r($data2); echo "</pre> and df is ".$df;
			
			foreach($data2 as $doc_item){
				if(!isset($relevence_docs[$doc_item['document_id']])){
					$relevence_docs[$doc_item['document_id']]=0;
				}
				$relevence_docs[$doc_item['document_id']]+=
						($doc_item['tf']/$doc_item['terms_count']) *
						log($total_documents / $df);
			}
			
			
		}
		/*echo "<br/><pre>";
		print_r($relevence_docs);
		echo "</pre>";*/
		
		$sql = "SELECT * FROM `documents`
				WHERE `document_id` = ".implode(" OR `document_id` = ", array_keys($relevence_docs));
		$result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
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
					relevant doc number $i : <a href='/ir/documents/".$links[$docID]."'>".$links[$docID]."</a></br>
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
		
		echo "---------------<br/><pre>";
		//print_r($relevence_docs);
		//print_r($links);
		echo "</pre>";
		echo "number of results is : ".count(array_unique($links));
		
	}
	
	public static function getDocuments(){
		$sql = "SELECT `document_id`,`document_title` FROM `documents`";
		$result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
		$data = array();
		while( $row = mysqli_fetch_assoc($result) ){
			$data[] = $row;
		}
		return $data;
	}
	
	public static function deleteDocument($id){
		
		$id = (int) $id;
		if($id < 1){
			exit;
		}
		
		# 1. deleting document from db .
		$sql = "DELETE FROM `documents` 
				WHERE `document_id` = ".$id;
		$result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
		
		# 2. get terms in it .
		$sql = "SELECT `term_id` FROM `term_document` 
				WHERE `document_id` = ".$id;
		$result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
		if (mysqli_num_rows($result) == 0){
			echo "There is no document with id : ".$id;
			exit;
		}
		$data = array();
		while( $row = mysqli_fetch_assoc($result) ){
			$data[] = $row['term_id'];
		}
		
		# 3. delete tf for all terms in that doc .
		$sql = "DELETE FROM `term_document`
				WHERE `document_id` = ".$id;
		$result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
		
		# 4. decreasdin df by (1) for all terms in that doc .
		$sql = "UPDATE `terms` 
				SET `document_frequently` = `document_frequently` - 1 
				WHERE `term_id` = ".implode(" OR `term_id` = ", $data);
		$result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
		
		# 5. not important but gives more efficent on query index .
		$sql = "DELETE FROM `terms`
				WHERE `document_frequently` = 0";
		$result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
		
	}
	
	public static function resetIndex(){
		// delete all data from all table 
		$sql = "TRUNCATE TABLE `documents`";
		$result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
		$sql = "TRUNCATE TABLE `terms`";
		$result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
		$sql = "TRUNCATE TABLE `term_document`";
		$result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
		// delete all files from documents directory
		array_map('unlink', glob('documents/' . '*.txt'));
	}
}


?>
