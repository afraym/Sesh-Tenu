@extends('themes.blk.back.app')
@section('content')
@php
	$workersData = $workers ?? collect();
@endphp
<div class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<div class="row align-items-center">
						<div class="col">
							<h4 class="card-title mb-0">Workers List / قائمة العمال</h4>
						</div>
						<div class="col text-right">
							<a href="{{ route('workers.export.pdf.merged') }}" class="btn btn-sm btn-success" title="Export all as merged PDF (HTML-based)" target="_blank">
								<i class="tim-icons icon-paper"></i> PDF All (HTML)
							</a>
							<a href="{{ route('workers.export.wordpdf.all') }}" class="btn btn-sm btn-primary" title="Export all as merged PDF (DOCX->PDF via LibreOffice)" target="_blank">
								<i class="tim-icons icon-paper"></i> PDF All (Word)
							</a>
							<a href="{{ route('workers.export.word.all') }}" class="btn btn-sm btn-info" title="Export all as DOCX files in ZIP" target="_blank">
								<i class="tim-icons icon-single-copy-04"></i> Word All (ZIP)
							</a>
							<a href="{{ route('workers.create') }}" class="btn btn-primary btn-sm">
								<i class="tim-icons icon-simple-add"></i> Add New Worker
							</a>
						</div>
					</div>
					@if(auth()->check() && auth()->user()->isSuperAdmin())
					<div class="row mt-3">
						<div class="col-md-6">
							<form method="GET" action="{{ route('workers.index') }}">
								<div class="input-group">
									<select name="company_id" class="form-control">
										<option value="">All Companies / جميع الشركات</option>
										@foreach($companies ?? [] as $company)
											<option value="{{ $company->id }}" @if(isset($selectedCompanyId) && $selectedCompanyId == $company->id) selected @endif>
												{{ $company->name }}
											</option>
										@endforeach
									</select>
									<div class="input-group-append">
										<button class="btn btn-outline-secondary" type="submit">Filter</button>
									</div>
								</div>
							</form>
						</div>
					</div>
					@endif
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

					@if($workersData->isEmpty())
						<div class="alert alert-info text-center">
							<i class="tim-icons icon-alert-circle-exc"></i>
							No workers found. Click "Add New Worker" to create one.
						</div>
					@else
						<div class="table-responsive">
							<table class="table table-striped table-hover">
								<thead>
									<tr>
										<th>#</th>
										<th>Name / الاسم</th>
										<th>Company / الشركة</th>
										<th>Job Type / الوظيفة</th>
										<th>National ID</th>
										<th>Phone</th>
										<th>Join Date</th>
										<th>Payroll</th>
										<th class="text-center">Actions / الإجراءات</th>
									</tr>
								</thead>
								<tbody>
									@foreach($workersData as $worker)
										<tr>
											<td>
												@if($workersData instanceof \Illuminate\Pagination\AbstractPaginator)
													{{ $loop->iteration + ($workersData->currentPage() - 1) * $workersData->perPage() }}
												@else
													{{ $loop->iteration }}
												@endif
											</td>
											<td><strong>{{ $worker->name }}</strong></td>
											<td>{{ optional($worker->company)->name ?? 'N/A' }}</td>
											<td>{{ optional($worker->jobType)->name ?? 'N/A' }}</td>
											<td>{{ $worker->national_id }}</td>
											<td>{{ $worker->phone_number }}</td>
											<td>{{ $worker->join_date ? $worker->join_date->format('Y-m-d') : 'N/A' }}</td>
											<td>
												@if($worker->is_on_company_payroll)
													<span class="badge badge-success">Yes</span>
												@else
													<span class="badge badge-secondary">No</span>
												@endif
											</td>
											<td class="text-center">
												<div class="btn-group" role="group">
												
													{{-- <a href="{{ route('workers.export.wordpdf', $worker->id) }}" class="btn btn-sm btn-primary" title="Word to PDF" target="_blank">
														<i class="tim-icons icon-paper"></i>
													</a> --}}
													<a href="{{ route('workers.export.word', $worker->id) }}" class="btn btn-sm btn-default" title="Word">
														<i class="tim-icons icon-single-copy-04"></i>
													</a>
													<a href="{{ route('workers.show', $worker->id) }}" class="btn btn-info btn-sm" title="View">
														<i class="tim-icons icon-notes"></i>
													</a>
													<a href="{{ route('workers.edit', $worker->id) }}" class="btn btn-warning btn-sm" title="Edit">
														<i class="tim-icons icon-pencil"></i>
													</a>
													<form action="{{ route('workers.destroy', $worker->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this worker?');">
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

						@if($workersData instanceof \Illuminate\Pagination\AbstractPaginator)
							<div class="mt-3">
								{{ $workersData->links() }}
							</div>
						@endif
					@endif
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
