@extends('layouts.back')
@section('content')
<div class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h4 class="card-title mb-0">Job Type Details / تفاصيل نوع الوظيفة</h4>
					<a href="{{ route('jobtypes.index') }}" class="btn btn-secondary btn-sm">
						<i class="tim-icons icon-minimal-left"></i> Back
					</a>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table class="table">
							<tbody>
								<tr>
									<th style="width: 220px;">Name / الإسم</th>
									<td>{{ $jobtype->name }}</td>
								</tr>
								<tr>
									<th>Description / الوصف</th>
									<td>{{ $jobtype->description ?? 'N/A' }}</td>
								</tr>
								<tr>
									<th>Status / الحالة</th>
									<td>
										@if($jobtype->is_active)
											<span class="badge badge-success">Active</span>
										@else
											<span class="badge badge-secondary">Inactive</span>
										@endif
									</td>
								</tr>
								<tr>
									<th>Created At</th>
									<td>{{ $jobtype->created_at ? $jobtype->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="mt-3">
						<a href="{{ route('jobtypes.edit', $jobtype->id) }}" class="btn btn-warning btn-sm">
							<i class="tim-icons icon-pencil"></i> Edit
						</a>
						<form action="{{ route('jobtypes.destroy', $jobtype->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this job type?');">
							@csrf
							@method('DELETE')
							<button type="submit" class="btn btn-danger btn-sm">
								<i class="tim-icons icon-trash-simple"></i> Delete
							</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
