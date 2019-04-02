<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;
use App\Document;

class Term extends Model {

    public static $tableName = 'terms';
    public static $tb_term_id = 'term_id';
    public static $tb_document_frequently = 'document_frequently';
    public static $tb_term = 'term';
    public static $tb_updated_at = 'updated_at';
    public static $tb_created_at = 'created_at';


    function __construct(){

    }

    function getAllTerms(){

        $Data = DB::table(self::$tableName)
            ->join('term_document', 'term_document.term_id', '=', self::$tableName . '.' . self::$tb_term_id)
            ->join(Document::$tableName, Document::$tableName . '.' . Document::$tb_id, '=', 'term_document.document_id')
            ->select(self::$tableName . '.' . self::$tb_term_id,
                    self::$tableName . '.' . self::$tb_term,
                    self::$tableName .  '.' . self::$tb_document_frequently)
            ->distinct()
        ->get()->toArray();

        return $Data;
    }

    function getTermsByDocumentID($Document_id){
        $documents = DB::table('documents')
            ->join(Term_document::$tableName, Term_document::$tableName . '.' . Term_document::$tb_document_id, '=', 'documents.document_id')
            ->join(self::$tableName, self::$tableName . '.' . self::$tb_term_id, '=', Term_document::$tableName . '.' . Term_document::$tb_term_id)
            ->where('documents.document_id', $Document_id)
            ->get()->toArray();

        return $documents;
    }

    function insert_conn($term, $document_frequently, $conn){

        $sql = "INSERT INTO `terms`
				(`term`, `document_frequently`) 
				VALUES ('".$term."',".$document_frequently.") 
				ON DUPLICATE KEY UPDATE 
				`term`='".$term."', `document_frequently`=`document_frequently`+".$document_frequently.", 
				`term_id` = LAST_INSERT_ID(`term_id`)";
        $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
        $termID = mysqli_insert_id($conn);

        return $termID;
    }

    function ProcessRelevance($relevence_docs, $ngram_relevence_docs){

        $MaxDifference = self::getMaxDifference($relevence_docs);

        $MaxRelevanceValue = self::getMaxRelevancealue($ngram_relevence_docs);

        $Range = self::GetRange($MaxDifference, $MaxRelevanceValue);

        $ngram_relevence_docs = self::editeRelevance($ngram_relevence_docs, $Range);

        return $ngram_relevence_docs;
    }

    function getMaxDifference($relevence_docs){

        usort($relevence_docs, function($a, $b) { // anonymous function
            if ($a['relevance_val'] == $b['relevance_val']) {
                return 0;
            }
            return ($a['relevance_val'] > $b['relevance_val']) ? -1 : 1;
        });

        $max = 0;
        for($i = 0; $i < count($relevence_docs) - 1; $i++){
            $difference = $relevence_docs[$i]['relevance_val'] - $relevence_docs[$i + 1]['relevance_val'];
            if($max < $difference){
                $max = $difference;
            }
        }
        return $max;
    }

    function getMaxRelevancealue($ngram_relevence_docs){
        //dd($ngram_relevence_docs);
        $ngram_relevence_docs = array_values($ngram_relevence_docs);
        $max = 0;
        for ($i = 0; $i < count($ngram_relevence_docs); $i++){
            $MaxRelevancealue = $ngram_relevence_docs[$i]['relevance_val'];
            if($max < $MaxRelevancealue){
                $max = $MaxRelevancealue;
            }
        }

        return $max;
    }

    function GetRange($MaxDifference, $MaxRelevanceValue){
        if($MaxDifference != 0){
            //return bcdiv($MaxDifference, $MaxRelevanceValue, 5);
            return 0;
        }
        return 1;

//        $divideOn = $MaxDifference * $MaxRelevanceValue;
//        if($divideOn == 0){
//            $divideOn = $MaxRelevanceValue * 10;
//        }
    }

