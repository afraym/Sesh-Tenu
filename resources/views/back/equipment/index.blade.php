@extends('layouts.back')
@section('content')
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
                                    <th>#</th>
                                    <th>اسم المشروع</th>
                                    <th>اسم الشركة</th>
                                    <th>نوع المعدة</th>
                                    <th>كود المعدة</th>
                                    <th>اسم السائق الحالي</th>
                                    <th>المصنع</th>
                                    <th>تصريح الدخول</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($equipments as $equipment)
                                    <tr>
                                        <td>{{ $loop->iteration + ($equipments->currentPage() - 1) * $equipments->perPage() }}</td>
                                        <td>{{ $equipment->project_name }}</td>
                                        <td>{{ optional($equipment->company)->name ?? 'غير متوفر' }}</td>
                                        <td>{{ $equipment->equipment_type }}</td>
                                        <td>{{ $equipment->equipment_code }}</td>
                                        <td>{{ $equipment->current_driver ?? 'غير متوفر' }}</td>
                                        <td>{{ $equipment->manufacture ?? 'غير متوفر' }}</td>
                                        <td>{{ $equipment->entry_per_ser ?? 'غير متوفر' }}</td>
                                        <td>
                                            <a href="{{ route('equipment.show', $equipment->id) }}" class="btn btn-info btn-sm" title="View"><i class="tim-icons icon-notes"></i></a>
                                            <a href="{{ route('equipment.exportWord', $equipment->id) }}"
                                               class="btn btn-sm btn-primary"
                                               target="_blank">
                                                Print Word
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
