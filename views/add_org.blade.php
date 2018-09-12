@extends('vendor.github-team-auth.default_layout')
@section('content')

    <h1>Add Organization</h1>

    <div class="panel panel-default">
        <div class="panel-body">
            <label>
                Start adding Organization for which the user should have access.
            </label>
        </div>
    </div>
    <br><br>
    <form action="{{route('github-team-auth.store_org')}}" method="POST">
    <div class="form-group">
        <div class="form-group">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <label for="name">Choose Organization</label>
            <select required="required" class="col-md-4 form-control" name="organization">
                <option value="" selected> none </option>
                @foreach($list_organization as $organization)
                    <option value="{{ $organization['id'].':'.$organization['login'] }}"> {{ $organization['login'] }} </option>
                @endforeach
            </select>
        </div>
    </div>
        <br><br>
        <a class="btn btn-primary" href="{{route('github-team-auth.index')}}" role="button"> << Return to index</a>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>



@endsection