    function editeRelevance($ngram_relevence_docs, $Range){

        foreach ($ngram_relevence_docs as $index => $elem){
            $ngram_relevence_docs[$index]['relevance_val'] = $elem['relevance_val'] * $Range;

//            $ngram_relevence_docs[$index]['relevance_val'] = self::divideFloat($elem['relevance_val'], $Range, 5);



//            foreach ($ngram_relevence_docs[$index]['token'] as $t_index => $token){
//                $indexes = array_keys($ngram_relevence_docs[$index]['token']);
//                foreach ($indexes as $elem_index => $elem_token){
//                    foreach ($ngram_relevence_docs[$index]['token'][$elem_token] as $ngram_index => $ngram_elem){
//                        $ngram_relevence_docs[$index]['token'][$elem_token][$ngram_index][1] =
//                            bcdiv($ngram_relevence_docs[$index]['token'][$elem_token][$ngram_index][1], $Range, 10);
//                    }
//                }
//            }
        }
        return $ngram_relevence_docs;
    }

    function array_merge_relvance($relevence_docs, $ngram_relevence_docs){

        if(count($relevence_docs) < 1){
            return $ngram_relevence_docs;
        }
        foreach ($relevence_docs as $index => $relvence){
            foreach ($ngram_relevence_docs as $n_index => $n_relvence){
                if($relevence_docs[$index]['document_id'] == $n_relvence['document_id']){
                    $relevence_docs[$index]['relevance_val'] += $n_relvence['relevance_val'];
                    $relevence_docs[$index]['token'] = self::merge_duplicate($relevence_docs[$index]['token'], $n_relvence['token']);
                    unset($ngram_relevence_docs[$n_index]);
                }
            }
        }
        if(count($ngram_relevence_docs) > 0){
            $relevence_docs = array_merge($relevence_docs, $ngram_relevence_docs);
        }
        return $relevence_docs;
    }

    function merge_duplicate($arr1, $arr2){
        $arr1_indexes = array_keys($arr1);
        $arr2_indexes = array_keys($arr2);
        foreach ($arr2_indexes as $arr2_indexes_index => $arr2_indexes_value){
            $continue = true;
            foreach ($arr1_indexes as $arr1_indexes_value){
                if($arr1_indexes_value == $arr2_indexes_value){
                    $continue = false;
                    break;
                }
            }
            if($continue){
                $arr1[$arr2_indexes_value] = $arr2[$arr2_indexes_value];
            }else{
                $arr1[$arr2_indexes_value] = array_merge($arr1[$arr2_indexes_value], $arr2[$arr2_indexes_value]);
            }
        }
        return $arr1;
    }

    function getMaxNgramLength($arr){
        $max = 0;
        foreach ($arr as $doc_item){
            $value = strlen($doc_item->term);
            if($value > $max){
                $max = $value;
            }
        }
        return $max;
    }

    function unseen($arr, $tokens){
        $new_arr = array();
        foreach ($tokens as $elem){
            array_push($new_arr, $elem->term);
        }
        return array_diff($arr, $new_arr);
    }

