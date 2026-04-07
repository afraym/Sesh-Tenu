@extends('layouts.back')
@section('content')
@php
	$workersData = $workers ?? collect();
	$sort = $sort ?? request('sort', 'created_at');
	$direction = $direction ?? request('direction', 'desc');
	$sortUrl = function (string $column) use ($sort, $direction) {
		$nextDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';
		return route('workers.index', array_merge(request()->query(), [
			'sort' => $column,
			'direction' => $nextDirection,
		]));
	};
	$sortIcon = function (string $column) use ($sort, $direction) {
		if ($sort !== $column) {
			return ' <i class="fas fa-sort text-muted"></i>';
		}

		return $direction === 'asc'
			? ' <i class="fas fa-sort-up"></i>'
			: ' <i class="fas fa-sort-down"></i>';
	};
	$workerSelectableRowClass = 'worker-selectable-row';
@endphp
<div class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<div class="row align-items-center text-center">
						<div class="col">
								 <a class="navbar-brand">
            @if(auth()->check() && auth()->user()->company)
              <img src="{{ asset(auth()->user()->company->logo)  }}" alt="{{ auth()->user()->company->name }}" class="company-logo" style="width: 90px;height: 90px;">
            @endif
          </a>
						</div>
						<div class="col">
							<h4 class="card-title mb-0">قائمة العمال</h4>
						</div>
						<div class="col"></div>

					</div>
					<div class="row mt-3">
						<div class="col-md-8">
							<form method="GET" action="{{ route('workers.index') }}" class="mb-0">
								<div class="input-group">
									<input
										type="text"
										name="search"
										class="form-control"
										placeholder="ابحث بالاسم أو الرقم القومي أو الهاتف"
										value="{{ request('search') }}"
									>
									@if(request()->filled('job_type_id'))
										<input type="hidden" name="job_type_id" value="{{ request('job_type_id') }}">
									@endif
									<input type="hidden" name="sort" value="{{ $sort }}">
									<input type="hidden" name="direction" value="{{ $direction }}">
									<div class="input-group-append">
										<button type="submit" class="btn btn-primary btn-sm"><i class="tim-icons icon-zoom-split"></i></button>
										<a href="{{ route('workers.index') }}" class="btn btn-secondary btn-sm"><i class="tim-icons icon-refresh-01"></i></a>
									</div>
								</div>
							</form>
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
									<input type="hidden" name="sort" value="{{ $sort }}">
									<input type="hidden" name="direction" value="{{ $direction }}">
									<div class="input-group-append">
										<button class="btn btn-sm btn-outline-secondary" type="submit">عرض</button>
									</div>
								</div>
							</form>
						</div>
					</div>
						@endif
						<div class="row">
						<div class="col-md-6">
								<form method="GET" action="{{ route('workers.index') }}" class="mb-3">
    <div class="input-group">
        <select name="job_type_id" class="form-control" style="max-width:260px;">
            <option value="">كل العمال</option>
			<option value="equipment_operator" {{ request('job_type_id') === 'equipment_operator' ? 'selected' : '' }}>
				مشغل معدة فقط
			</option>
            @foreach($jobTypes as $jobType)
                <option value="{{ $jobType->id }}" {{ (string)request('job_type_id') === (string)$jobType->id ? 'selected' : '' }}>
                    {{ $jobType->name }}
                </option>
            @endforeach
        </select>
		@if(request()->filled('search'))
			<input type="hidden" name="search" value="{{ request('search') }}">
		@endif
		<input type="hidden" name="sort" value="{{ $sort }}">
		<input type="hidden" name="direction" value="{{ $direction }}">

        <button type="submit" class="btn btn-sm btn-primary">عرض</button>
        <a href="{{ route('workers.index') }}" class="btn btn-sm btn-secondary"><i class="tim-icons icon-refresh-01"></i></a>
    </div>
