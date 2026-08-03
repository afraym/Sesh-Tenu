@extends('layouts.back')
@section('content')
@php
	$workersData = $workers ?? collect();
	$sort = $sort ?? request('sort', 'created_at');
	$direction = $direction ?? request('direction', 'desc');
	$selectedMonth = request('month', now()->format('Y-m'));
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
							<form method="GET" action="{{ route('workers.index') }}" class="mb-0 js-workers-filter-form">
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
										<a href="{{ route('workers.index') }}" class="btn btn-secondary btn-sm js-workers-reset-link"><i class="tim-icons icon-refresh-01"></i></a>
									</div>
								</div>
							</form>
						</div>
					</div>
					<div class="row mt-3">
						<div class="col-md-6">
							<label for="worker_month" class="mb-1 font-weight-bold">الشهر:</label>
							<input type="month" id="worker_month" class="form-control form-control-sm js-workers-month-input" style="max-width: 190px;" value="{{ $selectedMonth }}">
						</div>
					</div>
					@if(auth()->check() && auth()->user()->isSuperAdmin())
					<div class="row mt-3">
						<div class="col-md-6">
							<form method="GET" action="{{ route('workers.index') }}" class="js-workers-filter-form">
								<div class="input-group">
									<select name="company_id" class="form-control">
										<option value="">All Companies / جميع الشركات</option>
										@foreach($companies ?? [] as $company)
											<option value="{{ $company->id }}" @if(isset($selectedCompanyId) && $selectedCompanyId == $company->id) selected @endif>
												{{ $company->name }}
											</option>
										@endforeach
									</select>
									<input type="hidden" name="month" value="{{ $selectedMonth }}">
									<input type="hidden" name="sort" value="{{ $sort }}">
									<input type="hidden" name="direction" value="{{ $direction }}">
									<div class="input-group-append">
										<button class="btn btn-sm btn-outline-secondary" type="submit">عرض</button>
										<a href="{{ route('workers.index') }}" class="btn btn-sm btn-secondary js-workers-reset-link"><i class="tim-icons icon-refresh-01"></i></a>
									</div>
								</div>
							</form>
						</div>
					</div>
						@endif
						<div class="row">
						<div class="col-md-6">
								<form method="GET" action="{{ route('workers.index') }}" class="mb-3 js-workers-filter-form">
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
		<input type="hidden" name="month" value="{{ $selectedMonth }}">
		<input type="hidden" name="sort" value="{{ $sort }}">
		<input type="hidden" name="direction" value="{{ $direction }}">

        <button type="submit" class="btn btn-sm btn-primary">عرض</button>
									<a href="{{ route('workers.index') }}" class="btn btn-sm btn-secondary js-workers-reset-link"><i class="tim-icons icon-refresh-01"></i></a>
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
												<a href="{{ route('workers.export.wordpdf.all', array_filter(['job_type_id' => request('job_type_id'), 'month' => $selectedMonth])) }}" data-base-href="{{ route('workers.export.wordpdf.all', array_filter(['job_type_id' => request('job_type_id'), 'month' => $selectedMonth])) }}" class="btn btn-sm btn-info js-export-selected" title="تحميل ملف واحد لكل العمال pdf" target="_blank">
								<i class="far fa-file-pdf"></i> سركي مجمع PDF
							</a>
												<a href="{{ route('workers.export.word.merged', ['month' => $selectedMonth]) }}" data-base-href="{{ route('workers.export.word.merged', ['month' => $selectedMonth]) }}" class="btn btn-sm btn-info js-export-selected" title="تحميل ملف وورد مجمع" target="_blank">
								<i class="far fa-file-word"></i></i> سركي وورد مجمع
							</a>
												<a href="{{ route('workers.export.word.all', array_filter(['job_type_id' => request('job_type_id'), 'month' => $selectedMonth])) }}" data-base-href="{{ route('workers.export.word.all', array_filter(['job_type_id' => request('job_type_id'), 'month' => $selectedMonth])) }}" class="btn btn-sm btn-danger js-export-selected" title="تحميل ملف وورد مجمعة (ZIP)" target="_blank">
								<i class="far fa-file-archive"></i> سراكي وورد مجمعة (ZIP)
							</a>

							<a href="{{ route('workers.create') }}" class="btn btn-primary btn-sm">
								<i class="tim-icons icon-simple-add"></i> اضافة عامل جديد
							</a>
						</div>
				</div>
				<div class="card-body">
					<div id="workers-ajax-alerts"></div>
					<div id="workers-results">
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
						<div class="d-flex justify-content-end align-items-center mb-2" style="gap: 10px;">
							<span class="badge badge-info" id="workers-selected-count">0 مختار</span>
							<button type="button" class="btn btn-sm btn-outline-danger m-0" id="workers-clear-selection" style="display: none;">
								إلغاء التحديد <i class="tim-icons icon-simple-remove"></i>
							</button>
						</div>
						<div class="table-responsive">
							<table class="table table-striped table-hover">
								<thead>
									<tr>
										<th class="text-center" style="width: 42px;">
											<input type="checkbox" id="workers-select-all" class="worker-table-checkbox" aria-label="Select all workers">
										</th>
											<th><a href="{{ $sortUrl('id') }}" class="js-workers-sort-link" style="color: inherit;font-weight: 700;"># {!! $sortIcon('id') !!}</a></th>
											<th><a href="{{ $sortUrl('name') }}" class="js-workers-sort-link" style="color: inherit;font-weight: 700;">Name / الاسم {!! $sortIcon('name') !!}</a></th>
										{{-- <th>Company / الشركة</th> --}}
											<th><a href="{{ $sortUrl('job_type_id') }}" class="js-workers-sort-link" style="color: inherit;font-weight: 700;">Job Type / الوظيفة {!! $sortIcon('job_type_id') !!}</a></th>
											<th><a href="{{ $sortUrl('national_id') }}" class="js-workers-sort-link" style="color: inherit;font-weight: 700;">الرقم القومي {!! $sortIcon('national_id') !!}</a></th>
											<th><a href="{{ $sortUrl('phone_number') }}" class="js-workers-sort-link" style="color: inherit;font-weight: 700;">الهاتف {!! $sortIcon('phone_number') !!}</a></th>
											<th><a href="{{ $sortUrl('equipmentAsDriver') }}" class="js-workers-sort-link" style="color: inherit;font-weight: 700;">المعدات {!! $sortIcon('equipmentAsDriver') !!}</a></th>
											<th><a href="{{ $sortUrl('join_date') }}" class="js-workers-sort-link" style="color: inherit;font-weight: 700;">تاريخ الانضمام {!! $sortIcon('join_date') !!}</a></th>
											<th><a href="{{ $sortUrl('is_on_company_payroll') }}" class="js-workers-sort-link" style="color: inherit;font-weight: 700;">على قوة الشركة {!! $sortIcon('is_on_company_payroll') !!}</a></th>
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
														<a href="{{ route('workers.export.daily-equipment-inspection', $worker->id) }}" class="btn btn-sm btn-warning js-worker-document-export" title="فحص يومي" target="_blank">
															<i class="fa-solid fa-clipboard-check"></i>
														</a>
													@endif
													<a href="{{ route('workers.export.word', ['worker' => $worker->id, 'month' => $selectedMonth]) }}" data-base-href="{{ route('workers.export.word', ['worker' => $worker->id]) }}" class="btn btn-sm btn-default js-worker-month-export" title="Word">
														<i class="tim-icons icon-single-copy-04"></i>
													</a>
													<a href="{{ route('workers.show', $worker->id) }}" class="btn btn-info btn-sm" title="View">
														<i class="tim-icons icon-notes"></i>
													</a>
													<a href="{{ route('workers.edit', $worker->id) }}" class="btn btn-warning btn-sm" title="Edit">
														<i class="tim-icons icon-pencil"></i>
													</a>
														<form action="{{ route('workers.destroy', $worker->id) }}" method="POST" class="d-inline js-worker-delete-form" onsubmit="return confirm('Are you sure you want to delete this worker?');">
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
								<div class="mt-3" data-workers-pagination>
								{{ $workersData->links() }}
							</div>
						@endif
					@endif
					</div>
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
		const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
		const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') : '';

		const getResultsElement = function () {
			return document.getElementById('workers-results');
		};

		const getAlertsElement = function () {
			return document.getElementById('workers-ajax-alerts');
		};

		const getMonthInput = function () {
			return document.getElementById('worker_month');
		};

		const getRowCheckboxes = function () {
			return Array.from(document.querySelectorAll('.worker-select-checkbox'));
		};

		const getSelectAllCheckbox = function () {
			return document.getElementById('workers-select-all');
		};

		const getSelectedCountElement = function () {
			return document.getElementById('workers-selected-count');
		};

		const getExportSelectedButtons = function () {
			return Array.from(document.querySelectorAll('.js-export-selected'));
		};

		const getWorkerMonthExportButtons = function () {
			return Array.from(document.querySelectorAll('.js-worker-month-export'));
		};

		const getClearSelectionBtn = function () {
			return document.getElementById('workers-clear-selection');
		};

		let persistedWorkerSelectedIds = new Set();

		try {
			const stored = JSON.parse(sessionStorage.getItem('workerSelections'));
			if (stored && stored.ids) {
				stored.ids.forEach(id => persistedWorkerSelectedIds.add(String(id)));
			}
		} catch (e) {}

		const saveSelections = () => {
			sessionStorage.setItem('workerSelections', JSON.stringify({
				ids: Array.from(persistedWorkerSelectedIds)
			}));
		};

		const restoreDOMFromPersisted = function () {
			const rowCheckboxes = getRowCheckboxes();
			rowCheckboxes.forEach(checkbox => {
				const id = String(checkbox.value);
				const row = checkbox.closest('tr');
				
				if (persistedWorkerSelectedIds.has(id)) {
					checkbox.checked = true;
					if (row) row.classList.add('worker-row-selected');
				} else {
					checkbox.checked = false;
					if (row) row.classList.remove('worker-row-selected');
				}
			});
		};

		const escapeHtml = function (value) {
			return String(value)
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#039;');
		};

		const renderAlert = function (message, type) {
			const alerts = getAlertsElement();
			if (!alerts) {
				return;
			}

			alerts.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
				'<i class="tim-icons icon-check-2"></i> ' + escapeHtml(message) +
				'<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
					'<span aria-hidden="true">&times;</span>' +
				'</button>' +
			'</div>';
		};

		const clearAlerts = function () {
			const alerts = getAlertsElement();
			if (alerts) {
				alerts.innerHTML = '';
			}
		};

		const syncUI = function () {
			const rowCheckboxes = getRowCheckboxes();
			const selectAllCheckbox = getSelectAllCheckbox();
			const selectedCountElement = getSelectedCountElement();
			const workerMonthInput = getMonthInput();
			const clearSelectionBtn = getClearSelectionBtn();

			let currentPageSelectedCount = 0;

			rowCheckboxes.forEach(function (checkbox) {
				const id = String(checkbox.value);
				const row = checkbox.closest('tr');

				if (checkbox.checked) {
					currentPageSelectedCount += 1;
					persistedWorkerSelectedIds.add(id);
					if (row) row.classList.add('worker-row-selected');
				} else {
					persistedWorkerSelectedIds.delete(id);
					if (row) row.classList.remove('worker-row-selected');
				}
			});

			saveSelections();

			const totalSelectedCount = persistedWorkerSelectedIds.size;

			if (selectAllCheckbox) {
				selectAllCheckbox.checked = currentPageSelectedCount > 0 && currentPageSelectedCount === rowCheckboxes.length;
				selectAllCheckbox.indeterminate = currentPageSelectedCount > 0 && currentPageSelectedCount < rowCheckboxes.length;
			}

			if (selectedCountElement) {
				selectedCountElement.textContent = totalSelectedCount + ' مختار';
			}

			if (clearSelectionBtn) {
				clearSelectionBtn.style.display = totalSelectedCount > 0 ? 'inline-block' : 'none';
			}

			if (workerMonthInput) {
				Array.from(document.querySelectorAll('input[type="hidden"][name="month"]')).forEach(function (input) {
					input.value = workerMonthInput.value || '';
				});
			}

			const selectedIdsArr = Array.from(persistedWorkerSelectedIds);

			getExportSelectedButtons().forEach(function (button) {
				const baseHref = button.getAttribute('data-base-href') || button.getAttribute('href');
				if (!baseHref) {
					return;
				}

				const parsedUrl = new URL(baseHref, window.location.origin);
				if (selectedIdsArr.length > 0) {
					parsedUrl.searchParams.set('ids', selectedIdsArr.join(','));
				} else {
					parsedUrl.searchParams.delete('ids');
				}

				if (workerMonthInput && workerMonthInput.value) {
					parsedUrl.searchParams.set('month', workerMonthInput.value);
				} else {
					parsedUrl.searchParams.delete('month');
				}

				button.setAttribute('href', parsedUrl.toString());
			});

			getWorkerMonthExportButtons().forEach(function (button) {
				const baseHref = button.getAttribute('data-base-href') || button.getAttribute('href');
				if (!baseHref) {
					return;
				}

				const parsedUrl = new URL(baseHref, window.location.origin);
				if (workerMonthInput && workerMonthInput.value) {
					parsedUrl.searchParams.set('month', workerMonthInput.value);
				} else {
					parsedUrl.searchParams.delete('month');
				}

				button.setAttribute('href', parsedUrl.toString());
			});
		};

		const buildUrl = function (baseUrl, overrides) {
			const url = new URL(baseUrl, window.location.origin);

			const selectedIdsArr = Array.from(persistedWorkerSelectedIds);
			if (selectedIdsArr.length > 0) {
				url.searchParams.set('selected_ids', selectedIdsArr.join(','));
			} else {
				url.searchParams.delete('selected_ids');
			}

			Object.keys(overrides).forEach(function (key) {
				const value = overrides[key];
				if (value === null || value === undefined || value === '') {
					url.searchParams.delete(key);
				} else {
					url.searchParams.set(key, value);
				}
			});

			return url.toString();
		};

		const buildUrlFromForm = function (form) {
			const url = new URL(form.action || window.location.href, window.location.origin);
			const params = new URLSearchParams(window.location.search);
			const formData = new FormData(form);
			const workerMonthInput = getMonthInput();

			formData.forEach(function (value, key) {
				if (typeof value === 'string' && value.trim() === '') {
					params.delete(key);
				} else {
					params.set(key, value);
				}
			});

			if (workerMonthInput && workerMonthInput.value) {
				params.set('month', workerMonthInput.value);
			} else {
				params.delete('month');
			}

			const selectedIdsArr = Array.from(persistedWorkerSelectedIds);
			if (selectedIdsArr.length > 0) {
				params.set('selected_ids', selectedIdsArr.join(','));
			} else {
				params.delete('selected_ids');
			}

			url.search = params.toString();
			return url.toString();
		};

		const replaceResultsFromHtml = function (html) {
			const parser = new DOMParser();
			const doc = parser.parseFromString(html, 'text/html');
			const nextResults = doc.getElementById('workers-results');
			const currentResults = getResultsElement();

			if (!nextResults || !currentResults) {
				return false;
			}

			currentResults.replaceWith(nextResults);
			return true;
		};

		const loadWorkers = async function (url, updateHistory, preserveAlerts) {
			const response = await fetch(url, {
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
					'Accept': 'application/json',
				},
			});

			if (!response.ok) {
				throw new Error('Failed to load workers.');
			}

			const payload = await response.json();

			if (!payload.html || !replaceResultsFromHtml(payload.html)) {
				throw new Error('Invalid workers response.');
			}

			if (updateHistory && payload.url) {
				window.history.pushState({ workersAjax: true }, '', payload.url);
			}

			if (payload.message) {
				renderAlert(payload.message, 'success');
			} else if (!preserveAlerts) {
				clearAlerts();
			}

			restoreDOMFromPersisted();
			syncUI();
		};

		const reloadCurrentList = function (preserveAlerts) {
			return loadWorkers(window.location.href, false, preserveAlerts);
		};

		const downloadBlob = function (blob, filename) {
			const objectUrl = window.URL.createObjectURL(blob);
			const link = document.createElement('a');
			link.href = objectUrl;
			link.download = filename || 'document';
			document.body.appendChild(link);
			link.click();
			link.remove();
			window.URL.revokeObjectURL(objectUrl);
		};

		const requestDocumentDownload = async function (url) {
			const response = await fetch(url, {
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
					'Accept': 'application/json',
				},
			});

			if (!response.ok) {
				const payload = await response.json().catch(function () {
					return {};
				});

				throw new Error(payload.message || 'Failed to generate document.');
			}

			const payload = await response.json();

			if (!payload.download_url) {
				throw new Error('Document download URL was not returned.');
			}

			const downloadResponse = await fetch(payload.download_url, {
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
				},
			});

			if (!downloadResponse.ok) {
				throw new Error('Failed to fetch generated document.');
			}

			const blob = await downloadResponse.blob();
			downloadBlob(blob, payload.filename || 'document');
		};

		document.addEventListener('submit', function (event) {
			const form = event.target;
			if (!(form instanceof HTMLFormElement)) {
				return;
			}

			if (form.matches('.js-workers-filter-form')) {
				event.preventDefault();
				loadWorkers(buildUrlFromForm(form), true).catch(function (error) {
					renderAlert(error.message || 'Failed to load workers.', 'danger');
				});
				return;
			}

			if (form.matches('.js-worker-delete-form')) {
				event.preventDefault();

				if (!window.confirm('Are you sure you want to delete this worker?')) {
					return;
				}

				const formData = new FormData(form);

				fetch(form.action, {
					method: 'POST',
					headers: {
						'X-Requested-With': 'XMLHttpRequest',
						'Accept': 'application/json',
						'X-CSRF-TOKEN': csrfToken,
					},
					body: formData,
				})
					.then(async function (response) {
						const payload = await response.json().catch(function () {
							return {};
						});

						if (!response.ok) {
							throw new Error(payload.message || 'Failed to delete worker.');
						}

						if (payload.message) {
							renderAlert(payload.message, 'success');
						}

						return reloadCurrentList(true);
					})
					.catch(function (error) {
						renderAlert(error.message || 'Failed to delete worker.', 'danger');
					});
			}
		});

		document.addEventListener('click', function (event) {
			const clearSelectionBtn = event.target.closest('#workers-clear-selection');
			if (clearSelectionBtn) {
				persistedWorkerSelectedIds.clear();
				saveSelections();
				restoreDOMFromPersisted();
				syncUI();
				return;
			}

			const documentExportLink = event.target.closest('.js-export-selected, .js-worker-month-export, .js-worker-document-export');
			if (documentExportLink) {
				event.preventDefault();

				if (documentExportLink.classList.contains('js-export-selected') && persistedWorkerSelectedIds.size === 0) {
					alert('Please select at least one worker first.');
					return;
				}

				documentExportLink.classList.add('disabled');
				requestDocumentDownload(documentExportLink.href)
					.catch(function (error) {
						renderAlert(error.message || 'Failed to generate document.', 'danger');
					})
					.finally(function () {
						documentExportLink.classList.remove('disabled');
					});
				return;
			}

			const resetLink = event.target.closest('.js-workers-reset-link');
			if (resetLink) {
				event.preventDefault();
				loadWorkers(buildUrl(resetLink.href, {
					month: getMonthInput() ? getMonthInput().value : '',
				}), true).catch(function (error) {
					renderAlert(error.message || 'Failed to load workers.', 'danger');
				});
				return;
			}

			const sortLink = event.target.closest('.js-workers-sort-link');
			if (sortLink) {
				event.preventDefault();
				loadWorkers(buildUrl(sortLink.href, {
					month: getMonthInput() ? getMonthInput().value : '',
				}), true).catch(function (error) {
					renderAlert(error.message || 'Failed to load workers.', 'danger');
				});
				return;
			}

			const paginationLink = event.target.closest('[data-workers-pagination] a');
			if (paginationLink) {
				event.preventDefault();
				loadWorkers(buildUrl(paginationLink.href, {
					month: getMonthInput() ? getMonthInput().value : '',
				}), true).catch(function (error) {
					renderAlert(error.message || 'Failed to load workers.', 'danger');
				});
			}
		});

		document.addEventListener('change', function (event) {
			const target = event.target;

			if (!(target instanceof HTMLElement)) {
				return;
			}

			if (target.id === 'workers-select-all') {
				getRowCheckboxes().forEach(function (checkbox) {
					checkbox.checked = target.checked;
				});
				syncUI();
				return;
			}

			if (target.classList.contains('worker-select-checkbox')) {
				syncUI();
				return;
			}

			if (target.id === 'worker_month') {
				loadWorkers(buildUrl(window.location.href, {
					month: target.value,
				}), true).catch(function (error) {
					renderAlert(error.message || 'Failed to load workers.', 'danger');
				});
			}
		});

		window.addEventListener('popstate', function () {
			reloadCurrentList().catch(function (error) {
				renderAlert(error.message || 'Failed to load workers.', 'danger');
			});
		});

		restoreDOMFromPersisted();
		syncUI();
	});
</script>



@endsection

