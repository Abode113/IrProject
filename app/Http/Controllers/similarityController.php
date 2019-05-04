<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use App\docsimilarity;
use App\test_docsimilaritie;
use App\Indexing;
use Session;
use DB;

class similarityController extends Controller
{
    public function __construct(){

    }

    public function browse(){

        $Data = DB::table('docsimilarities')
            ->select('doc_left_id')
            ->distinct()->get();

        $unneededDoc = array();

        foreach ($Data as $elem){
            array_push($unneededDoc, $elem->doc_left_id);
        }

        $indexing = new Indexing();
        $documents = $indexing->getNeededDocuments($unneededDoc);

        $Data = [
            'Content' => $documents
            ,'add' => true
        ];

        return view('similarityList', compact('Data'));
    }

    public function browseexist(){

        $Data = DB::table('docsimilarities')
            ->select('doc_left_id')
            ->distinct()->get();

        $unneededDoc = array();

        foreach ($Data as $elem){
            array_push($unneededDoc, $elem->doc_left_id);
        }

        $indexing = new Indexing();
        $documents = $indexing->getUnNeededDocuments($unneededDoc);

        $Data = [
            'Content' => $documents
            ,'add' => false
        ];

        return view('similarityList', compact('Data'));

    }

    public function deleteSimilarity(Request $request){
        $DocList = $request['DocList'];

        $data = DB::table('docsimilarities')->whereIn('doc_left_id', $DocList)->get();

        $indexing = new Indexing();
        $arr = array();
        $documents = $indexing->getUnNeededDocuments($arr);
        $Data = [
            'Content' => $documents
            ,'add' => false
        ];

        return view('similarityList', compact('Data'));

    }

    public function findSimilartiy($document_id, $view = true){

        $_docsimilarity = new docsimilarity();

        $FinalData = $_docsimilarity->findSimilartiy((int)$document_id);

        if($view){
            $Data = [
                'Content' => ['Data' => $FinalData]
            ];

            return view('similarity', compact('Data'));
        }else{
            return $FinalData;
        }
    }



    public function findsimilartiyForDoc($doc_id, $last_arr){

        $_docsimilarity = new docsimilarity();
        $FinalData = $_docsimilarity->findSimilartiy($doc_id);
        $arr = array();
        foreach ($FinalData[1] as $elem){
            $_docsimilarity_obj = new docsimilarity();
            $obj = array();
//            if(count($last_arr) > 300){
//                dd($last_arr);
//            }
            if(self::AddOrNot($FinalData[0][0], $elem[2], $last_arr)){
                $obj = [
                    'doc_left_id' => $FinalData[0][0],
                    'doc_id_right' => $elem[2],
                    'Similarity_value' => $elem[1],
                    'updated_at' => Carbon::now(),
                    'created_at' => Carbon::now()
                ];
                array_push($arr, $obj);
            }else {
                var_dump($FinalData[0][0] . ' with ' . $elem[2]);
            }
            //$_docsimilarity_obj->add($FinalData[0][0], $elem[2], $elem[1]);
        }
        return $arr;
    }

    public function AddOrNot($id_left, $id_right, $arr){
        foreach ($arr as $elem){
            if($elem['doc_left_id'] == $id_left){
                if($elem['doc_id_right'] == $id_right){
                    return false;
                }
            }else if ($elem['doc_id_right'] == $id_left){
                if($elem['doc_left_id'] == $id_right){
                    return false;
                }
            }
        }
        return true;
    }

    public function findsimilartiyAllDoc(Request $request){

        $DocList = $request['DocList'];
        ini_set('max_execution_time', 15000);
        try {
        DB::transaction(function() use($DocList) {


            $arr = self::getdocsimilaritiesData();
            $new_arr = array();
            foreach ($DocList as $elem){
                $new_arr = array_merge($new_arr, self::findsimilartiyForDoc((int)$elem, $arr));
                $arr = array_merge($arr, $new_arr);
                //self::findSimilartiy($elem->document_title, false);
            }

            docsimilarity::insert($new_arr);

            return Redirect::to("http://127.0.0.1:8000/seach_engine/");

        });
        }catch(\Exception $exc){
            var_dump('hey');
            dd($exc->getMessage());
        }
    }

