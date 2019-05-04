@extends('BaseView')

@section('script')
    function checkAll(){
        if($('#AllCheckBox').is(':checked')){
            $('.checkBox').click();
        } else {
            $('.checkBox').click();
        }
    }
@endsection


@section('body')
    <?php
    $documents = $Data['Content'];
    $add = $Data['add'];
    //dd($documents[0]);
    ?>
    <div class="container">
        <br/>
        <a href="/" class="btn btn-primary">Home
        </a>
        <form method="post" action="{{route('BackUpSimilarity')}}" enctype="multipart/form-data">
            {{ csrf_field() }}
            <button type="submit" class="btn btn-success">BackUp Similarity</button>
        </form>
        @if(!$add)
            <form method="post" action="{{route('deleteAllSimilarityNow')}}" enctype="multipart/form-data">
                {{ csrf_field() }}
                <button type="submit" class="btn btn-success">DeleteAll</button>
            </form>
        @endif
        @if($add)
            <form method="post" action="{{route('findsimilartiyalldoc')}}" enctype="multipart/form-data">
        @else
            <form method="post" action="{{route('deleteSimilarity')}}" enctype="multipart/form-data">
        @endif
            {{ csrf_field() }}
            @if($add)
            <button type="submit" class="btn btn-success">Calculate Similarity</button>
            @else
            <button type="submit" class="btn btn-success">Delete Similarity</button>
            @endif

            <h3 style="font-weight: 300;">Current Documents :</h3><br/>
            <div class="table-responsive">
                <table id="users_table" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>Title</th>
                        @if($add)
                            <th><input type="checkbox" onclick="checkAll()" id="AllCheckBox"/>Find Similarity</th>
                        @else
                            <th><input type="checkbox" onclick="checkAll()" id="AllCheckBox"/>Delete Similarity</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(isset($documents[0])) {
                    foreach($documents as $row){ ?>
                    <tr>
                        <td><?=$row['document_id']?></td>
                        <td><a href="{{ url('documents\\' . $row['document_title']) }}"><?=$row['document_title']?></a></td>
                        <td>
                            <input type="checkbox" name="DocList[]"class="checkBox" value="{{$row['document_id']}}"/>
                        </td>
                    </tr>

                    <?php } } else { ?>
                    <tr>
                        <td colspan="3" class="text-center">There is no Documents now</td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>

@endsection
