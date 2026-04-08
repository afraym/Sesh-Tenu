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
              <img src="{{ asset(auth()->user()->company->logo)  }}" alt="{{ auth()->user()->company->name }}" class="company-logo" style="width: 90px;height: 90px;">
            @endif
          </a>
                    <h4 class="card-title">Equipment List / قائمة المعدات</h4>
                    <div class="d-flex justify-content-center align-items-center flex-wrap mt-2" style="gap: 8px;">
                        <label for="inspection_month" class="mb-0">الشهر:</label>
                        <input type="month" id="inspection_month" class="form-control form-control-sm" style="width: 190px;" value="{{ request('month', now()->format('Y-m')) }}">
                        <button type="button" class="btn btn-info btn-sm" id="select-all-actual">تحديد كل الفعلي</button>
                        <button type="button" class="btn btn-info btn-sm" id="select-all-optional">تحديد كل الاختياري</button>
                        <a href="{{ route('equipment.exportWordSelected') }}" data-base-href="{{ route('equipment.exportWordSelected') }}" class="btn btn-warning btn-sm js-export-selected" target="_blank">تحميل الفحص اليومي للمحدد</a>
                        <a href="{{ route('equipment.create') }}" class="btn btn-primary btn-sm">Add Equipment / إضافة معدة</a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <div class="d-flex justify-content-end mb-2">
                        <span class="badge badge-info" id="equipments-selected-count">0 مختار</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 42px;">
                                        <input type="checkbox" id="equipments-select-all" class="equipment-table-checkbox" aria-label="Select all equipment">
                                    </th>
                                    <th><a href="{{ $sortUrl('id') }}" style="color: inherit;"># {!! $sortIcon('id') !!}</a></th>
                                    {{-- <th><a href="{{ $sortUrl('project_name') }}" style="color: inherit;">اسم المشروع {!! $sortIcon('project_name') !!}</a></th> --}}
                                    <th><a href="{{ $sortUrl('company_id') }}" style="color: inherit;">اسم الشركة {!! $sortIcon('company_id') !!}</a></th>
                                    <th><a href="{{ $sortUrl('equipment_type') }}" style="color: inherit;">نوع المعدة {!! $sortIcon('equipment_type') !!}</a></th>
                                    <th><a href="{{ $sortUrl('model_year') }}" style="color: inherit;">موديل المعدة {!! $sortIcon('model_year') !!}</a></th>
                                    <th><a href="{{ $sortUrl('equipment_code') }}" style="color: inherit;">كود المعدة {!! $sortIcon('equipment_code') !!}</a></th>
                                    <th>نوع المعدة (فعلي او اختياري)</th>
                                    <th><a href="{{ $sortUrl('equipment_number') }}" style="color: inherit;">رقم شاسيه المعدة {!! $sortIcon('equipment_number') !!}</a></th>
                                    <th><a href="{{ $sortUrl('current_driver') }}" style="color: inherit;">اسم السائق الحالي {!! $sortIcon('current_driver') !!}</a></th>
                                    <th><a href="{{ $sortUrl('manufacture') }}" style="color: inherit;">المصنع {!! $sortIcon('manufacture') !!}</a></th>
                                    <th><a href="{{ $sortUrl('entry_per_ser') }}" style="color: inherit;">تصريح الدخول {!! $sortIcon('entry_per_ser') !!}</a></th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($equipments as $equipment)
                                    <tr class="{{ $equipmentSelectableRowClass }}" data-equipment-id="{{ $equipment->id }}">
                                        <td class="text-center">
                                            <input
                                                type="checkbox"
                                                id="equipment-select-{{ $equipment->id }}"
                                                name="selected_equipment_ids[]"
                                                class="equipment-table-checkbox equipment-select-checkbox"
                                                value="{{ $equipment->id }}"
                                                data-equipment-option="{{ trim((string) ($equipment->equipment_option ?? '')) }}"
                                                aria-label="Select equipment {{ $equipment->equipment_code ?? $equipment->id }}"
                                            >
                                        </td>
                                        <td>{{ $loop->iteration + ($equipments->currentPage() - 1) * $equipments->perPage() }}</td>
                                        {{-- <td>{{ $equipment->project_name }}</td> --}}
                                        <td>{{ optional($equipment->company)->name ?? 'غير متوفر' }}</td>
                                        <td>{{ $equipment->equipment_type }}</td>
                                        <td>{{ $equipment->model_year ?? 'غير متوفر' }}</td>
                                        <td>{{ $equipment->equipment_code }}</td>
                                        <td>{{ $equipment->equipment_option ?? 'غير متوفر' }}</td>
                                        <td>{{ $equipment->equipment_number ?? 'غير متوفر' }}</td>
                                        <td>{{ $equipment->current_driver ?? 'غير متوفر' }}</td>
                                        <td>{{ $equipment->manufacture ?? 'غير متوفر' }}</td>
                                        <td>{{ $equipment->entry_per_ser ?? 'غير متوفر' }}</td>
                                        <td>
                                            <a href="{{ route('equipment.show', $equipment->id) }}" class="btn btn-info btn-sm" title="View"><i class="tim-icons icon-notes"></i></a>
                                            <a href="{{ route('equipment.exportWord', $equipment->id) }}"
                                               class="btn btn-sm btn-primary"
                                               target="_blank">
                                                طباعة الفحص
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $equipments->links() }}
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
        const selectAllCheckbox = document.getElementById('equipments-select-all');
        const selectedCountElement = document.getElementById('equipments-selected-count');
        const rowCheckboxes = Array.from(document.querySelectorAll('.equipment-select-checkbox'));
        const exportSelectedButtons = Array.from(document.querySelectorAll('.js-export-selected'));
        const inspectionMonthInput = document.getElementById('inspection_month');
        const selectAllActualBtn = document.getElementById('select-all-actual');
        const selectAllOptionalBtn = document.getElementById('select-all-optional');

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
                    selectedCount++;
                    selectedIds.push(checkbox.value);
                    row.classList.add('equipment-row-selected');
                } else {
                    row.classList.remove('equipment-row-selected');
                }
            });

            if (selectedCountElement) {
                selectedCountElement.textContent = selectedCount + ' مختار';
            }

            selectAllCheckbox.checked = selectedCount === rowCheckboxes.length;
            selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < rowCheckboxes.length;

            exportSelectedButtons.forEach(function (button) {
                const baseHref = button.dataset.baseHref || button.href;
                if (selectedIds.length > 0) {
                    const query = new URLSearchParams();
                    query.set('ids', selectedIds.join(','));

                    if (inspectionMonthInput && inspectionMonthInput.value) {
                        query.set('month', inspectionMonthInput.value);
                    }

                    button.href = baseHref + '?' + query.toString();
                    button.classList.remove('disabled');
                    button.setAttribute('aria-disabled', 'false');
                } else {
                    button.href = baseHref;
                    button.classList.add('disabled');
                    button.setAttribute('aria-disabled', 'true');
                }
            });
        };

        exportSelectedButtons.forEach(function (button) {
            button.addEventListener('click', function (event) {
                const selectedIds = rowCheckboxes.filter(function (checkbox) {
                    return checkbox.checked;
                });

                if (selectedIds.length === 0) {
                    event.preventDefault();
                    alert('Please select at least one equipment first.');
                }
            });
        });

        selectAllCheckbox.addEventListener('change', function () {
            rowCheckboxes.forEach(function (checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });

            syncUI();
        });

        rowCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', syncUI);
        });

        const selectByOption = function (optionLabel) {
            rowCheckboxes.forEach(function (checkbox) {
                const option = (checkbox.dataset.equipmentOption || '').trim();
                checkbox.checked = option === optionLabel;
            });

            syncUI();
        };

        if (selectAllActualBtn) {
            selectAllActualBtn.addEventListener('click', function () {
                selectByOption('فعلي');
            });
        }

        if (selectAllOptionalBtn) {
            selectAllOptionalBtn.addEventListener('click', function () {
                selectByOption('اختياري');
            });
        }

        if (inspectionMonthInput) {
            inspectionMonthInput.addEventListener('change', syncUI);
        }

        syncUI();
    });
</script>
@endsection