    function Get_nGram_relevance($arr, $relevence_docs){

        $ngram_data = DB::table('term_document')
            ->join('terms', 'terms.term_id', '=', 'term_document.term_id')
            ->join('documents', 'documents.document_id', '=', 'term_document.document_id')
            ->select('term', 'document_frequently', 'documents.document_id', 'term_frequently', 'terms_count')
            ->Where(function ($query) use($arr) {
                for ($i = 0; $i < count($arr); $i++){
                    $query->orwhere('term', 'like',  '%' . $arr[$i] .'%');
                }
            })->get();

        $ngram_relevence_docs = array();

        $MaxLen = self::getMaxNgramLength($ngram_data);
        foreach($ngram_data as $doc_item){

            if(!isset($ngram_relevence_docs[$doc_item->document_id])){
                $ngram_relevence_docs[$doc_item->document_id]['document_id'] = $doc_item->document_id;
                $ngram_relevence_docs[$doc_item->document_id]['relevance_val'] = 0;
                $ngram_relevence_docs[$doc_item->document_id]['token'] = [];
            }
            foreach ($arr as $elem){
                $editeDistance = levenshtein($elem, $doc_item->term);
                $elem_length = strlen($elem);
                $term_length = strlen($doc_item->term);
//                    $max_length = $term_length;
//                    if ($elem_length > $term_length){
//                        $max_length = $elem_length;
//                    }

                $relevance_val = $MaxLen - $editeDistance;

                if($relevance_val > 1) {
                    $ngram_relevence_docs[$doc_item->document_id]['relevance_val'] += $relevance_val;
                    if(isset($ngram_relevence_docs[$doc_item->document_id]['token'][$doc_item->term])){
                        array_push(
                            $ngram_relevence_docs[$doc_item->document_id]['token'][$doc_item->term],
                            [$elem, $relevance_val]);
                    }else{
                        $ngram_relevence_docs[$doc_item->document_id]['token'][$doc_item->term] = array();
                        array_push(
                            $ngram_relevence_docs[$doc_item->document_id]['token'][$doc_item->term],
                            [$elem, $relevance_val]);
                    }
                }
            }
        }
        return self::ProcessRelevance($relevence_docs, $ngram_relevence_docs);
    }

    function GetMostPropableWordUnseen($queryStatment, $unseen_tokens, $unseen_docs){

        $unseen_tokens_Prop = self::unseen_tokens_Prop($unseen_tokens);

        foreach ($unseen_docs as $index => $elem){
            //dd($index);

            foreach (array_keys($elem['token']) as $key){
                foreach ($elem['token'][$key] as $item){
                    $unseen_tokens_Prop = self::Addto_unseen_tokens_Prop($unseen_tokens_Prop, $item, $key);
                }
            }
        }

        $arrayOfPropableWordUnseen = self::arrayOfPropableWordUnseen($unseen_tokens_Prop);


        foreach ($arrayOfPropableWordUnseen as $elem){
            $queryStatment = str_replace($elem[0], $elem[1], $queryStatment);
        }
        $Res_arrayOfPropableWordUnseen = [$queryStatment, $arrayOfPropableWordUnseen];
        return $Res_arrayOfPropableWordUnseen;
    }
    function unseen_tokens_Prop($unseen_tokens){
        $arr = array();
        foreach ($unseen_tokens as $elem){
            array_push($arr, [$elem, array()]);
        }
        return $arr;
    }

    function Addto_unseen_tokens_Prop($unseen_tokens_Prop, $item, $key){
        //$item[0] token
        //$unseen_tokens_Prop array of token

        //$item[1] relevance
        //$key propable word
        foreach ($unseen_tokens_Prop as $index => $elem){
            if($elem[0] == $item[0]){
                $arr[$key] = $item[1];
                $unseen_tokens_Prop[$index][1] = array_merge($unseen_tokens_Prop[$index][1], $arr);
            }
        }
        return $unseen_tokens_Prop;
    }

    function arrayOfPropableWordUnseen($arr){
        $MostWordsArr = array();
        foreach ($arr as $elem){
            $word = self::MostRelevanceWord($elem[1]);
            array_push($MostWordsArr, [$elem[0], $word]);
        }
        return $MostWordsArr;
    }

    function MostRelevanceWord($arr){
        $word = '';
        $max = 0;
        foreach (array_keys($arr) as $elem){
            if($max < $arr[$elem]) {
                $max = $arr[$elem];
                $word = $elem;
            }
        }
        return $word;
    }

    function divideFloat($a, $b, $precision=3) {
        try {var_dump($a);
            dd($b);
            $a *= pow(10, $precision);

            $result = ((float)$a / $b);
//        $result=($a / $b);
            if (strlen($result) == $precision) return '0.' . $result;
            else return preg_replace('/(\d{' . $precision . '})$/', '.\1', $result);
        }catch(Exception $e){
            return 0;
        }
    }
}

?>















