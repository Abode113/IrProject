<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;
use App\Term_document;


class Document extends Model {

    public static $tableName = 'documents';
    public static $tb_id = 'document_id';
    public static $tb_document_title = 'document_title';
    public static $tb_terms_count = 'terms_count';


    function __construct(){

    }

    function insert($docName, $termCount){

        $this->document_title = $docName;
        $this->terms_count = $termCount;
        $id = $this->save();

        return $id;
    }

    function getData($arr){
        $documents = DB::table('documents')
            ->whereIn('document_id', $arr)
            ->get()->toArray();
        $Data = array();
        foreach ($documents as $item){
            $obj = array();
            $obj['document_id'] = $item->document_id;
            $obj['document_title'] = $item->document_title;
            $obj['terms_count'] = $item->terms_count;
            $obj['updated_at'] = $item->updated_at;
            $obj['created_at'] = $item->created_at;
            array_push($Data, $obj);
        }
        return $Data;
    }

    function getDocumentById($id){
        $documents = DB::table('documents')
            ->where('document_id', $id)
            ->get()->first();

        $obj = array();
        $obj['document_id'] = $documents->document_id;
        $obj['document_title'] = $documents->document_title;
        $obj['terms_count'] = $documents->terms_count;
        $obj['updated_at'] = $documents->updated_at;
        $obj['created_at'] = $documents->created_at;

        return $obj;
    }

    function AddDocmentData($arr){

        foreach ($arr as $index => $elem){
            //dd($elem['document_id']);
            $DocObj = self::getDocumentById($elem['document_id']);
            $arr[$index]['document_title'] = $DocObj['document_title'];
            $arr[$index]['document_Link'] = "http://127.0.0.1:8000/documents/" . $DocObj['document_title'];
        }
        return $arr;
    }


    function getDocumentByTremID($term_id){
        $documents = DB::table(self::$tableName)
            ->join(Term_document::$tableName, Term_document::$tableName . '.' . Term_document::$tb_document_id, '=', self::$tableName . '.' . self::$tb_id)
            ->join('terms', 'terms.term_id', '=', Term_document::$tableName . '.' . Term_document::$tb_term_id)
            ->where('terms.term_id', $term_id)
            ->get()->toArray();

        $documents_arr = array();
        foreach ($documents as $document) {
            $arr_obj = array();
            $arr_obj['document_id'] = $document->document_id;
            $arr_obj['document_title'] = $document->document_title;
            $arr_obj['terms_count'] = $document->terms_count;
            $arr_obj['updated_at'] = $document->updated_at;
            $arr_obj['created_at'] = $document->created_at;
            $arr_obj['term_id'] = $document->term_id;
            $arr_obj['term_frequently'] = $document->term_frequently;
            $arr_obj['locations'] = $document->locations;
            $arr_obj['document_frequently'] = $document->document_frequently;
            $arr_obj['term'] = $document->term;

            array_push($documents_arr, $arr_obj);
        }

        return $documents_arr;
    }













}

?>
