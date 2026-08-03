@extends('layouts.back')
@section('content')
    @php
        $sort = $sort ?? request('sort', 'created_at');
        $direction = $direction ?? request('direction', 'desc');
        $equipmentSelectableRowClass = 'equipment-selectable-row';

        $sortUrl = function (string $column) use ($sort, $direction) {
            $nextDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';

            return route('equipment.index', array_merge(request()->query(), [
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
    @endphp
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header  align-items-center text-center">
                        <a class="navbar-brand">
                            @if(auth()->check() && auth()->user()->company)
                                <img src="{{ asset(auth()->user()->company->logo)  }}" alt="{{ auth()->user()->company->name }}"
                                    class="company-logo" style="width: 90px;height: 90px;">
                            @endif
                        </a>
                        <h4 class="card-title">Equipment List / قائمة المعدات</h4>
                        <div class="d-flex justify-content-center align-items-center flex-wrap mt-2" style="gap: 8px;">
                            <label for="inspection_month" class="mb-0">الشهر:</label>
                            <input type="month" id="inspection_month" class="form-control form-control-sm"
                                style="width: 190px;" value="{{ request('month', now()->format('Y-m')) }}">
                            <button type="button" class="btn btn-info btn-sm" id="select-all-actual">تحديد كل
                                الفعلي</button>
                            <button type="button" class="btn btn-info btn-sm" id="select-all-optional">تحديد كل
                                الاختياري</button>
                            <a href="{{ route('equipment.exportWordSelected') }}"
                                data-base-href="{{ route('equipment.exportWordSelected') }}"
                                class="btn btn-warning btn-sm js-export-selected" target="_blank">تحميل الفحص اليومي
                                للمحدد</a>
                            <a href="{{ route('equipment.create') }}" class="btn btn-primary btn-sm">Add Equipment / إضافة
                                معدة</a>
                        </div>
                        <div class="row mt-3 justify-content-center">
                            <div class="col-md-8">
                                <form method="GET" action="{{ route('equipment.index') }}" class="mb-0 js-equipments-filter-form">
                                    <div class="input-group">
                                        <input
                                            type="text"
                                            name="search"
                                            class="form-control"
                                            placeholder="ابحث بكود المعدة، النوع، الرقم، أو السائق..."
                                            value="{{ request('search') }}"
                                        >
                                        <input type="hidden" name="month" value="{{ request('month', now()->format('Y-m')) }}">
                                        <input type="hidden" name="sort" value="{{ $sort }}">
                                        <input type="hidden" name="direction" value="{{ $direction }}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary btn-sm"><i class="tim-icons icon-zoom-split"></i></button>
                                            <a href="{{ route('equipment.index') }}" class="btn btn-secondary btn-sm js-equipments-reset-link"><i class="tim-icons icon-refresh-01"></i></a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="equipments-ajax-alerts"></div>
                        <div id="equipments-results">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        <div class="d-flex justify-content-end align-items-center mb-2" style="gap: 10px;">
                            <span class="badge badge-info" id="equipments-selected-count">0 مختار</span>
                            <button type="button" class="btn btn-sm btn-outline-danger m-0" id="equipments-clear-selection" style="display: none;">
                                إلغاء التحديد <i class="tim-icons icon-simple-remove"></i>
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 42px;">
                                            <input type="checkbox" id="equipments-select-all"
                                                class="equipment-table-checkbox" aria-label="Select all equipment">
                                        </th>
                                        <th><a href="{{ $sortUrl('id') }}" class="js-equipments-sort-link" style="color: inherit;">#
                                                {!! $sortIcon('id') !!}</a></th>
                                        <th><a href="{{ $sortUrl('current_driver') }}" class="js-equipments-sort-link" style="color: inherit;">اسم السائق
                                                الحالي {!! $sortIcon('current_driver') !!}</a></th>
                                        {{-- <th><a href="{{ $sortUrl('project_name') }}" class="js-equipments-sort-link" style="color: inherit;">اسم
                                                المشروع {!! $sortIcon('project_name') !!}</a></th> --}}
                                        <th><a href="{{ $sortUrl('company_id') }}" class="js-equipments-sort-link" style="color: inherit;">اسم الشركة
                                                {!! $sortIcon('company_id') !!}</a></th>
                                        <th><a href="{{ $sortUrl('equipment_type') }}" class="js-equipments-sort-link" style="color: inherit;">نوع المعدة
                                                {!! $sortIcon('equipment_type') !!}</a></th>
                                        <th><a href="{{ $sortUrl('model_year') }}" class="js-equipments-sort-link" style="color: inherit;">موديل المعدة
                                                {!! $sortIcon('model_year') !!}</a></th>
                                        <th><a href="{{ $sortUrl('equipment_code') }}" class="js-equipments-sort-link" style="color: inherit;">كود المعدة
                                                {!! $sortIcon('equipment_code') !!}</a></th>
                                        <th>نوع المعدة (فعلي او اختياري)</th>
                                        <th><a href="{{ $sortUrl('equipment_number') }}" class="js-equipments-sort-link" style="color: inherit;">رقم شاسيه
                                                المعدة {!! $sortIcon('equipment_number') !!}</a></th>
                                        <th><a href="{{ $sortUrl('manufacture') }}" class="js-equipments-sort-link" style="color: inherit;">المصنع
                                                {!! $sortIcon('manufacture') !!}</a></th>
                                        <th><a href="{{ $sortUrl('entry_per_ser') }}" class="js-equipments-sort-link" style="color: inherit;">تصريح الدخول
                                                {!! $sortIcon('entry_per_ser') !!}</a></th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($equipments as $equipment)
                                        <tr class="{{ $equipmentSelectableRowClass }}" data-equipment-id="{{ $equipment->id }}">
                                            <td class="text-center">
                                                <input type="checkbox" id="equipment-select-{{ $equipment->id }}"
                                                    name="selected_equipment_ids[]"
                                                    class="equipment-table-checkbox equipment-select-checkbox"
                                                    value="{{ $equipment->id }}"
                                                    data-equipment-option="{{ trim((string) ($equipment->equipment_option ?? '')) }}"
                                                    aria-label="Select equipment {{ $equipment->equipment_code ?? $equipment->id }}">
                                            </td>
                                            <td>{{ $loop->iteration + ($equipments->currentPage() - 1) * $equipments->perPage() }}
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column" style="gap: 6px; min-width: 200px;">
                                                    <strong>{{ $equipment->current_driver ?? 'غير متوفر' }}</strong>
                                                    <input type="text"
                                                        class="form-control form-control-sm js-equipment-excluded-dates"
                                                        placeholder="استبعاد أيام مثل 1/2/8/15/22-30"
                                                        aria-label="Excluded out-of-month dates for {{ $equipment->equipment_code ?? $equipment->id }}"
                                                        value="{{ data_get(request('excluded_dates', []), $equipment->id, '') }}">
                                                </div>
                                            </td>
                                            
                                            <td>{{ optional($equipment->company)->name ?? 'غير متوفر' }}</td>
                                            <td>{{ $equipment->equipment_type }}</td>
                                            <td>{{ $equipment->model_year ?? 'غير متوفر' }}</td>
                                            <td>{{ $equipment->equipment_code }}</td>
                                            <td>{{ $equipment->equipment_option ?? 'غير متوفر' }}</td>
                                            <td>{{ $equipment->equipment_number ?? 'غير متوفر' }}</td>
                                            
                                            <td>{{ $equipment->manufacture ?? 'غير متوفر' }}</td>
                                            <td>{{ $equipment->entry_per_ser ?? 'غير متوفر' }}</td>
                                            <td>
                                                <a href="{{ route('equipment.show', $equipment->id) }}"
                                                    class="btn btn-info btn-sm" title="View"><i
                                                        class="tim-icons icon-notes"></i></a>
                                                <a href="{{ route('equipment.edit', $equipment->id) }}"
                                                    class="btn btn-warning btn-sm" title="Edit"><i
                                                        class="tim-icons icon-pencil"></i></a>
                                                <a href="{{ route('equipment.exportWord', $equipment->id) }}"
                                                    data-base-href="{{ route('equipment.exportWord', $equipment->id) }}"
                                                    class="btn btn-sm btn-primary js-equipment-document-export"
                                                    target="_blank">
                                                    طباعة الفحص
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3" data-equipments-pagination>
                            {{ $equipments->links() }}
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .equipment-table-checkbox {
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

        .equipment-selectable-row.equipment-row-selected {
            background: rgba(56, 178, 172, 0.18) !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const getInspectionMonthInput = () => document.getElementById('inspection_month');
            const getSelectAllCheckbox = () => document.getElementById('equipments-select-all');
            const getSelectedCountElement = () => document.getElementById('equipments-selected-count');
            const getRowCheckboxes = () => Array.from(document.querySelectorAll('.equipment-select-checkbox'));
            const getExportSelectedButtons = () => Array.from(document.querySelectorAll('.js-export-selected'));
            const getRowExportButtons = () => Array.from(document.querySelectorAll('.js-equipment-document-export'));
            const getClearSelectionBtn = () => document.getElementById('equipments-clear-selection');

            let persistedSelectedIds = new Set();
            let persistedExcludedDates = {};

            try {
                const stored = JSON.parse(sessionStorage.getItem('equipmentSelections'));
                if (stored && stored.ids) {
                    stored.ids.forEach(id => persistedSelectedIds.add(String(id)));
                    persistedExcludedDates = stored.dates || {};
                }
            } catch (e) {}

            const saveSelections = () => {
                sessionStorage.setItem('equipmentSelections', JSON.stringify({
                    ids: Array.from(persistedSelectedIds),
                    dates: persistedExcludedDates
                }));
            };

            const restoreDOMFromPersisted = function () {
                const rowCheckboxes = getRowCheckboxes();
                rowCheckboxes.forEach(checkbox => {
                    const id = String(checkbox.value);
                    const row = checkbox.closest('tr');
                    const excludedDatesInput = row ? row.querySelector('.js-equipment-excluded-dates') : null;
                    
                    if (persistedSelectedIds.has(id)) {
                        checkbox.checked = true;
                        if (row) row.classList.add('equipment-row-selected');
                        if (excludedDatesInput && persistedExcludedDates[id] !== undefined) {
                            excludedDatesInput.value = persistedExcludedDates[id];
                        }
                    } else {
                        checkbox.checked = false;
                        if (row) row.classList.remove('equipment-row-selected');
                    }
                });
            };

            const syncUI = function () {
                const rowCheckboxes = getRowCheckboxes();
                const selectAllCheckbox = getSelectAllCheckbox();
                const selectedCountElement = getSelectedCountElement();
                const exportSelectedButtons = getExportSelectedButtons();
                const rowExportButtons = getRowExportButtons();
                const inspectionMonthInput = getInspectionMonthInput();
                const clearSelectionBtn = getClearSelectionBtn();

                let currentPageSelectedCount = 0;

                rowCheckboxes.forEach(function (checkbox) {
                    const id = String(checkbox.value);
                    const row = checkbox.closest('tr');
                    const excludedDatesInput = row ? row.querySelector('.js-equipment-excluded-dates') : null;
                    const excludedDates = excludedDatesInput ? excludedDatesInput.value.trim() : '';

                    if (checkbox.checked) {
                        currentPageSelectedCount++;
                        persistedSelectedIds.add(id);
                        if (excludedDates) {
                            persistedExcludedDates[id] = excludedDates;
                        } else {
                            delete persistedExcludedDates[id];
                        }
                        if (row) row.classList.add('equipment-row-selected');
                    } else {
                        persistedSelectedIds.delete(id);
                        delete persistedExcludedDates[id];
                        if (row) row.classList.remove('equipment-row-selected');
                    }
                });

                saveSelections();

                const totalSelectedCount = persistedSelectedIds.size;

                if (selectedCountElement) {
                    selectedCountElement.textContent = totalSelectedCount + ' مختار';
                }

                if (clearSelectionBtn) {
                    clearSelectionBtn.style.display = totalSelectedCount > 0 ? 'inline-block' : 'none';
                }
                
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = currentPageSelectedCount > 0 && currentPageSelectedCount === rowCheckboxes.length;
                    selectAllCheckbox.indeterminate = currentPageSelectedCount > 0 && currentPageSelectedCount < rowCheckboxes.length;
                }

                const selectedIdsArr = Array.from(persistedSelectedIds);

                exportSelectedButtons.forEach(function (button) {
                    const baseHref = button.dataset.baseHref || button.href;
                    if (selectedIdsArr.length > 0) {
                        const query = new URLSearchParams();
                        query.set('ids', selectedIdsArr.join(','));

                        if (inspectionMonthInput && inspectionMonthInput.value) {
                            query.set('month', inspectionMonthInput.value);
                        }

                        selectedIdsArr.forEach(function (equipmentId) {
                            if (persistedExcludedDates[equipmentId]) {
                                query.set('excluded_dates[' + equipmentId + ']', persistedExcludedDates[equipmentId]);
                            }
                        });

                        button.href = baseHref + '?' + query.toString();
                        button.classList.remove('disabled');
                        button.setAttribute('aria-disabled', 'false');
                    } else {
                        button.href = baseHref;
                        button.classList.add('disabled');
                        button.setAttribute('aria-disabled', 'true');
                    }
                });

                rowExportButtons.forEach(function (button) {
                    const baseHref = button.dataset.baseHref || button.href;
                    const row = button.closest('tr');

                    if (!baseHref || !row) return;

                    const equipmentId = row.getAttribute('data-equipment-id');
                    const excludedDatesInput = row.querySelector('.js-equipment-excluded-dates');
                    const excludedDates = excludedDatesInput ? excludedDatesInput.value.trim() : '';
                    const parsedUrl = new URL(baseHref, window.location.origin);

                    if (inspectionMonthInput && inspectionMonthInput.value) {
                        parsedUrl.searchParams.set('month', inspectionMonthInput.value);
                    } else {
                        parsedUrl.searchParams.delete('month');
                    }

                    if (equipmentId && excludedDates) {
                        parsedUrl.searchParams.set('excluded_dates[' + equipmentId + ']', excludedDates);
                    } else if (equipmentId) {
                        parsedUrl.searchParams.delete('excluded_dates[' + equipmentId + ']');
                    }

                    button.href = parsedUrl.toString();
                });
            };

            const getResultsElement = () => document.getElementById('equipments-results');
            const getAlertsElement = () => document.getElementById('equipments-ajax-alerts');

            const renderAlert = function (message, type) {
                const alerts = getAlertsElement();
                if (!alerts) return;
                alerts.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span>' +
                    '</button>' +
                '</div>';
            };

            const clearAlerts = function () {
                const alerts = getAlertsElement();
                if (alerts) alerts.innerHTML = '';
            };

            const replaceResultsFromHtml = function (html) {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const nextResults = doc.getElementById('equipments-results');
                const currentResults = getResultsElement();

                if (!nextResults || !currentResults) return false;

                currentResults.replaceWith(nextResults);
                return true;
            };

            const loadEquipments = async function (url, updateHistory, preserveAlerts) {
                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) throw new Error('Failed to load equipments.');

                    const payload = await response.json();

                    if (!payload.html || !replaceResultsFromHtml(payload.html)) {
                        throw new Error('Invalid equipments response.');
                    }

                    if (updateHistory && payload.url) {
                        window.history.pushState({ equipmentsAjax: true }, '', payload.url);
                    }

                    if (payload.message) {
                        renderAlert(payload.message, 'success');
                    } else if (!preserveAlerts) {
                        clearAlerts();
                    }
                    
                    restoreDOMFromPersisted();
                    syncUI();
                } catch (error) {
                    renderAlert(error.message || 'Failed to load equipments.', 'danger');
                }
            };

            const buildUrlFromForm = function (form) {
                const url = new URL(form.action || window.location.href, window.location.origin);
                const params = new URLSearchParams(window.location.search);
                const formData = new FormData(form);

                formData.forEach(function (value, key) {
                    if (typeof value === 'string' && value.trim() === '') {
                        params.delete(key);
                    } else {
                        params.set(key, value);
                    }
                });

                const inspectionMonthInput = getInspectionMonthInput();
                if (inspectionMonthInput && inspectionMonthInput.value) {
                    params.set('month', inspectionMonthInput.value);
                }

                const selectedIdsArr = Array.from(persistedSelectedIds);
                if (selectedIdsArr.length > 0) {
                    params.set('selected_ids', selectedIdsArr.join(','));
                } else {
                    params.delete('selected_ids');
                }

                url.search = params.toString();
                return url.toString();
            };
            
            const buildUrl = function (baseUrl, overrides) {
                const url = new URL(baseUrl, window.location.origin);

                const selectedIdsArr = Array.from(persistedSelectedIds);
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

            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (form && form.matches('.js-equipments-filter-form')) {
                    event.preventDefault();
                    loadEquipments(buildUrlFromForm(form), true);
                }
            });

            document.addEventListener('click', function (event) {
                const exportSelectedButton = event.target.closest('.js-export-selected');
                if (exportSelectedButton) {
                    if (persistedSelectedIds.size === 0) {
                        event.preventDefault();
                        alert('Please select at least one equipment first.');
                    }
                    return;
                }

                const clearSelectionBtn = event.target.closest('#equipments-clear-selection');
                if (clearSelectionBtn) {
                    persistedSelectedIds.clear();
                    persistedExcludedDates = {};
                    saveSelections();
                    restoreDOMFromPersisted();
                    syncUI();
                    return;
                }

                const resetLink = event.target.closest('.js-equipments-reset-link');
                if (resetLink) {
                    event.preventDefault();
                    loadEquipments(buildUrl(resetLink.href, {
                        month: getInspectionMonthInput() ? getInspectionMonthInput().value : '',
                    }), true);
                    return;
                }

                const sortLink = event.target.closest('.js-equipments-sort-link');
                if (sortLink) {
                    event.preventDefault();
                    loadEquipments(buildUrl(sortLink.href, {
                        month: getInspectionMonthInput() ? getInspectionMonthInput().value : '',
                    }), true);
                    return;
                }

                const paginationLink = event.target.closest('[data-equipments-pagination] a');
                if (paginationLink) {
                    event.preventDefault();
                    loadEquipments(buildUrl(paginationLink.href, {
                        month: getInspectionMonthInput() ? getInspectionMonthInput().value : '',
                    }), true);
                    return;
                }

                const selectAllActualBtn = event.target.closest('#select-all-actual');
                if (selectAllActualBtn) {
                    getRowCheckboxes().forEach(function (checkbox) {
                        const option = (checkbox.dataset.equipmentOption || '').trim();
                        checkbox.checked = option === 'فعلي';
                    });
                    syncUI();
                    return;
                }

                const selectAllOptionalBtn = event.target.closest('#select-all-optional');
                if (selectAllOptionalBtn) {
                    getRowCheckboxes().forEach(function (checkbox) {
                        const option = (checkbox.dataset.equipmentOption || '').trim();
                        checkbox.checked = option === 'اختياري';
                    });
                    syncUI();
                    return;
                }
            });

            document.addEventListener('change', function (event) {
                const target = event.target;

                if (target.id === 'equipments-select-all') {
                    getRowCheckboxes().forEach(function (checkbox) {
                        checkbox.checked = target.checked;
                    });
                    syncUI();
                    return;
                }

                if (target.classList.contains('equipment-select-checkbox')) {
                    syncUI();
                    return;
                }

                if (target.id === 'inspection_month') {
                    syncUI();
                }
            });

            document.addEventListener('input', function (event) {
                if (event.target && event.target.classList && event.target.classList.contains('js-equipment-excluded-dates')) {
                    syncUI();
                }
            });

            window.addEventListener('popstate', function () {
                loadEquipments(window.location.href, false, false);
            });

            restoreDOMFromPersisted();
            syncUI();
        });
    </script>
@endsection