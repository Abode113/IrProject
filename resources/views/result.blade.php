@extends('BaseView')


@section('body')
    <?php
        //dd($Data['Content']['Data']);
        if(isset($Data['Content']['Data'])){
            if(isset($Data['Content']['Data'][0])){
                $content = $Data['Content']['Data'][0];
            }
            if(isset($Data['Content']['Data'][1])){
                $MostPropableWord = $Data['Content']['Data'][1];
                if(isset($Data['Content']['Data'][1][0])){
                    $MostPropableWord_statment = $Data['Content']['Data'][1][0];
                }
            }
            if(isset($Data['Content']['Data'][2])){
                $removed_stop_words = $Data['Content']['Data'][2];
            }
        }
        $query = '';
        if(isset($Data['Content']['query'])){
            $query = $Data['Content']['query'];
        }
        //dd($MostPropableWord_statment);
    //dd($query);
    //var_dump('hey');
    //dd($content);
    ?>

    <section class="jumbotron text-center">
        <div class="container" style="margin-top: -70px;">
            <div class="row">
                <div class="col-md-6">
                    <div class="bounce" style="width: 20%;margin-top: -34px;">
                        <span class="letter">A</span>
                        <span class="letter">b</span>
                        <span class="letter">o</span>
                        <span class="letter">d</span>
                        <span class="letter">e</span>
                    </div>
                    <form method="post" action="{{route('search')}}">
                        {{ csrf_field() }}
                        <div class="input-group" style="width: 50%">
                            <input type="text" class="form-control" name="Query" id="q" placeholder="Search ..." value="{{$query}}" style="margin-top:-45px">
                            <input type="submit"
                                   style="position: absolute; left: -9999px; width: 1px; height: 1px;"
                                   tabindex="-1" />
                        </div>

                        {{--<div class="input-group">--}}
                            {{--<div class="checkbox">--}}
                                {{--<label style="font-size:large"><input id="s" name="s" type="checkbox" value="1"> Semantic ?</label>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    </form>
                    <?php
                    //dd('hey');
                    //dd(isset($MostPropableWord_statment));
                    ?>
                    @if(isset($MostPropableWord_statment))
                        <form method="post" action="{{route('search')}}" id="anchorForm">
                            {{ csrf_field() }}
                            <input type="text" class="form-control hidden" name="Query" value="{{$MostPropableWord_statment}}">
                            <h3 style="text-align: left;">
                                do you Mean :
                                <a onclick="$('#anchorForm').submit()" href="javascript:void(0)">{{$MostPropableWord_statment}}</a>
                            </h3>
                        </form>
                    @else
                        <div style="height: 25px;"></div>
                    @endif
                </div>
                @if(isset($removed_stop_words))
                    <div class="col-md-6">
                        <!-- PRODUCT LIST -->
                        <div class="box box-primary" style="margin-top: 69px;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Query after removing stop words</h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <ul class="products-list product-list-in-box">
                                    <li class="item">
                                        <div class="product-info">
                                            <span class="product-description">
                                              {{$removed_stop_words}}
                                            </span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="container">
                <div class="row">
                    <div class="">
                        <!-- Nav tabs -->
                        @if(isset($content))
                            @foreach($content as $elem)
                                <div class="card">
                                    <div class="tab-content">
                                        <div role="tabpanel" class="tab-pane active" id="home">
                                            <a href="{{$elem['document_Link']}}"><legend>{{$elem['document_title']}}</legend></a>
                                            <p>relevance value = {{$elem['relevance_val']}}</p> <br>
                                            <?php
                                            $indexes = array_keys($elem['token']);
                                            ?>
                                            @foreach($indexes as $token_index_val)
                                                <p>
                                                    {{$token_index_val}} :
                                                    @foreach($elem['token'][$token_index_val] as $match)
                                                        ( {{$match[0]}}, {{$match[1]}} )
                                                    @endforeach
                                                </p>
                                                <br>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

