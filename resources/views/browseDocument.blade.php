@extends('BaseView')

@section('body')
<?php
$documents = $Data['Content'];
//dd($documents);
?>
<div class="container">
    <br/>
    <a href="/" class="btn btn-primary">Home
    </a>
    <h3 style="font-weight: 300;">Current Documents :</h3><br/>
    <div class="table-responsive">
        <table id="users_table" class="table table-striped table-bordered">
            <thead>
            <tr>
                <th>Id</th>
                <th>Title</th>
                <th>Delete?</th>
            </tr>
            </thead>
            <tbody>
            <?php if(isset($documents[0])) {
            foreach($documents as $row){ ?>
                <form method="post" action="{{route('deleteDocument', $row['document_id'])}}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <tr>
                        <td><?=$row['document_id']?></td>
                        <td><a href="{{ url('documents\\' . $row['document_title']) }}"><?=$row['document_title']?></a></td>
                        <td><button type="submit" class="btn btn-danger">Delete</button></td>
                    </tr>
                </form>
            <?php } } else { ?>
            <tr>
                <td colspan="3" class="text-center">There is no Documents now</td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

@endsection