</form>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							
						</div>
					</div>
					
											<div class="col text-right">
							{{-- <a href="{{ route('workers.export.pdf.merged') }}" class="btn btn-sm btn-success" title="Export all as merged PDF (HTML-based)" target="_blank">
								<i class="tim-icons icon-paper"></i> سركي مجمع PDF
							</a> --}}
							<a href="{{ route('workers.export.wordpdf.all', ['job_type_id' => request('job_type_id')]) }}" data-base-href="{{ route('workers.export.wordpdf.all', ['job_type_id' => request('job_type_id')]) }}" class="btn btn-sm btn-info js-export-selected" title="تحميل ملف واحد لكل العمال pdf" target="_blank">
								<i class="far fa-file-pdf"></i> سركي مجمع PDF
							</a>
							<a href="{{ route('workers.export.word.merged') }}" data-base-href="{{ route('workers.export.word.merged') }}" class="btn btn-sm btn-info js-export-selected" title="تحميل ملف وورد مجمع" target="_blank">
								<i class="far fa-file-word"></i></i> سركي وورد مجمع
							</a>
							<a href="{{ route('workers.export.word.all', ['job_type_id' => request('job_type_id')]) }}" data-base-href="{{ route('workers.export.word.all', ['job_type_id' => request('job_type_id')]) }}" class="btn btn-sm btn-danger js-export-selected" title="تحميل ملف وورد مجمعة (ZIP)" target="_blank">
								<i class="far fa-file-archive"></i> سراكي وورد مجمعة (ZIP)
							</a>

							<a href="{{ route('workers.create') }}" class="btn btn-primary btn-sm">
								<i class="tim-icons icon-simple-add"></i> اضافة عامل جديد
							</a>
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

					@if($workersData->isEmpty())
						<div class="alert alert-info text-center">
							<i class="tim-icons icon-alert-circle-exc"></i>
							لا يوجد عمال. اضغط على "اضافة عامل جديد" لإنشاء عامل جديد.
						</div>
					@else
						<div class="d-flex justify-content-end mb-2">
							<span class="badge badge-info" id="workers-selected-count">0 مختار</span>
						</div>
						<div class="table-responsive">
							<table class="table table-striped table-hover">
								<thead>
									<tr>
										<th class="text-center" style="width: 42px;">
											<input type="checkbox" id="workers-select-all" class="worker-table-checkbox" aria-label="Select all workers">
										</th>
										<th><a href="{{ $sortUrl('id') }}" style="color: inherit;font-weight: 700;"># {!! $sortIcon('id') !!}</a></th>
										<th><a href="{{ $sortUrl('name') }}" style="color: inherit;font-weight: 700;">Name / الاسم {!! $sortIcon('name') !!}</a></th>
										{{-- <th>Company / الشركة</th> --}}
										<th><a href="{{ $sortUrl('job_type_id') }}" style="color: inherit;font-weight: 700;">Job Type / الوظيفة {!! $sortIcon('job_type_id') !!}</a></th>
										<th><a href="{{ $sortUrl('national_id') }}" style="color: inherit;font-weight: 700;">الرقم القومي {!! $sortIcon('national_id') !!}</a></th>
										<th><a href="{{ $sortUrl('phone_number') }}" style="color: inherit;font-weight: 700;">الهاتف {!! $sortIcon('phone_number') !!}</a></th>
										<th><a href="{{ $sortUrl('equipmentAsDriver') }}" style="color: inherit;font-weight: 700;">المعدات {!! $sortIcon('equipmentAsDriver') !!}</a></th>
										<th><a href="{{ $sortUrl('join_date') }}" style="color: inherit;font-weight: 700;">تاريخ الانضمام {!! $sortIcon('join_date') !!}</a></th>
										<th><a href="{{ $sortUrl('is_on_company_payroll') }}" style="color: inherit;font-weight: 700;">على قوة الشركة {!! $sortIcon('is_on_company_payroll') !!}</a></th>
										<th class="text-center">Actions / الإجراءات</th>
									</tr>
								</thead>
								<tbody>
									@foreach($workersData as $worker)
										<tr class="{{ $workerSelectableRowClass }}" data-worker-id="{{ $worker->id }}">
											<td class="text-center">
												<input type="checkbox" id="worker-select-{{ $worker->id }}" name="selected_worker_ids[]" class="worker-table-checkbox worker-select-checkbox" value="{{ $worker->id }}" aria-label="Select worker {{ $worker->name }}">
											</td>
											<td>
												@if($workersData instanceof \Illuminate\Pagination\AbstractPaginator)
													{{ $loop->iteration + ($workersData->currentPage() - 1) * $workersData->perPage() }}
												@else
													{{ $loop->iteration }}
												@endif
											</td>
											<td><strong>{{ $worker->name }}</strong></td>
											{{-- <td>{{ optional($worker->company)->name ?? 'N/A' }}</td> --}}
											<td>{{ optional($worker->jobType)->name ?? 'N/A' }}</td>
											<td>
											<a class="text-center" data-toggle="tooltip" data-placement="top" title="{{ $worker->national_id }}" data-original-title="{{ $worker->national_id }}">
												<i class="fas fa-id-card workerid" ></i></a>
										
											</td>
											<td>
												@php
													$rawPhone = trim((string) ($worker->phone_number ?? ''));
													$cleanPhone = preg_replace('/\D+/', '', $rawPhone);
												@endphp

												@if($rawPhone !== '' && $cleanPhone !== '')
													<div class="d-flex align-items-center" style="min-width: 170px;">
												
														<div class="btn-group btn-group-sm" role="group" aria-label="contact actions">
														<button class="btn btn-icon btn-info btn-round btn-simple js-phone-tooltip" data-toggle="tooltip" data-placement="top" data-phone="{{ $cleanPhone }}" data-original-title="{{ $cleanPhone }}">
															<i class="fas fa-eye" style="font-size: 1.25rem;"></i>
														</button>
															<a href="tel:+2{{ $cleanPhone }}" class="btn btn-icon btn-info btn-round btn-simple" title="اتصال">
																<i class="fas fa-phone" style="font-size: 1.25rem;"></i>
															</a>
															<a href="https://wa.me/+2{{ $cleanPhone }}" class="btn btn-icon btn-info btn-round btn-simple" target="_blank" rel="noopener noreferrer" title="واتساب" style="display: flex; align-items: center; justify-content: center;">
																<i class="fab fa-whatsapp" style="font-size: 1.25rem;"></i>
															</a>
														</div>
													</div>
												
												@endif
											</td>
											<td>
													@if($worker->equipmentAsDriver->isNotEmpty())
												<a class="text-center ml-1"
												   data-toggle="tooltip"
												   data-placement="top"
												   data-html="true"
												   title="{{ $worker->equipmentAsDriver->map(fn($e) => 'كود : ' . ($e->equipment_code ?? '-') . '<br>نوع : ' . ($e->equipment_type ?? '-') . '<br>موديل: ' . ($e->model_year ?? '-') . '<br>شاسية: ' . ($e->equipment_number ?? '-'))->implode('<br><br>') }}"
												   href="{{ route('equipment.index') }}"
												   style="color:#00bcd4">
													<img src="{{ asset('assets/img/bulldozer.png')}}" alt="" class="img-fluid">
												</a>
											@endif
											</td>
											<td>{{ $worker->join_date ? $worker->join_date->format('Y-m-d') : 'N/A' }}</td>
											<td>
												@if($worker->is_on_company_payroll)
													<span class="badge badge-success">نعم</span>
												@else
													<span class="badge badge-danger">لا</span>
												@endif
											</td>
											<td class="text-center">
												<div class="btn-group" role="group">
												
													{{-- <a href="{{ route('workers.export.wordpdf', $worker->id) }}" class="btn btn-sm btn-primary" title="Word to PDF" target="_blank">
														<i class="tim-icons icon-paper"></i>
													</a> --}}
													@if($worker->equipmentAsDriver->isNotEmpty())
														<a href="{{ route('workers.export.daily-equipment-inspection', $worker->id) }}" class="btn btn-sm btn-warning" title="فحص يومي" target="_blank">
															<i class="fa-solid fa-clipboard-check"></i>
														</a>
													@endif
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

