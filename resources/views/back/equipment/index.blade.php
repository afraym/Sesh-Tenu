@extends('layouts.back')
@section('content')
@php
    $sort = $sort ?? request('sort', 'created_at');
    $direction = $direction ?? request('direction', 'desc');

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
                <div class="card-header">
                    <h4 class="card-title">Equipment List / قائمة المعدات</h4>
                    <a href="{{ route('equipment.create') }}" class="btn btn-primary btn-sm float-right">Add Equipment / إضافة معدة</a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><a href="{{ $sortUrl('id') }}" style="color: inherit;"># {!! $sortIcon('id') !!}</a></th>
                                    {{-- <th><a href="{{ $sortUrl('project_name') }}" style="color: inherit;">اسم المشروع {!! $sortIcon('project_name') !!}</a></th> --}}
                                    <th><a href="{{ $sortUrl('company_id') }}" style="color: inherit;">اسم الشركة {!! $sortIcon('company_id') !!}</a></th>
                                    <th><a href="{{ $sortUrl('equipment_type') }}" style="color: inherit;">نوع المعدة {!! $sortIcon('equipment_type') !!}</a></th>
                                    <th><a href="{{ $sortUrl('model_year') }}" style="color: inherit;">موديل المعدة {!! $sortIcon('model_year') !!}</a></th>
                                    <th><a href="{{ $sortUrl('equipment_code') }}" style="color: inherit;">كود المعدة {!! $sortIcon('equipment_code') !!}</a></th>
                                    <th><a href="{{ $sortUrl('equipment_number') }}" style="color: inherit;">رقم شاسيه المعدة {!! $sortIcon('equipment_number') !!}</a></th>
                                    <th><a href="{{ $sortUrl('current_driver') }}" style="color: inherit;">اسم السائق الحالي {!! $sortIcon('current_driver') !!}</a></th>
                                    <th><a href="{{ $sortUrl('manufacture') }}" style="color: inherit;">المصنع {!! $sortIcon('manufacture') !!}</a></th>
                                    <th><a href="{{ $sortUrl('entry_per_ser') }}" style="color: inherit;">تصريح الدخول {!! $sortIcon('entry_per_ser') !!}</a></th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($equipments as $equipment)
                                    <tr>
                                        <td>{{ $loop->iteration + ($equipments->currentPage() - 1) * $equipments->perPage() }}</td>
                                        {{-- <td>{{ $equipment->project_name }}</td> --}}
                                        <td>{{ optional($equipment->company)->name ?? 'غير متوفر' }}</td>
                                        <td>{{ $equipment->equipment_type }}</td>
                                        <td>{{ $equipment->model_year ?? 'غير متوفر' }}</td>
                                        <td>{{ $equipment->equipment_code }}</td>
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
@endsection
