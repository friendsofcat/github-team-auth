@extends('vendor.github-team-auth.default_layout')
@section('content')

<h1>Settings for Organizations/Teams</h1>

<div class="panel panel-default">
    <div class="panel-body">
        <label>
            Start adding Teams we should look for access to at the org level.<br>
            Give an ACL if you want (optional) like read etc, so you can react to the event once the user logs in<br>
            and set them as needed in your system.
        </label>
    </div>
</div>
<br>
<a class="btn btn-primary" href="{{route('github-team-auth.add_org')}}" role="button">Add an Org</a>

<a class="btn btn-primary" href="{{route('github-team-auth.add_team')}}" role="button">Add an Team</a>

<br><br>

<label>
    Organizations:
</label>
@if(count($organizations) > 0)
    <table class="table table-striped table-bordered text-center">
        <thead>
        <tr>
            <th class="text-center">Org Id</th>
            <th class="text-center">Org Name</th>
            <th class="text-center">Delete</th>
        </tr>
        </thead>
        @foreach($organizations as $organization)
            <tr>
                <td>{{ $organization->id }}</td>
                <td>{{ $organization->org_name }}</td>
                <td>
                    <form action="{{ route('github-team-auth.org.delete', $organization->id) }}" method="POST" style="display: inline;"
                          onsubmit="if(confirm('Delete? Are you sure?')) { return true } else {return false };">
                        <input type="hidden" name="_method" value="DELETE">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>
@else
    <br>
    <label>
        No Data
    </label>
@endif
<br><br>


<label>
    Teams:
</label>
@if(count($teams) > 0)
    <table class="table table-striped table-bordered text-center">
        <thead>
        <tr>
            <th class="text-center">Team Id</th>
            <th class="text-center">Team Name</th>
            <th class="text-center">Acl</th>
            <th class="text-center">Delete</th>
        </tr>
        </thead>
        @foreach($teams as $team)
            <tr>
                <td>{{ $team->id }}</td>
                <td>{{ $team->team_name }}</td>
                <td>{{ $team->acl }}</td>
                <td>
                    <form action="{{ route('github-team-auth.team.delete', $team->id) }}" method="POST" style="display: inline;"
                          onsubmit="if(confirm('Delete? Are you sure?')) { return true } else {return false };">
                        <input type="hidden" name="_method" value="DELETE">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>
@else
    <br>
    <label>
        No Data
    </label>
@endif

@endsection