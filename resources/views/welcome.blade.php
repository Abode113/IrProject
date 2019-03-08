<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>IR System</title>
        <link href="css/bootstrap-4.0.0-alpha.6-dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="style.css" rel="stylesheet">
    </head>
    <body>



        <section class="jumbotron text-center">
            <div class="container">


                <h1 class="jumbotron-heading">IR System</h1>
                <p class="lead text-muted">write the query here :</p>
                <form method="get" action="result.php">
                    <div class="input-group">
                        <input type="text" class="form-control" name="q" id="q" placeholder="Search ...">
                        <span class="input-group-btn">
                            <button class="btn btn-secondary" type="submit">Go!</button>
                          </span>
                    </div>
                    <div class="input-group">
                        <div class="checkbox">
                            <label style="font-size:large"><input id="s" name="s" type="checkbox" value="1"> Semantic ?</label>
                        </div>
                    </div>
                </form>



                <small class="text-muted">based on xpath index</small>
                <p class="lead text-muted">write the query here :</p>
                <form method="get" action="result_xpath.php">
                    <div class="input-group">
                        <input type="text" class="form-control" name="q_x" id="q_x" placeholder="Search ...">
                        <span class="input-group-btn">
                            <button class="btn btn-secondary" type="submit">Go!</button>
                          </span>
                    </div>
                </form>


            </div>
        </section>



        <section class="jumbotron text-center">
            <form method="post" action="{{route('uploadeFile')}}" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="document">Choose file
                        <input type="file" class="form-control-file"
                               name="document[]" id="document[]" aria-describedby="fileHelp" accept="text/plain" multiple>
                    </label>
                    <small id="fileHelp" class="form-text text-muted">add one document to your current corpus</small>
                </div>
                <button name="submit" type="submit" class="btn btn-primary">Add</button>
            </form>
            <br/>
            <a class="btn btn-success" href="view_documents.php">View Documents
            </a>
            <a class="btn btn-danger" href="reset_index.php">Reset Documents
            </a>
            <a class="btn btn-primary" href="similarity.php">Find Similarity
            </a>
        </section>



        <script src="js/jquery/jquery-3.1.1.min.js"></script>
        <script src="css/bootstrap-4.0.0-alpha.6-dist/js/bootstrap.min.js"></script>
    </body>
</html>
