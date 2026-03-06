@extends('layouts.back')
@section('content')
@php
    $deliveries = $deliveries ?? collect();
    $sort = $sort ?? request('sort', 'created_at');
    $direction = $direction ?? request('direction', 'desc');
    $sortUrl = function (string $column) use ($sort, $direction) {
        $nextDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';
        return route('worker-document-deliveries.index', array_merge(request()->query(), [
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
    $shiftNames = [
        'morning' => 'صباحية',
        'evening' => 'ليلية',
        'mixed' => 'كلاهما',
    ];
    $shiftOptions = [
        'morning' => 'صباحية',
        'night' => 'ليلية',
        'both' => 'كلاهما',
    ];
@endphp
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center text-center">
                        <div class="col"></div>
                        <div class="col">
                            <h4 class="card-title mb-0">متابعة استلام السيركي</h4>
                        </div>
                        <div class="col text-right">
                            <a href="{{ route('worker-document-deliveries.receive') }}" class="btn btn-sm btn-success">
                                <i class="tim-icons icon-vector"></i> تسجيل سريع جماعي
                            </a>
                            <a href="{{ route('worker-document-deliveries.create') }}" class="btn btn-sm btn-primary">
                                <i class="tim-icons icon-simple-add"></i> تسليم جديد
                            </a>
                        </div>
                    </div>
                    @if(session('success'))
                        <div class="alert alert-success alert-with-icon mx-3 my-3" role="alert">
                            <span class="data"><b>نجاح:</b> {{ session('success') }}</span>
                        </div>
                    @endif
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('worker-document-deliveries.index') }}" class="mb-0">
                                <div class="row">
                                    <div class="col-md-3">
                                        <input type="text" name="search" class="form-control" placeholder="ابحث باسم العامل أو الرقم القومي" value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <select name="year" class="form-control">
                                            <option value="">-- كل السنوات --</option>
                                            @for($y = 2026; $y <= now()->year; $y++)
                                                <option value="{{ $y }}" {{ (request('year') ?: now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="month" class="form-control">
                                            <option value="">-- كل الأشهر --</option>
                                            @foreach($monthNames as $num => $name)
                                                <option value="{{ $num }}" {{ (request('month') ?: now()->month) == $num ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="shift" class="form-control">
                                            <option value="">-- كل الفترات --</option>
                                            @foreach($shiftOptions as $key => $name)
                                                <option value="{{ $key }}" {{ request('shift') == $key ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <button type="submit" class="btn btn-primary btn-sm"><i class="tim-icons icon-zoom-split"></i></button>
                                            <a href="{{ route('worker-document-deliveries.index') }}" class="btn btn-secondary btn-sm"><i class="tim-icons icon-refresh-01"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table tablesorter">
                        <thead class="text-primary">
                            <tr>
                                <th>
                                    <a href="{{ $sortUrl('worker_id') }}">
                                        العامل {!! $sortIcon('worker_id') !!}
                                    </a>
                                </th>
                                <th>الرقم القومي</th>
                                <th>
                                    <a href="{{ $sortUrl('year') }}">
                                        السنة {!! $sortIcon('year') !!}
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ $sortUrl('month') }}">
                                        الشهر {!! $sortIcon('month') !!}
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ $sortUrl('shift') }}">
                                        الفترة {!! $sortIcon('shift') !!}
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ $sortUrl('morning_delivery_date') }}">
                                        التسليم الصباحية {!! $sortIcon('morning_delivery_date') !!}
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ $sortUrl('evening_delivery_date') }}">
                                        التسليم المسائية {!! $sortIcon('evening_delivery_date') !!}
                                    </a>
                                </th>
                                <th class="text-right">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deliveries as $delivery)
                                <tr>
                                    <td>
                                        <a href="{{ route('workers.show', $delivery->worker_id) }}">
                                            {{ $delivery->worker->name ?? '-' }}
                                        </a>
                                    </td>
                                    <td>{{ $delivery->worker->national_id ?? '-' }}</td>
                                    <td>{{ $delivery->year }}</td>
                                    <td>{{ $monthNames[$delivery->month] ?? '-' }}</td>
                                    <td>{{ $shiftNames[$delivery->shift] ?? '-' }}</td>
                                    <td>
                                        @if($delivery->morning_delivery_date)
                                            <span class="badge badge-success">{{ $delivery->morning_delivery_date->format('m-d') }}</span>
                                        @else
                                            <span class="badge badge-secondary">غير مسلم</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($delivery->evening_delivery_date)
                                            <span class="badge badge-success">{{ $delivery->evening_delivery_date->format('m-d') }}</span>
                                        @else
                                            <span class="badge badge-secondary">غير مسلم</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('worker-document-deliveries.show', $delivery->id) }}" class="btn btn-sm btn-info" title="عرض">
                                            <i class="tim-icons icon-zoom-split"></i>
                                        </a>
                                        <a href="{{ route('worker-document-deliveries.edit', $delivery->id) }}" class="btn btn-sm btn-warning" title="تعديل">
                                            <i class="tim-icons icon-pencil"></i>
                                        </a>
                                        <form action="{{ route('worker-document-deliveries.destroy', $delivery->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="حذف" onclick="return confirm('هل أنت متأكد من حذف هذا التسليم؟')">
                                                <i class="tim-icons icon-simple-remove"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        لا توجد تسليمات مستندات
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    @if($deliveries->hasPages())
                        {{ $deliveries->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
