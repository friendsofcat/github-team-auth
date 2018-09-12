@extends('vendor.github-team-auth.default_layout')
@section('content')

    <h1>Add Team</h1>

    <div class="panel panel-default">
        <div class="panel-body">
            <label>
                Start adding Team for which the user should have access.
            </label>
        </div>
    </div>
    <br><br>
    <form action="{{route('github-team-auth.store_team')}}" method="POST">
        <div class="form-group">
            <div class="form-group">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <label for="name">Choose Organization</label>
                <select required="required" class="col-md-4 form-control" name="select_team">
                    <option value="" selected> none </option>
                    @foreach($list_teams as $list_team)
                        <option value="{{ $list_team['id'].':'.$list_team['name'] }}"> {{ $list_team['name'] }} </option>
                    @endforeach
                </select>
            </div>
        </div>
        <br>
        <div class="form-group">
            <div class="form-group">
                <label for="acl">ACL</label>
                <select required="required" class="col-md-4 form-control" name="acl">
                    <option value="" selected> none </option>
                    @foreach($team_grants as $team_grant)
                        <option value="{{ $team_grant['grant_name'] }}"> {{ $team_grant['grant_name'] }} </option>
                    @endforeach
                </select>
            </div>
        </div>

        <br><br>
        <a class="btn btn-primary" href="{{route('github-team-auth.index')}}" role="button"> << Return to index</a>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>



@endsection