    function getdocsimilaritiesData(){
        $Data = DB::table('docsimilarities')
            ->select('doc_left_id', 'doc_id_right')
            ->get();

        $arr = array();
        foreach ($Data as $elem){
            array_push($arr, ['doc_left_id' => $elem->doc_left_id, 'doc_id_right' => $elem->doc_id_right]);
        }

        return $arr;
    }

    function BackUpSimilarity(){
        $Data = DB::table('docsimilarities')
            ->get();

        $BackUpId = 0;
        // ------------------------------------------------
        $BackUpIds = array();
        $test_term_max_BackUpId = DB::table('test_docsimilarities')
            ->select('test_docsimilarities.BackUpId')->distinct()->get();
        foreach ($test_term_max_BackUpId as $elem){
            array_push($BackUpIds, $elem->BackUpId);
        }
        $BackUpId = self::GetRightBackUpId($BackUpIds);

        $arr = array();
        foreach ($Data as $elem){
            $obj = array();
            $obj = [
                'doc_left_id' => $elem->doc_left_id,
                'doc_id_right' => $elem->doc_id_right,
                'similarity_value' => $elem->similarity_value,
                'BackUpId' => $BackUpId,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now()
            ];

            array_push($arr, $obj);
        }

        foreach (array_chunk($arr,1000) as $ins) {
            try {
                DB::transaction(function() use ($ins) {
                    test_docsimilaritie::insert($ins);
                });
            }catch(\Exception $exc){
                var_dump('hey');
                dd($exc->getMessage());
            }

        }
        dd('done');

//        ini_set('max_execution_time', 15000);

    }

    function GetRightBackUpId($corpus_id){
        sort($corpus_id);
        $count = count($corpus_id);
        for ($i = 0; $i < $count - 1; $i++){
            if(!in_array($i, $corpus_id)){
                return $i;
            }
        }
        return $count;
    }

    function BrowseBackUp(){

        $Data = DB::table('test_docsimilarities')
            ->select('test_docsimilarities.BackUpId')->distinct()->get();

        $Data = [
            'Content' => $Data
        ];

        return view('similarityBackUp', compact('Data'));
    }

    function deleteBackUp(Request $request, $backup_id){
        $Data = DB::table('test_docsimilarities')
            ->where('test_docsimilarities.BackUpId', $backup_id)->delete();

        $Data = DB::table('test_docsimilarities')
            ->select('test_docsimilarities.BackUpId')->distinct()->get();

        $Data = [
            'Content' => $Data
        ];

        return view('similarityBackUp', compact('Data'));
    }

    function ApplyBackUp(Request $request, $backup_id){
        $Data = DB::table('test_docsimilarities')
            ->where('test_docsimilarities.BackUpId', $backup_id)
            ->get();

        $arr = array();
        foreach ($Data as $elem){
            $obj = array();
            $obj = [
                'doc_left_id' => $elem->doc_left_id,
                'doc_id_right' => $elem->doc_id_right,
                'similarity_value' => $elem->similarity_value,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now()
            ];

            array_push($arr, $obj);
        }

        foreach (array_chunk($arr,1000) as $ins) {
            try {
                DB::transaction(function() use ($ins) {
                    docsimilarity::insert($ins);
                });
            }catch(\Exception $exc){
                var_dump('hey');
                dd($exc->getMessage());
            }

        }


        $Data = DB::table('docsimilarities')
            ->select('doc_left_id')
            ->distinct()->get();

        $unneededDoc = array();

        foreach ($Data as $elem){
            array_push($unneededDoc, $elem->doc_left_id);
        }

        $indexing = new Indexing();
        $documents = $indexing->getNeededDocuments($unneededDoc);

        $Data = [
            'Content' => $documents
            ,'add' => false
        ];

        return view('similarityList', compact('Data'));
    }

    function deleteAllSimilarityNow(){

        $Data = DB::table('docsimilarities')->delete();


        $Data = DB::table('docsimilarities')
            ->select('doc_left_id')
            ->distinct()->get();

        $unneededDoc = array();

        foreach ($Data as $elem){
            array_push($unneededDoc, $elem->doc_left_id);
        }

        $indexing = new Indexing();
        $documents = $indexing->getNeededDocuments($unneededDoc);

        $Data = [
            'Content' => $documents
            ,'add' => true
        ];

        return view('similarityList', compact('Data'));
    }
}


?>
