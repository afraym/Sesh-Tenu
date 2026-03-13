@extends('layouts.back')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                <a class="navbar-brand">
                @if(auth()->check() && auth()->user()->company)
                <img src="{{ asset(auth()->user()->company->logo)  }}" alt="{{ auth()->user()->company->name }}" class="company-logo" style="width: 90px;height: 90px;">
                @endif
                </a>
                    <h4 class="card-title mb-0">Equipment Type Details / تفاصيل نوع المعدة</h4>
                    <a href="{{ route('equipment-types.index') }}" class="btn btn-secondary btn-sm">
                        <i class="tim-icons icon-minimal-left"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th style="width: 220px;">Name / الإسم</th>
                                    <td>{{ $equipmentType->name }}</td>
                                </tr>
                                <tr>
                                    <th>Description / الوصف</th>
                                    <td>{{ $equipmentType->description ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Status / الحالة</th>
                                    <td>
                                        @if($equipmentType->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $equipmentType->created_at ? $equipmentType->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('equipment-types.edit', $equipmentType->id) }}" class="btn btn-warning btn-sm">
                            <i class="tim-icons icon-pencil"></i> Edit
                        </a>
                        <form action="{{ route('equipment-types.destroy', $equipmentType->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this equipment type?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="tim-icons icon-trash-simple"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
