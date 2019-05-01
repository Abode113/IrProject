@extends('BaseView')


@section('body')
<?php
if(isset($Data['Content'])){
    $Data = $Data['Content']['Data'];
    $main_doc = $Data[0];
    $otherSimilarDoc = $Data[1];
    //dd($otherSimilarDoc);
}
?>
    <section class="jumbotron text-center">
        <div class="container" style="margin-top: -70px;">
            <div class="row">
                <div class="col-md-6">
                    <div class="bounce" style="width: 45%;">
                        <span class="letter">S</span>
                        <span class="letter">I</span>
                        <span class="letter">M</span>
                        <span class="letter">I</span>
                        <span class="letter">L</span>
                        <span class="letter">A</span>
                        <span class="letter">R</span>
                        <span class="letter">I</span>
                        <span class="letter">T</span>
                        <span class="letter">Y</span>
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="row">
                    <div class="">
                        <div class="card">
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="home">
                                <a href="#"><legend>{{$main_doc}}</legend></a>
                                <p>relevance Document Is : </p> <br>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @foreach($otherSimilarDoc as $elem)
            <div class="container">
                <div class="row">
                    <div class="">
                        <div class="card">
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="home">
                                    <a href="#"><legend>{{$elem[0]}}</legend></a>
                                    <p>relevance = {{$elem[1]}}</p> <br>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

        </div>
    </section>

@endsection
