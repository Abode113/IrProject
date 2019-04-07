<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use DB;
use App\Indexing;
use App\deleteDocument;
use App\resetIndex;

use App\Term;
use App\Term_document;
use App\Document;
use App\test_Terms;
use App\test_term_document;
use App\test_documents;

class FileManagerController extends Controller
{
    public function __construct(){

    }

    public function uploade(Request $request){
//        dd($request->all());

        $indexing = new Indexing();
//        var_dump($request['submit']);exit();
        //include_once "indexing.php";
        $content = array();
        //dd($request['document']);
        if(isset($request['document'])){
//            echo "<pre>";
//            print_r($request["document"]);
//            echo "</pre>";exit();
            foreach ($_FILES["document"]["type"] as $num => $file){
                if($file == "text/plain" && $_FILES["document"]["size"][$num]){
                    $name = $_FILES["document"]["name"][$num];
                    $content[$name] = file_get_contents($_FILES["document"]["tmp_name"][$num]);
                    move_uploaded_file($_FILES["document"]["tmp_name"][$num], dirname(__FILE__). "\..\..\..\public\documents\\" . $name);
                }
            }
            $indexing->updateIndex($content);
//		echo "<pre>";
//		print_r($content);
//		echo "</pre>";exit();
        }else{
             // no uploaded document
            var_dump('no uploaded document');//exit();
        }
        return redirect('/');

    }

    public function deleteDocument(Request $request, $id){

        $deleteDocument = new deleteDocument();
        $deleteDocument->DeleteDocument($id);

        return redirect('/Document/browse/');
    }

    public function resetIndex(){

        $resetIndex = new resetIndex();
        $resetIndex->resetIndex();

        return redirect('/');
    }

    public function BackUpCorpus(Request $request){
        ini_set('max_execution_time', 1200);
        try {
            DB::transaction(function() use($request) {
                //dd('hey hey hey');
                $term = new Term();
                $term_document = new Term_document();
                $document = new  Document();

                $test_term = new test_terms();
                $test_term_document = new test_term_document();
                $test_documents = new test_documents();

                $corpus_num = 1;

                // ------------------------------------------------
                $corpus_id = array();
                $test_term_max_corpus_id = DB::table($test_term::$tableName)
                    ->max($test_term::$corpus_id);
                array_push($corpus_id, $test_term_max_corpus_id);
                $Allterm_max_corpus_id = DB::table($test_term_document::$tableName)
                    ->max($test_term_document::$corpus_id);
                array_push($corpus_id, $Allterm_max_corpus_id);
                $Allterm_max_corpus_id = DB::table($test_documents::$tableName)
                    ->max($test_documents::$corpus_id);
                array_push($corpus_id, $Allterm_max_corpus_id);

                $corpus_num = self::GetRightCorpus($corpus_id);



                // ------------------------------------------------
                $Allterm = DB::table($term::$tableName)
                    ->orderBy('term_id')
                    ->get()->toArray();

                foreach ($Allterm as $elem) {

                    $test_term_obj = new test_terms();
                    $test_term_obj->corpus_id = $corpus_num;
                    $test_term_obj->term_id = $elem->term_id;
                    $test_term_obj->document_frequently = $elem->document_frequently;
                    $test_term_obj->term = $elem->term;
                    $test_term_obj->updated_at = $elem->updated_at;
                    $test_term_obj->created_at = $elem->created_at;

                    $test_term_obj->save();
                }
                // ------------------------------------------------

                $Alldocument = DB::table($document::$tableName)
                    ->orderBy('document_title')
                    ->get()->toArray();

                foreach ($Alldocument as $elem) {

                    $test_document_obj = new test_documents();
                    $test_document_obj->corpus_id = $corpus_num;
                    $test_document_obj->document_id = $elem->document_id;
                    $test_document_obj->document_title = $elem->document_title;
                    $test_document_obj->terms_count = $elem->terms_count;
                    $test_document_obj->updated_at = $elem->updated_at;
                    $test_document_obj->created_at = $elem->created_at;

                    $test_document_obj->save();
                }
                // ------------------------------------------------
                $AlltermDoc = DB::table($term_document::$tableName)
                    ->orderBy('document_id')->orderBy('term_id')
                    ->get()->toArray();

                foreach ($AlltermDoc as $elem) {

                    $test_term_document_obj = new test_term_document();
                    $test_term_document_obj->corpus_id = $corpus_num;
                    $test_term_document_obj->term_id = $elem->term_id;
                    $test_term_document_obj->document_id = $elem->document_id;
                    $test_term_document_obj->term_frequently = $elem->term_frequently;
                    $test_term_document_obj->locations = $elem->locations;
                    $test_term_document_obj->updated_at = $elem->updated_at;
                    $test_term_document_obj->created_at = $elem->created_at;

                    $test_term_document_obj->save();
                }
                // ------------------------------------------------
                self::getCorpus();

            });



        }catch(\Exception $exc){
            var_dump('hey');
            dd($exc->getMessage());
        }
    }

