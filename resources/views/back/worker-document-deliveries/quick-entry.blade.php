@extends('layouts.back')
@section('content')
@php
    $monthNames = [
        1 => 'يناير',
        2 => 'فبراير',
        3 => 'مارس',
        4 => 'أبريل',
        5 => 'مايو',
        6 => 'يونيو',
        7 => 'يوليو',
        8 => 'أغسطس',
        9 => 'سبتمبر',
        10 => 'أكتوبر',
        11 => 'نوفمبر',
        12 => 'ديسمبر',
    ];
    $sort = request('sort', 'name');
    $direction = request('direction', 'asc');
    $sortUrl = function (string $column) use ($sort, $direction) {
        $nextDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';
        return route('worker-document-deliveries.quick-entry', [
            'year' => request('year'),
            'month' => request('month'),
            'sort' => $column,
            'direction' => $nextDirection,
        ]);
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
                <div class="card-header">
                    <div class="row align-items-center text-center">
                        <div class="col"></div>
                        <div class="col">
                            <h4 class="card-title mb-0">تسجيل التسليمات الجماعي السريع</h4>
                        </div>
                        <div class="col text-right">
                            <a href="{{ route('worker-document-deliveries.index') }}" class="btn btn-sm btn-secondary">
                                <i class="tim-icons icon-zoom-split"></i> القائمة الكاملة
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

                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('worker-document-deliveries.quick-entry') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="year">السنة</label>
                                    <select name="year" id="year" class="form-control" onchange="this.form.submit()">
                                        @for($y = 2026; $y <= now()->year; $y++)
                                            <option value="{{ $y }}" {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="month">الشهر</label>
                                    <select name="month" id="month" class="form-control" onchange="this.form.submit()">
                                        @foreach($monthNames as $num => $name)
                                            <option value="{{ $num }}" {{ ($month ?? now()->month) == $num ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="tim-icons icon-zoom-split"></i> بحث
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <hr>

                    <!-- Bulk Entry Form -->
                    <form action="{{ route('worker-document-deliveries.bulk-store') }}" method="POST" id="bulkForm" onsubmit="return validateForm()">
                        @csrf
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="month" value="{{ $month }}">

                        <div class="table-responsive">
                            <table class="table tablesorter">
                                <thead class="text-primary">
                                    <tr>
                                        <th><a href="{{ $sortUrl('id') }}" style="color: inherit;font-weight: 700;">#{!! $sortIcon('id') !!}</a></th>
                                        <th><a href="{{ $sortUrl('name') }}" style="color: inherit;font-weight: 700;">اسم العامل {!! $sortIcon('name') !!}</a></th>
                                        <th><a href="{{ $sortUrl('national_id') }}" style="color: inherit;font-weight: 700;">الرقم القومي {!! $sortIcon('national_id') !!}</a></th>
                                         @if(auth()->check() && auth()->user()->isSuperAdmin())
                                        <th><a href="{{ $sortUrl('company_id') }}" style="color: inherit;font-weight: 700;">الشركة {!! $sortIcon('company_id') !!}</a></th>
                                        @endif
                                        <th><a href="{{ $sortUrl('job_type_id') }}" style="color: inherit;font-weight: 700;">الوظيفة {!! $sortIcon('job_type_id') !!}</a></th>
                                        <th><a href="{{ $sortUrl('morning_delivery_date') }}" style="color: inherit;font-weight: 700;">التسليم الصباحية {!! $sortIcon('morning_delivery_date') !!}</a></th>
                                        <th><a href="{{ $sortUrl('evening_delivery_date') }}" style="color: inherit;font-weight: 700;">التسليم المسائية {!! $sortIcon('evening_delivery_date') !!}</a></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($workers as $index => $worker)
                                        @php
                                            $morningDelivery = $deliveries->get("{$worker->id}_morning");
                                            $eveningDelivery = $deliveries->get("{$worker->id}_evening");
                                            $morningDate = optional($morningDelivery?->morning_delivery_date)->format('Y-m-d');
                                            $eveningDate = optional($eveningDelivery?->evening_delivery_date)->format('Y-m-d');
                                            $morningShort = optional($morningDelivery?->morning_delivery_date)->format('m-d');
                                            $eveningShort = optional($eveningDelivery?->evening_delivery_date)->format('m-d');
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>
                                                    <a href="{{ route('workers.show', $worker->id) }}" target="_blank" class="text-primary" title="عرض تفاصيل العامل">
                                                        {{ $worker->name }}
                                                    </a>
                                                </strong>
                                            </td>
                                            <td>
                                                <a class="text-center" data-toggle="tooltip" data-placement="top" title="{{ $worker->national_id }}" data-original-title="{{ $worker->national_id }}">
                                                    <i class="fas fa-id-card"></i>
                                                </a>
                                            </td>
                                             @if(auth()->check() && auth()->user()->isSuperAdmin())
                                            <td>
                                                <small>{{ $worker->company->name ?? '-' }}</small>
                                            </td>
                                            @endif
                                            <td>
                                                <small>{{ $worker->jobType->name ?? '-' }}</small>
                                            </td>
                                                                                        <td class="date-cell">
                                                <input type="hidden" name="deliveries[{{ $worker->id }}][worker_id]" value="{{ $worker->id }}">
                                                  <input type="hidden"
                                                      name="deliveries[{{ $worker->id }}][morning_date]"
                                                      class="delivery-date-value"
                                                      value="{{ $morningDate }}">
                                                  <input type="text"
                                                      class="form-control form-control-sm delivery-date"
                                                      data-worker-id="{{ $worker->id }}"
                                                      data-shift="morning"
                                                      data-hidden-name="deliveries[{{ $worker->id }}][morning_date]"
                                                      value="{{ $morningShort }}"
                                                      placeholder="MM-DD"
                                                      maxlength="5"
                                                      autocomplete="off"
                                                      lang="ar" dir="rtl">
                                            </td>
                                            <td class="date-cell">
                                                  <input type="hidden"
                                                      name="deliveries[{{ $worker->id }}][evening_date]"
                                                      class="delivery-date-value"
                                                      value="{{ $eveningDate }}">
                                                  <input type="text"
                                                      class="form-control form-control-sm delivery-date"
                                                      data-worker-id="{{ $worker->id }}"
                                                      data-shift="evening"
                                                      data-hidden-name="deliveries[{{ $worker->id }}][evening_date]"
                                                      value="{{ $eveningShort }}"
                                                      placeholder="MM-DD"
                                                      maxlength="5"
                                                      autocomplete="off"
                                                      lang="ar" dir="rtl">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                لا توجد عمال نشطين
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="tim-icons icon-single-copy-04"></i> حفظ جميع التسليمات
                            </button>
                            <a href="{{ route('worker-document-deliveries.index') }}" class="btn btn-secondary">
                                <i class="tim-icons icon-simple-remove"></i> إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function validateForm() {
    const dates = document.querySelectorAll('.delivery-date-value');
    let hasData = false;
    
    dates.forEach(input => {
        if (input.value) {
            hasData = true;
        }
    });
    
    if (!hasData) {
        alert('الرجاء تحديد تاريخ تسليم واحد على الأقل');
        return false;
    }
    
    return true;
}

// Save single cell automatically when date changes
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const selectedYear = Number({{ $year }});

    const mmDdToYmd = (mmDd) => {
        const value = (mmDd || '').trim();
        if (!value) return null;

        const parts = value.split('-');
        if (parts.length !== 2) return null;

        const month = String(parts[0]).padStart(2, '0');
        const day = String(parts[1]).padStart(2, '0');
        const m = Number(month);
        const d = Number(day);

        if (Number.isNaN(m) || Number.isNaN(d) || m < 1 || m > 12 || d < 1 || d > 31) {
            return null;
        }

        return `${selectedYear}-${month}-${day}`;
    };

    const saveDelivery = async (inputEl) => {
        const workerId = inputEl.dataset.workerId;
        const shift = inputEl.dataset.shift;
        const hiddenName = inputEl.dataset.hiddenName;
        const hiddenInput = hiddenName ? document.querySelector(`input[name="${hiddenName}"]`) : null;

        if (!hiddenInput) {
            return;
        }

        const dateValue = mmDdToYmd(inputEl.value);

        if (inputEl.value && !dateValue) {
            inputEl.style.borderColor = '#dc3545';
            return;
        }

        hiddenInput.value = dateValue || '';

        if (!workerId || !shift || !csrfToken) {
            return;
        }

        // Avoid duplicate requests when datepicker triggers multiple events.
        if (inputEl.dataset.lastSentValue === String(dateValue)) {
            return;
        }

        const originalBorder = inputEl.style.borderColor;
        inputEl.style.borderColor = '#17a2b8';

        try {
            const response = await fetch('{{ route('worker-document-deliveries.ajax-update') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    worker_id: Number(workerId),
                    year: Number({{ $year }}),
                    month: Number({{ $month }}),
                    shift: shift,
                    date: dateValue,
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                inputEl.style.borderColor = '#dc3545';
                alert(data.message || 'تعذر حفظ التسليم');
                return;
            }

            inputEl.dataset.lastSentValue = String(dateValue);
            inputEl.style.borderColor = '#28a745';
            setTimeout(() => {
                inputEl.style.borderColor = originalBorder;
            }, 1200);
        } catch (error) {
            inputEl.style.borderColor = '#dc3545';
            alert('حدث خطأ أثناء الحفظ، حاول مرة أخرى');
        }
    };

    document.querySelectorAll('.delivery-date').forEach((input) => {
        // Initialize bootstrap datepicker on m-d text inputs.
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.datepicker) {
            const $input = window.jQuery(input);
            $input.datepicker({
                format: 'mm-dd',
                autoclose: true,
                todayHighlight: true,
                language: 'ar',
                orientation: 'auto'
            });
        }

        input.addEventListener('input', function () {
            const onlyAllowed = this.value.replace(/[^0-9-]/g, '');
            if (onlyAllowed !== this.value) {
                this.value = onlyAllowed;
            }
        });

        input.addEventListener('input', function () { saveDelivery(this); });
        input.addEventListener('change', function () { saveDelivery(this); });
        input.addEventListener('blur', function () { saveDelivery(this); });

        // Support Bootstrap datepicker event if present in this layout.
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.datepicker) {
            window.jQuery(input).on('changeDate', function () {
                saveDelivery(input);
            });
        }
    });
});

// Enable Tab key to move between date fields
document.addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && e.target.classList.contains('delivery-date')) {
        e.preventDefault();
        const inputs = Array.from(document.querySelectorAll('.delivery-date'));
        const currentIndex = inputs.indexOf(e.target);
        if (currentIndex < inputs.length - 1) {
            inputs[currentIndex + 1].focus();
        }
    }
});
</script>

<style>
/* Style for quick entry date fields */
.delivery-date {
    font-weight: 500;
}

.delivery-date:focus {
    background-color: #fff3cd;
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}

@media (max-width: 767.98px) {
    .table td.date-cell,
    .table th.date-cell {
        min-width: 50px;
        width: 150px;
    }

    .delivery-date {
        min-width: 50px;
    }
}
</style>
@endsection
