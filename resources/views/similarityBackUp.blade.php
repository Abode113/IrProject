@extends('BaseView')


@section('body')

    <?php
    $BackUp = $Data['Content'];
    //dd($corpus[0]);
    ?>
    <div class="container">
        <br/>
        <a href="/" class="btn btn-primary">Home
        </a>
        <h3 style="font-weight: 300;">Current BackUp :</h3><br/>
        <div class="table-responsive">
            <table id="users_table" class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>SimilarityBackUp</th>
                    <th>BackUp</th>
                </tr>
                </thead>
                <tbody>
                <?php if(isset($BackUp[0])) {
                foreach($BackUp as $row){ ?>
                <tr>
                    <td>{{$row->BackUpId}}</td>
                    <td>
                        <form method="post" action="{{route('ApplySimilarityBackUp', $row->BackUpId)}}" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-success">Apply</button>
                        </form>
                    </td>
                    <td>
                        <form method="post" action="{{route('deleteSimilarityBackUp', $row->BackUpId)}}" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>

                <?php } } else { ?>
                <tr>
                    <td colspan="3" class="text-center">There is no Backup now</td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
@endsection
