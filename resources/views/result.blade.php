@extends('BaseView')


@section('body')
    <section class="jumbotron text-center">
        <div class="container">

            <div class="bounce" style="width: 20%">
                <span class="letter">A</span>
                <span class="letter">b</span>
                <span class="letter">o</span>
                <span class="letter">d</span>
                <span class="letter">e</span>
            </div>
            <form method="post" action="{{route('search')}}">
                {{ csrf_field() }}
                <div class="input-group" style="width: 50%">
                    <input type="text" class="form-control" name="Query" id="q" placeholder="Search ...">
                    <input type="submit"
                           style="position: absolute; left: -9999px; width: 1px; height: 1px;"
                           tabindex="-1" />
                </div>
                <div class="input-group">
                    <div class="checkbox">
                        <label style="font-size:large"><input id="s" name="s" type="checkbox" value="1"> Semantic ?</label>
                    </div>
                </div>
            </form>
            <div class="container">
                <div class="row">
                    <div class="">
                        <!-- Nav tabs -->
                        <div class="card">
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="home">
                                    <a href="#"><legend>Responsive Tabs</legend></a>
                                    <p>If you are on desktop Try resizing the window to mobile view to see the scroll effect</p>
                                    <p>Horizontal scrollable tabs</p>
                                    <p>Best for mobile view</p>
                                    <p>Similar to gravity MODE_SCROLLABLE in Android</p>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