<style>
	.worker-table-checkbox {
		appearance: auto !important;
		-webkit-appearance: checkbox !important;
		opacity: 1 !important;
		visibility: visible !important;
		position: static !important;
		width: 16px;
		height: 16px;
		margin: 0;
		accent-color: #00d1b2;
	}

	.worker-selectable-row.worker-row-selected {
		background: rgba(56, 178, 172, 0.18) !important;
	}
</style>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		const selectAllCheckbox = document.getElementById('workers-select-all');
		const selectedCountElement = document.getElementById('workers-selected-count');
		const rowCheckboxes = Array.from(document.querySelectorAll('.worker-select-checkbox'));
			const exportSelectedButtons = Array.from(document.querySelectorAll('.js-export-selected'));

		if (!selectAllCheckbox || rowCheckboxes.length === 0) {
			return;
		}

		const syncUI = function () {
			let selectedCount = 0;
				const selectedIds = [];

			rowCheckboxes.forEach(function (checkbox) {
				const row = checkbox.closest('tr');
				if (!row) {
					return;
				}

				if (checkbox.checked) {
					selectedCount += 1;
						selectedIds.push(String(checkbox.value));
					row.classList.add('worker-row-selected');
				} else {
					row.classList.remove('worker-row-selected');
				}
			});

			selectAllCheckbox.checked = selectedCount > 0 && selectedCount === rowCheckboxes.length;
			selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < rowCheckboxes.length;

			if (selectedCountElement) {
				selectedCountElement.textContent = selectedCount + ' مختار';
			}

				exportSelectedButtons.forEach(function (button) {
					const baseHref = button.getAttribute('data-base-href') || button.getAttribute('href');
					if (!baseHref) {
						return;
					}

					const parsedUrl = new URL(baseHref, window.location.origin);
					if (selectedIds.length > 0) {
						parsedUrl.searchParams.set('ids', selectedIds.join(','));
					} else {
						parsedUrl.searchParams.delete('ids');
					}

					button.setAttribute('href', parsedUrl.toString());
				});
		};

		selectAllCheckbox.addEventListener('change', function () {
			rowCheckboxes.forEach(function (checkbox) {
				checkbox.checked = selectAllCheckbox.checked;
			});

			syncUI();
		});

		rowCheckboxes.forEach(function (checkbox) {
			checkbox.addEventListener('change', syncUI);
		});

		syncUI();
	});
</script>



@endsection

