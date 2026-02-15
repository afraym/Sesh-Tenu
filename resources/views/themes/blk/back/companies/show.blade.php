@extends('themes.blk.back.app')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Company Details / تفاصيل الشركة</h4>
                    <a href="{{ route('companies.index') }}" class="btn btn-secondary btn-sm">
                        <i class="tim-icons icon-minimal-left"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            @if($company->logo)
                                <img src="{{ asset($company->logo) }}" alt="{{ $company->name }}" class="img-fluid rounded" style="max-height: 180px; object-fit: contain;">
                            @else
                                <div class="bg-secondary text-white text-center rounded" style="height: 180px; line-height: 180px;">
                                    <i class="tim-icons icon-badge" style="font-size: 32px;"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <div class="table-responsive">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <th style="width: 220px;">Name / الإسم</th>
                                            <td>{{ $company->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Short Name / الاسم المختصر</th>
                                            <td>{{ $company->short_name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Phone / الهاتف</th>
                                            <td>{{ $company->phone ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Address / العنوان</th>
                                            <td>{{ $company->address ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Created At</th>
                                            <td>{{ $company->created_at ? $company->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-warning btn-sm">
                                    <i class="tim-icons icon-pencil"></i> Edit
                                </a>
                                <form action="{{ route('companies.destroy', $company->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this company?');">
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
    </div>
</div>
@endsection
