@extends('BaseView')

@section('body')
    <?php
    if(isset($Data['Content']['Data'])){
        $terms = $Data['Content']['Data'];
    }

    ?>
    <div class="container">
        <br/>
        <a href="/" class="btn btn-primary">Home
        </a>
        <h3 style="font-weight: 300;">Current Term :</h3><br/>
        <div class="table-responsive">
            <table id="users_table" class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>Term Name</th>
                    <th>Document Frequently</th>
                </tr>
                </thead>
                <tbody>
                <?php
                //dd($terms);
                ?>
                @if(isset($terms))
                    @foreach($terms as $term)
                        <tr>
                            <td>
                                {{$term->term}}
                            </td>
                            <td>
                                <form method="post" action="{{route('TermDocs', $term->term_id)}}" id="anchorForm">
                                    {{ csrf_field() }}
                                    {{--<input type="text" class="form-control hidden" name="Term_id" value="">--}}
                                    <a onclick="$('#anchorForm').submit()" href="javascript:void(0)">{{$term->document_frequently}}</a>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @else
                <tr>
                    <td colspan="3" class="text-center">There is no Term now</td>
                </tr>
                @endif

                </tbody>
            </table>
        </div>
    </div>

@endsection
