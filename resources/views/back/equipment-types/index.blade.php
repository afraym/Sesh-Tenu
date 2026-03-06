@extends('layouts.back')
@section('content')
@php
    $equipmentTypesData = $equipmentTypes ?? collect();
@endphp
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title mb-0">Equipment Types List / قائمة أنواع المعدات</h4>
                        </div>
                        <div class="col text-right">
                            <a href="{{ route('equipment-types.create') }}" class="btn btn-primary btn-sm">
                                <i class="tim-icons icon-simple-add"></i> Add New Equipment Type
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

                    @if($equipmentTypesData->isEmpty())
                        <div class="alert alert-info text-center">
                            <i class="tim-icons icon-alert-circle-exc"></i>
                            No equipment types found. Click "Add New Equipment Type" to create one.
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
                                    @foreach($equipmentTypesData as $equipmentType)
                                        <tr>
                                            <td>
                                                @if($equipmentTypesData instanceof \Illuminate\Pagination\AbstractPaginator)
                                                    {{ $loop->iteration + ($equipmentTypesData->currentPage() - 1) * $equipmentTypesData->perPage() }}
                                                @else
                                                    {{ $loop->iteration }}
                                                @endif
                                            </td>
                                            <td><strong>{{ $equipmentType->name }}</strong></td>
                                            <td>{{ \Illuminate\Support\Str::limit($equipmentType->description ?? 'N/A', 50) }}</td>
                                            <td>
                                                @if($equipmentType->is_active)
                                                    <span class="badge badge-success">مفعلة</span>
                                                @else
                                                    <span class="badge badge-secondary">غير مفعلة</span>
                                                @endif
                                            </td>
                                            <td>{{ $equipmentType->created_at ? $equipmentType->created_at->format('Y-m-d') : 'N/A' }}</td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('equipment-types.show', $equipmentType->id) }}" class="btn btn-info btn-sm" title="View">
                                                        <i class="tim-icons icon-notes"></i>
                                                    </a>
                                                    <a href="{{ route('equipment-types.edit', $equipmentType->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="tim-icons icon-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('equipment-types.destroy', $equipmentType->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this equipment type?');">
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

                        @if($equipmentTypesData instanceof \Illuminate\Pagination\AbstractPaginator)
                            <div class="mt-3">
                                {{ $equipmentTypesData->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
