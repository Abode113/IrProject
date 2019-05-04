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
use App\Regex;
use App\Soundex;

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
	    // initialzing
        ini_set('max_execution_time', 1200);
        $phrasePorterStemmer = new PhrasePorterStemmer();
        $stop_words = new Stop_words();
        $termObj = new Term();
        $term_document = new Term_document();

		$dictionary = array();

		# for each file ( 1400 times for cranfield )
		foreach($files_to_be_indexed as $name => $file){

            // removing stop words from text of the file
			$text_without_stopWords = $stop_words->remove_stop_words($file);
            $text_without_stopWords_arr = explode(' ', $text_without_stopWords);

            // stemming process to file after removing stop words
            //$text_after_stemming = $phrasePorterStemmer->StemPhrase($text_without_stopWords);


            // to check date token
            $text_without_stopWords_arr = self::includeDate($text_without_stopWords_arr);

            // to check Name
            //$text_without_stopWords_arr = self::includeName($text_without_stopWords_arr);

            // limitization
            $text_after_limitization = array();
            $global_delay = 0;
            foreach($text_without_stopWords_arr as $location => $term) {

                 $arr = self::Process_the_word($term);

                 $local_delay = 0;
                 foreach ($arr as $elem){
                     $text_after_limitization[$global_delay + $location + $local_delay] = $elem;
                     $local_delay++;
                 }

                $global_delay += count($arr) - 1;

            }

            // insert into document table
            //$document = new Document();
            //$docID = $document->insert($name, count($text_after_stemming));
            //////////////////////
            $sql = "INSERT INTO `documents` 
				(`document_title`, `terms_count`) 
				VALUES ('".mysqli_escape_string(self::$conn, $name)."',".count($text_after_limitization).")";
            //echo $sql;
            $result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
            $docID = mysqli_insert_id(self::$conn);
            ////////////////





            foreach($text_after_limitization as $location => $term) {
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
		//dd($dictionary);
		foreach($dictionary as $term => $dict){
            if($term != '.') {
                // insert into term table
                $termID = $termObj->insert_conn($term, $dict['df'], self::$conn);

                foreach ($dict['postings'] as $docID => $posting) {
                    $sql2 .= "(" . $termID . "," . $docID . "," . $posting['tf'] . ",'" . implode(",", $posting['locations']) . "'),";
                }
            }
		}

		// insert into term document table
        $result = $term_document->insert_conn($sql2, self::$conn);

	}

    public static function Process_the_word($term){
        $regex = new Regex();
        $soundex = new Soundex();
        $res = self::get_verb_Limit($term);
        $returnedVal = array();
        if($regex->isDate($term) || $regex->isLink($term)){
            if ($regex->isDate($term)){
                $var = $regex->getGeneralDate($term);
//            array_push($returnedVal, $regex->getGeneralDate($term));
                array_push($returnedVal, $var);
            }else if ($regex->isLink($term)){
                array_push($returnedVal, $regex->getGeneralLink($term));
            }
        }else if($res == null){ // name

            array_push($returnedVal, $term);
            array_push($returnedVal, $soundex->getsoundex($term));
        }else{
            array_push($returnedVal, self::get_verb_Limit($term));
        }
        return $returnedVal;
    }

    public static function includeDate($arr){
        $regex = new Regex();
        $newArr = array();
        foreach ($arr as $index => $term){
            if($regex->isYear($term) || $regex->isMonth($term) || $regex->isDay($term)){
                if(isset($arr[$index - 1])) {
                    if ($regex->isYear($arr[$index - 1]) || $regex->isMonth($arr[$index - 1]) || $regex->isDay($arr[$index - 1])) {
                        //$arr[$index - 1] .= ' ' . $term;
                        $newArr[count($newArr) - 1] .= '-' . $term;
                    }else{
                        array_push($newArr, $term);
                        continue;
                    }
                }else{
                    array_push($newArr, $term);
                    continue;
                }
            }else{
                array_push($newArr, $term);
            }
        }
        return $newArr;
    }

    public static function includeName($arr){
        $regex = new Regex();
        $newArr = array();
        foreach ($arr as $index => $term){
//            if($regex->isYear($term) || $regex->isMonth($term) || $regex->isDay($term)){
//                if(isset($arr[$index - 1])) {
//                    if ($regex->isYear($arr[$index - 1]) || $regex->isMonth($arr[$index - 1]) || $regex->isDay($arr[$index - 1])) {
//                        //$arr[$index - 1] .= ' ' . $term;
//                        $newArr[count($newArr) - 1] .= '-' . $term;
//                    }else{
//                        array_push($newArr, $term);
//                        continue;
//                    }
//                }else{
//                    array_push($newArr, $term);
//                    continue;
//                }
//            }else{
//                array_push($newArr, $term);
//            }
        }
        return $newArr;
    }

    public static function submit_query($queryStatment, $orginalQuery, $query, $expanded_query){

        $document = new Document();
        $term = new Term();
        //var_dump($query);exit();
        //$QueryWords = preg_split('/\s+/', $query);
        $data = DB::table('term_documents')
            ->join('terms', 'terms.term_id', '=', 'term_documents.term_id')
            ->join('documents', 'documents.document_id', '=', 'term_documents.document_id')
            ->select('term', 'document_frequently', 'documents.document_id', 'term_frequently', 'terms_count')
            ->whereIn('term', $query)
            ->get();

        $unseen = $term->unseen($orginalQuery, $data);

        // applying Ngram
        $langDetector = new LangDetector();
        $Ngram_arr = array();
        $Ngram = array();
        $soundex = new Soundex();
        foreach ($unseen as $index => $item){
            array_push($Ngram, $langDetector->getNgrams($item));
            array_push($Ngram[$index], $soundex->getsoundex($item));
        }
        foreach ($Ngram as $index => $item){
            foreach ($item as $index => $elem){
                array_push($Ngram_arr, $elem);
            }
        }
        // ------------

        $total_documents = DB::table('documents')->count();
		//echo $total_documents;

		$relevence_docs = array();

		foreach($data as $doc_item){
            $relevance_val = ($doc_item->term_frequently*50000/$doc_item->terms_count) *
                (log($total_documents / $doc_item->document_frequently) + 1);


            if(!isset($relevence_docs[$doc_item->document_id])){
                $relevence_docs[$doc_item->document_id]['document_id'] = $doc_item->document_id;
                $relevence_docs[$doc_item->document_id]['relevance_val'] = 0;
                $relevence_docs[$doc_item->document_id]['token'] = [];
            }

            $relevence_docs[$doc_item->document_id]['relevance_val'] += $relevance_val;
            if(isset($relevence_docs[$doc_item->document_id]['token'][$doc_item->term])){
                array_push(
                    $relevence_docs[$doc_item->document_id]['token'][$doc_item->term],
                    [$doc_item->term, $relevance_val]);
            }else{
                $relevence_docs[$doc_item->document_id]['token'][$doc_item->term] = array();
                array_push(
                    $relevence_docs[$doc_item->document_id]['token'][$doc_item->term],
                    [$doc_item->term, $relevance_val]);
            }
		}

        $mostPropWords = array();

		if (count($relevence_docs) < 10 || $unseen){
		    if(count($relevence_docs) < 10){
                $ngram_relevence_docs = $term->Get_nGram_relevance($Ngram_arr, $relevence_docs);

                $unseen_docs = $term->Get_nGram_relevance(array_values($unseen), $relevence_docs);

                if($unseen) {
                    $mostPropWords = $term->GetMostPropableWordUnseen($queryStatment, array_values($unseen), $unseen_docs);
                }
            }else{
                $ngram_relevence_docs = $term->Get_nGram_relevance(array_values($unseen), $relevence_docs);
                if($unseen) {
                    $mostPropWords = $term->GetMostPropableWordUnseen($queryStatment, array_values($unseen), $ngram_relevence_docs);
                }
            }

            $relevence_docs = $term->array_merge_relvance($relevence_docs, $ngram_relevence_docs);
        }

        //------------------------------------------------------
        if($expanded_query != null){
            $data = DB::table('term_documents')
                ->join('terms', 'terms.term_id', '=', 'term_documents.term_id')
                ->join('documents', 'documents.document_id', '=', 'term_documents.document_id')
                ->select('term', 'document_frequently', 'documents.document_id', 'term_frequently', 'terms_count')
                ->whereIn('term', $expanded_query)
                ->get();

            $expanded_relevence_docs = array();
            foreach($data as $doc_item){
                $relevance_val = ($doc_item->term_frequently*5000/$doc_item->terms_count) *
                    (log($total_documents / $doc_item->document_frequently) + 1);


                if(!isset($expanded_relevence_docs[$doc_item->document_id])){
                    $expanded_relevence_docs[$doc_item->document_id]['document_id'] = $doc_item->document_id;
                    $expanded_relevence_docs[$doc_item->document_id]['relevance_val'] = 0;
                    $expanded_relevence_docs[$doc_item->document_id]['token'] = [];
                }

                $expanded_relevence_docs[$doc_item->document_id]['relevance_val'] += $relevance_val;
                if(isset($expanded_relevence_docs[$doc_item->document_id]['token'][$doc_item->term])){
                    array_push(
                        $expanded_relevence_docs[$doc_item->document_id]['token'][$doc_item->term],
                        [$doc_item->term, $relevance_val]);
                }else{
                    $expanded_relevence_docs[$doc_item->document_id]['token'][$doc_item->term] = array();
                    array_push(
                        $expanded_relevence_docs[$doc_item->document_id]['token'][$doc_item->term],
                        [$doc_item->term, $relevance_val]);
                }
            }


            $relevence_docs = self::mergeNormalWithSemantic($relevence_docs, $expanded_relevence_docs);
        }
        //------------------------------------------------------

        $relevence_docs = $document->AddDocmentData($relevence_docs);

        usort($relevence_docs, function($a, $b) { // anonymous function
            if ($a['relevance_val'] == $b['relevance_val']) {
                return 0;
            }
            return ($a['relevance_val'] > $b['relevance_val']) ? -1 : 1;
        });

        $relevence_docs = array_slice($relevence_docs,0, 30);
        $result = [$relevence_docs, $mostPropWords];
		return $result;
	}

    public static function get_verb_Limit ($tag){
	    // cmd commend
        $wn_command = '"C:/Program Files (x86)/WordNet/2.1/bin/wn" "'.$tag.'" "-synsv"';
        $raw_synonims = shell_exec ($wn_command);

        // if the word exist
        if (! $raw_synonims) {
            return null;
        }

        // get the result of cmmend
        $matches = array ();
        preg_match_all ("/\s+(.+)\s+=>/",
            $raw_synonims, $matches, PREG_PATTERN_ORDER);

        // if no matched result
        if(!isset($matches[1][0])){
            return $tag;
        }

        // get all accepted words
        $all_stems = array();
        foreach ($matches[1] as $match){
            $match = explode (", ", $match);
            foreach ($match as $word){
                array_push($all_stems, $word);
            }
        }

        // get the needed word
        $stemmed_word = self::most_frequent_Word($all_stems);

        // return the word
        return $stemmed_word;
    }

    public function get_synonim ($tag){
//        $raw_synonims1 = 'Sense 1
//administration, disposal
//       => management, direction';
//        $matches1 = array ();
//        preg_match_all ("/\s+(.+)\s+=>/",
//            $raw_synonims1, $matches1, PREG_PATTERN_ORDER);
//        var_dump($raw_synonims1);
//        var_dump($matches1);
//
//        $raw_synonims1 = 'Sense 1
//Kennedy, Jack Kennedy, John Fitzgerald Kennedy, JFK, President Kennedy, President John F. Kennedy
//       INSTANCE OF=> President of the United States, United States President, President, Chief Executive';
//        $matches1 = array ();
//        preg_match_all ("/\s+(.+)\s+=>/",
//            $raw_synonims1, $matches1, PREG_PATTERN_ORDER);
//        var_dump($raw_synonims1);
//        var_dump($matches1);
//        exit();


        // cmd commend
        $wn_command = '"C:/Program Files (x86)/WordNet/2.1/bin/wn" "'.$tag.'" "-synsn"';
        $raw_synonims = shell_exec ($wn_command);

        // if the word exist
        if (! $raw_synonims) {
            return null;
        }

        // get the result of cmmend
        $matches = array ();
        preg_match_all ("/\s+(.+)\s+.+?=>/",
            $raw_synonims, $matches, PREG_PATTERN_ORDER);
//        var_dump($raw_synonims);

        // if no matched result
        if(!isset($matches[1][0])){
            return null;
        }

//        dd($matches);
        // get all accepted words
        $expanded_query = array();

        foreach ($matches[1] as $match){
            $match = explode (", ", $match);
            foreach ($match as $word){
                array_push($expanded_query, strtolower($word));
            }
        }

        // remove repeated element
        $expanded_query = array_unique($expanded_query);

        // return the word
        return $expanded_query;
    }

    public function get_hypernyms ($tag){
//        $raw_synonims1 = 'Sense 1
//Kennedy, Jack Kennedy, John Fitzgerald Kennedy, JFK, President Kennedy, President John F. Kennedy
//       INSTANCE OF=> President of the United States, United States President, President, Chief Executive
//           => head of state, chief of state
//               => representative
//                   => negotiator, negotiant, treater
//                       => communicator
//                           => person, individual, someone, somebody, mortal, soul
//                               => organism, being
//                                   => living thing, animate thing
//                                       => object, physical object
//                                           => physical entity
//                                               => entity
//                               => causal agent, cause, causal agency
//                                   => physical entity
//                                       => entity
//
//Sense 2
//Kennedy, Kennedy Interrnational, Kennedy International Airport
//       INSTANCE OF=> airport, airdrome, aerodrome, drome
//           => airfield, landing field, flying field, field
//               => facility, installation
//                   => artifact, artefact
//                       => whole, unit
//                           => object, physical object
//                               => physical entity
//                                   => entity';
//        $matches1 = array ();
//        preg_match_all ("/\s+(.+)\s+=>/",
//            $raw_synonims1, $matches1, PREG_PATTERN_ORDER);
//        var_dump($raw_synonims1);
//        var_dump($matches1);
//
//        $raw_synonims1 = 'Sense 1
//Kennedy, Jack Kennedy, John Fitzgerald Kennedy, JFK, President Kennedy, President John F. Kennedy
//       INSTANCE OF=> President of the United States, United States President, President, Chief Executive';
//        $matches1 = array ();
//        preg_match_all ("/\s+(.+)\s+=>/",
//            $raw_synonims1, $matches1, PREG_PATTERN_ORDER);
//        var_dump($raw_synonims1);
//        var_dump($matches1);
//        exit();
        ini_set("xdebug.var_display_max_children", -1);
        ini_set("xdebug.var_display_max_data", -1);
        ini_set("xdebug.var_display_max_depth", -1);

        // cmd commend
        $wn_command = '"C:/Program Files (x86)/WordNet/2.1/bin/wn" "'.$tag.'" "-hypen"';
        $raw_synonims = shell_exec ($wn_command);

        // if the word exist
        if (! $raw_synonims) {
            return null;
        }

        // get the result of cmmend
        $matches = array ();
        preg_match_all ("/\s+(.+)\s+.+?=>/",
            $raw_synonims, $matches, PREG_PATTERN_ORDER);

        // if no matched result
        if(!isset($matches[1][0])){
            return null;
        }

//        dd($matches);
        // get all accepted words
        $expanded_query = array();

        foreach ($matches[1] as $match){
            $match = explode (", ", $match);
            foreach ($match as $word){
                array_push($expanded_query, strtolower($word));
            }
        }

        // remove repeated element
        $expanded_query = array_unique($expanded_query);

        // return the word
        return $expanded_query;
    }

    public static function most_frequent_Word($arr){
        // new array containing frequency of values of $arr
        $arr_freq = array_count_values($arr);

        // arranging the new $arr_freq in decreasing
        // order of occurrences
        arsort($arr_freq);

        // $new_arr containing the keys of sorted array
        $new_arr = array_keys($arr_freq);

        // Second most frequent element
        return $new_arr[0];
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
							FROM `terms`, `term_documents`
							WHERE `terms`.`term_id` = `term_documents`.`term_id` AND
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
								FROM `terms`, `term_documents`
								WHERE `terms`.`term_id` = `term_documents`.`term_id` AND
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
					(SELECT SUM(`term_frequently`) as `tf`, `term_documents`.`document_id`
					FROM `terms`, `term_documents` 
					WHERE `terms`.`term_id` = `term_documents`.`term_id` AND  
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

    public static function getNeededDocuments($unNeededDocs){
	    if($unNeededDocs != null) {
            $sql = "SELECT `document_id`,`document_title` FROM `documents` WHERE `document_id` NOT IN (";
            foreach ($unNeededDocs as $elem) {
                $sql .= (string)$elem . ",";
            }
            $sql = rtrim($sql, ',');
            $sql .= ")";

        }else {
            $sql = "SELECT `document_id`,`document_title` FROM `documents`";
        }
        $result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    public static function getUnNeededDocuments($unNeededDocs){
	    if($unNeededDocs != null) {
            $sql = "SELECT `document_id`,`document_title` FROM `documents` WHERE `document_id` IN (";
            foreach ($unNeededDocs as $elem) {
                $sql .= (string)$elem . ",";
            }
            $sql = rtrim($sql, ',');
            $sql .= ")";

            $result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
            $data = array();
            while( $row = mysqli_fetch_assoc($result) ){
                $data[] = $row;
            }
            return $data;
        }else{
            return null;
        }

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
		$sql = "SELECT `term_id` FROM `term_documents` 
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
		$sql = "DELETE FROM `term_documents`
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
		$sql = "TRUNCATE TABLE `term_documents`";
		$result = mysqli_query(self::$conn, $sql) or die(mysqli_error(self::$conn));
		// delete all files from documents directory
		array_map('unlink', glob('documents/' . '*.txt'));
	}

	public static function relevanceByDoc($arr, $doc_id){
	    foreach ($arr as $elem){
	        if($elem['document_id'] == $doc_id){
	            return $elem['relevance_val'];
            }
        }
        return 0;
    }

    public static function notexisteDoc($arr1, $arr2){
        $arr1_id = array();
        $arr2_id = array();
	    foreach ($arr1 as $elem){
            array_push($arr1_id, $elem['document_id']);
        }
        foreach ($arr2 as $elem){
            array_push($arr2_id, $elem['document_id']);
        }
        $result=array_diff($arr2_id, $arr1_id);
        return $result;
    }

    public static function ObjByDocId($arr, $doc_id){
        foreach ($arr as $elem){
            if($elem['document_id'] == $doc_id){
                return $elem;
            }
        }
        return null;
    }

    public static function mergeNormalWithSemantic($normal, $semantic){

	    foreach ($normal as $index => $elem){
            $normal[$index]['relevance_val'] += self::relevanceByDoc($semantic, $elem['document_id']);
        }

        $diff = self::notexisteDoc($normal, $semantic);
        foreach ($diff as $elem){
            array_push($normal, self::ObjByDocId($semantic, $elem));
        }

        return $normal;
    }
}


?>
