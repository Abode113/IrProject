@extends('BaseView')


@section('body')
    <section class="jumbotron text-center">
        <div class="container">

            <div class="bounce">
                <span class="letter">A</span>
                <span class="letter">b</span>
                <span class="letter">o</span>
                <span class="letter">d</span>
                <span class="letter">e</span>
            </div>
            <form method="post" action="{{route('xpathsearch')}}">
                {{ csrf_field() }}
                <div class="input-group">
                    <input type="text" class="form-control" name="q_Query" id="q_x" placeholder="Search ...">
                    <input type="submit"
                           style="position: absolute; left: -9999px; width: 1px; height: 1px;"
                           tabindex="-1" />
                </div>
            </form>

        </div>
    </section>
@endsection