    public function getCorpus(){
        $test_documents = new test_documents();

        $documents_corpuses = DB::table($test_documents::$tableName)
        ->select($test_documents::$tableName . '.' . $test_documents::$corpus_id)
        ->distinct()->get();

        $Data = [
            'Content' => $documents_corpuses
        ];

        return view('corpus', compact('Data'));

    }

    public function ApplyBackUp(Request $request, $corpus_is){
        //var_dump('ApplyBackUp');
        //dd($corpus_is);
        ini_set('max_execution_time', 1200);
        try {
            DB::transaction(function() use($request, $corpus_is) {

                $test_term = new test_terms();
                $test_term_document = new test_term_document();
                $test_documents = new test_documents();


                // ------------------------------------------------
                $Allterm = DB::table($test_term::$tableName)
                    ->where($test_term::$tableName . '.' . $test_term::$corpus_id, '=', $corpus_is)
                    ->orderBy('term_id')
                    ->get()->toArray();

                foreach ($Allterm as $elem) {

                    $test_term_obj = new Term();
                    $test_term_obj->term_id = $elem->term_id;
                    $test_term_obj->document_frequently = $elem->document_frequently;
                    $test_term_obj->term = $elem->term;
                    $test_term_obj->updated_at = $elem->updated_at;
                    $test_term_obj->created_at = $elem->created_at;

                    $test_term_obj->save();
                }
                // ------------------------------------------------

                $Alldocument = DB::table($test_documents::$tableName)
                    ->where($test_documents::$tableName . '.' . $test_documents::$corpus_id, '=', $corpus_is)
                    ->orderBy('document_title')
                    ->get()->toArray();

                foreach ($Alldocument as $elem) {

                    $test_document_obj = new Document();
                    $test_document_obj->document_id = $elem->document_id;
                    $test_document_obj->document_title = $elem->document_title;
                    $test_document_obj->terms_count = $elem->terms_count;
                    $test_document_obj->updated_at = $elem->updated_at;
                    $test_document_obj->created_at = $elem->created_at;

                    $test_document_obj->save();
                }
                // ------------------------------------------------
                $AlltermDoc = DB::table($test_term_document::$tableName)
                    ->where($test_term_document::$tableName . '.' . $test_term_document::$corpus_id, '=', $corpus_is)
                    ->orderBy('document_id')->orderBy('term_id')
                    ->get()->toArray();

                foreach ($AlltermDoc as $elem) {

                    $test_term_document_obj = new Term_document();
                    $test_term_document_obj->term_id = $elem->term_id;
                    $test_term_document_obj->document_id = $elem->document_id;
                    $test_term_document_obj->term_frequently = $elem->term_frequently;
                    $test_term_document_obj->locations = $elem->locations;
                    $test_term_document_obj->updated_at = $elem->updated_at;
                    $test_term_document_obj->created_at = $elem->created_at;

                    $test_term_document_obj->save();
                }
                // ------------------------------------------------
                self::getCorpus();

            });

            self::getCorpus();

        }catch(\Exception $exc){
            var_dump('hey');
            dd($exc->getMessage());
        }


    }

    public function deleteCorpusById(Request $request, $corpus_is){
        //var_dump('deleteCorpusById');

        //dd($corpus_is);

        $test_term = new test_terms();
        $test_term_document = new test_term_document();
        $test_documents = new test_documents();


        $test_term_tb = DB::table($test_term::$tableName)
            ->where($test_term::$tableName . '.' . $test_term::$corpus_id, '=', $corpus_is)
            ->delete();

        $test_term_document_tb = DB::table($test_term_document::$tableName)
            ->where($test_term_document::$tableName . '.' . $test_term_document::$corpus_id, '=', $corpus_is)
            ->delete();

        $test_documents_tb = DB::table($test_documents::$tableName)
            ->where($test_documents::$tableName . '.' . $test_documents::$corpus_id, '=', $corpus_is)
            ->delete();

        self::getCorpus();
    }

    function GetRightCorpus($corpus_id){
        sort($corpus_id);
        $count = count($corpus_id);
        for ($i = 0; $i < $count - 1; $i++){
            if(!in_array($i, $corpus_id)){
                return $i;
            }
        }
        return $count;
    }

}


?>
