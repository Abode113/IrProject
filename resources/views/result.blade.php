@extends('BaseView')


@section('body')
    <?php
    $query = $Data['Content'];
    //$query['Query']
    //$query['semantic']
    ?>
    <section class="jumbotron text-center">
        <div class="container">
            <h1 class="jumbotron-heading">IR System</h1>
            <p class="lead text-muted">write the query here :</p>
            <form method="get" action="result.php">
                <div class="input-group">
                    <input type="text" class="form-control" name="q" id="q" value="<?=htmlentities($query['Query'])?>">
                    <span class="input-group-btn">
					<button class="btn btn-secondary" type="submit">Go!</button>
				  </span>
                </div>
                <div class="input-group">
                    <div class="checkbox">
                        <label style="font-size:large"><input id="s" name="s" type="checkbox" value="1"
                            <?php
                                if(isset($query['semantic']) && $query['semantic'] == 1)
                                    echo "checked";
                                ?>
                            > Semantic ?</label>
                    </div>
                </div>
            </form>
            <h5>query before removing stopwords :</h5>
            <p style="color:red;">
                <?php
                if($query['Query'] !== ''){
                    echo $query['Query'];
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
                $s1 = remove_stop_words(strtolower($query['Query']));
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
                include_once 'indexing.php';
                # with semantic we use the query before stemming
                if(isset($query['semantic']) && $query['semantic'] == 1){
                    Index::submit_semantic_query($s1);
                    exit;
                }
                # without semantic
                else
                    Index::submit_query($s3);
            }
            else{
                echo "your query has no keyword !";
                exit;
            }
            ?>

        </div>
    </section>


@endsection
