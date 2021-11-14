@extends('app')

@section('content')
<div class="cotainer">
	<div class="row justify-content-center">
		<div class="col-md-10">
			<h3>Users</h3>
			
			<div class="row">
				<div class="col-md-3">
					Role
					<select id="role" class="form-control" name="role" multiple size="3">
						@foreach($roles as $role)
							@if(in_array($role->id, $roleParam))
							<option value="{{$role->id}}" selected>{{$role->name}}</option>
							@else
							<option value="{{$role->id}}">{{$role->name}}</option>
							@endif
						@endforeach
					</select>
				</div>
				<div class="col-md-3">
					Industry
					<select id="industry" class="form-control" name="industry" multiple size="3">
						@foreach($industries as $industry)
							@if(in_array($industry->id, $industryParam))
							<option value="{{$industry->id}}" selected>{{$industry->name}}</option>
							@else
							<option value="{{$industry->id}}">{{$industry->name}}</option>
							@endif
						@endforeach
					</select>
				</div>
				
				<div class="col-md-3">
					Sort By
					<select id="sort_by" class="form-control" name="sort_by" onchange="sortList(this.value)">
						<option value="1" @if($sortParam == '1')) selected @endif>All</option>
						<option value="2" @if($sortParam == '2')) selected @endif>New Registered Member</option>
						<option value="3" @if($sortParam == '3')) selected @endif>Profile Score</option>
					</select>
				</div>
				<input type="hidden" name="url" id="url" value="{{$url}}" />
			</div>
			<br>
			<div class="row">
				<div class="col-md-3">
					<button type="button" class="btn btn-dark btn-block" onclick="filterList();">Filter</button>
					<button type="button" class="btn btn-light btn-block" onclick="resetList();">Reset</button>
				</div>
			</div>
			<br>
			
			<table class="table table-bordered table-hover">
				<thead>
					<th>Name</th>
					<th>Email</th>
					<th>Role</th>
					<th>Industry</th>
				</thead>
				<tbody>
					@if ($users->count() == 0)
					<tr>
						<td colspan="5">No users to display.</td>
					</tr>
					@endif

					@foreach ($users as $user)
					<tr>
						<td>{{ $user->name }}</td>
						<td>{{ $user->email }}</td>
						<td>{{ $user->role[0]->name }}</td>
						<td>{{ $user->industry[0]->name }}</td>
					</tr>
					@endforeach
				</tbody>
			</table>

			{{ $users->links() }}

			<p>
				Displaying {{$users->count()}} of {{ $users->total() }} users(s).
			</p>
		</div>
	</div>
</div>
@endsection

<script type="text/javascript">
function filterList() {
	var role = $("#role").val();
	var industry = $("#industry").val();
	var role_param = role.join("_");
	var industry_param = industry.join("_");
	var url = $("#url").val();
	var sortOption = $("#sort_by").val();
	
	window.location.href = url + "/role:"+role_param+"/industry:"+industry_param+"/sort:"+sortOption;
}

function sortList(sortOption) {
	var role = $("#role").val();
	var industry = $("#industry").val();
	var role_param = role.join("_");
	var industry_param = industry.join("_");
	var url = $("#url").val();
	
	window.location.href = url + "/role:"+role_param+"/industry:"+industry_param+"/sort:"+sortOption;
}
function resetList() {
	var url = $("#url").val();
	window.location.href = url;
}
</script>