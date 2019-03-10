@extends('BaseView')


@section('body')

    <?php
    $query = $Data['Content'];
    ?>

    <section class="jumbotron text-center">
        <div class="container">
            <h1 class="jumbotron-heading">IR System</h1>
            <small class="text-muted">based on xpath index</small>
            <p class="lead text-muted">write the query here :</p>
            <form method="get" action="result_xpath.php">
                <div class="input-group">
                    <input type="text" class="form-control" name="q_x" id="q_x" placeholder="Search ..." value="<?=htmlentities($query)?>">
                    <span class="input-group-btn">
					<button class="btn btn-secondary" type="submit">Go!</button>
				  </span>
                </div>
            </form>

            <h5>query before removing stopwords :</h5>
            <p style="color:red;">
                <?php
                if($query !== ''){
                    echo $query;
                }
                else{
                    echo "no words in your query !";
                    exit;
                }
                ?>
            </p>
            <h5>query after removing stopwords :</h5>
            <p style="color:blue;">
                <?php
                include_once 'remove_stop_words.php';
                $s1 = remove_stop_words(strtolower($query));
                if($s1 != ''){
                    echo $s1;
                }
                else{
                    echo "your query has no keyword !";
                    exit;
                }
                ?>
            </p>
            <h5>query after stemming :</h5>
            <p style="color:purple;">
                <?php
                if($s1 != ''){
                    include_once 'PhrasePorterStemmer.php';
                    $s2 = PhrasePorterStemmer::StemPhrase($s1);
                    $s3 = implode(' ', $s2);
                    echo $s3;
                }
                else{
                    echo "your query has no keyword !";
                    exit;
                }

                ?>
            </p>
            <?php
            if($s3 != ''){
                include_once 'query_index_xpath.php';
                submit_query($s3);
            }
            else{
                echo "your query has no keyword !";
                exit;
            }
            ?>
        </div>
    </section>

@endsection
