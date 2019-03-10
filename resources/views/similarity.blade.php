@extends('BaseView')


@section('body')


    <section class="jumbotronr">
        <div class="container">
            <h1 class="jumbotron-heading">IR System</h1>
            <p class="lead text-muted">write the two docs here to find similarity :</p>
            <form method="post" action="<?=$_SERVER['REQUEST_URI']?>" enctype="multipart/form-data">
                <div class="form-group" style="max-width:200px">
                    <input type="text" placeholder="doc id .." class="form-control" name="doc1" id="doc1">
                </div>
                <button name="submit" type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </section>
    <?php
    if(isset($_POST['doc1'])){

        function tf_idf_length($conn, $doc, $total_documents){
            $sql = "SELECT `terms`.`term_id`, `term_frequently`, `term_document`.`document_id`,
						`document_frequently`
						FROM `term_document`,`terms`
						WHERE `term_document`.`document_id` = ".(int)$doc." AND
						`term_document`.`term_id` = `terms`.`term_id`";
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

        function square($n)
        {
            return($n * $n);
        }

        function find_similarity($tf_idf,$tf_idf_rest){
            $sim = array();
            $doc_base = array_sum(array_map('square', $tf_idf));
            foreach($tf_idf_rest as $i => $tf_idf_single_other){
                $a = array();
                foreach($tf_idf as $key => $value){
                    if(array_key_exists($key, $tf_idf_single_other))
                        $a[$key] = $tf_idf_single_other[$key] * $value;
                }
                $sum = array_sum($a);
                $other_doc_base = array_sum(array_map('square', $tf_idf_single_other));
                $base = sqrt($doc_base * $other_doc_base);
                @$sim[$i] = $sum/$base;
            }
            return $sim;
        }

        $conn = mysqli_connect('localhost', 'root', '', 'search_engine');
        if( mysqli_connect_errno() ){
            throw new exception('Could not connect to DB');
        }

        $sql = "SELECT COUNT(*) as 'N' FROM `documents`";
        $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
        $total_documents = mysqli_fetch_assoc($result)['N'];

        $tf_idf = tf_idf_length($conn, $_POST['doc1'], $total_documents);
        //echo "<pre>"; print_r($tf_idf); echo "</pre>";

        $sql = "SELECT `document_id` FROM `documents` WHERE `document_id` != ".(int)$_POST['doc1'];
        $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

        $tf_idf_rest = array();
        while( $row = mysqli_fetch_assoc($result) ){
            $tf_idf_rest[$row['document_id']] = tf_idf_length($conn, $row['document_id'], $total_documents) ;
        }

        //echo "<pre>"; print_r($tf_idf_rest); echo "</pre>";

        $sim = find_similarity($tf_idf,$tf_idf_rest);
        arsort($sim);

        $i = 0;
        echo '<div class="container"><div class="card">
  				<ul class="list-group list-group-flush">';
        foreach($sim as $docID => $s){
            if($i <= 15){
                echo "
					<li class='list-group-item'>
						relevant doc number $i : <a href='/ir/documents/".$docID.".txt'>".$docID."</a></br>
					</li>
					";
                $i++;
            }
            else{
                break;
            }
        }
        echo "</ul>
			</div>
			</div>";


        echo "<pre>"; print_r($sim); echo "</pre>";

    }

    ?>

@endsection
