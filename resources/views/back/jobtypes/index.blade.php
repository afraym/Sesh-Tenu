@extends('layouts.back')
@section('content')
@php
	$jobtypesData = $jobtypes ?? collect();
@endphp
<div class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<div class="row align-items-center">
						<div class="col">
							<h4 class="card-title mb-0">Job Types List / قائمة أنواع الوظائف</h4>
						</div>
						<div class="col text-right">
							<a href="{{ route('jobtypes.create') }}" class="btn btn-primary btn-sm">
								<i class="tim-icons icon-simple-add"></i> Add New Job Type
							</a>
						</div>
					</div>
				</div>
				<div class="card-body">
					@if(session('success'))
						<div class="alert alert-success alert-dismissible fade show" role="alert">
							<i class="tim-icons icon-check-2"></i> {{ session('success') }}
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
					@endif

					@if($jobtypesData->isEmpty())
						<div class="alert alert-info text-center">
							<i class="tim-icons icon-alert-circle-exc"></i>
							No job types found. Click "Add New Job Type" to create one.
						</div>
					@else
						<div class="table-responsive">
							<table class="table table-striped table-hover">
								<thead>
									<tr>
										<th>#</th>
										<th>Name / الإسم</th>
										<th>Description / الوصف</th>
										<th>Status / الحالة</th>
										<th>Created / تاريخ الإنشاء</th>
										<th class="text-center">Actions / الإجراءات</th>
									</tr>
								</thead>
								<tbody>
									@foreach($jobtypesData as $jobtype)
										<tr>
											<td>
												@if($jobtypesData instanceof \Illuminate\Pagination\AbstractPaginator)
													{{ $loop->iteration + ($jobtypesData->currentPage() - 1) * $jobtypesData->perPage() }}
												@else
													{{ $loop->iteration }}
												@endif
											</td>
											<td><strong>{{ $jobtype->name }}</strong></td>
											<td>{{ \Illuminate\Support\Str::limit($jobtype->description ?? 'N/A', 50) }}</td>
											<td>
												@if($jobtype->is_active)
													<span class="badge badge-success">مفعلة</span>
												@else
													<span class="badge badge-secondary">غير مفعلة</span>
												@endif
											</td>
											<td>{{ $jobtype->created_at ? $jobtype->created_at->format('Y-m-d') : 'N/A' }}</td>
											<td class="text-center">
												<div class="btn-group" role="group">
													<a href="{{ route('jobtypes.show', $jobtype->id) }}" class="btn btn-info btn-sm" title="View">
														<i class="tim-icons icon-notes"></i>
													</a>
													<a href="{{ route('jobtypes.edit', $jobtype->id) }}" class="btn btn-warning btn-sm" title="Edit">
														<i class="tim-icons icon-pencil"></i>
													</a>
													<form action="{{ route('jobtypes.destroy', $jobtype->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this job type?');">
														@csrf
														@method('DELETE')
														<button type="submit" class="btn btn-danger btn-sm" title="Delete">
															<i class="tim-icons icon-trash-simple"></i>
														</button>
													</form>
												</div>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>

						@if($jobtypesData instanceof \Illuminate\Pagination\AbstractPaginator)
							<div class="mt-3">
								{{ $jobtypesData->links() }}
							</div>
						@endif
					@endif
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
