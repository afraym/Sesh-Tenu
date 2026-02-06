@extends('themes.blk.back.app')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title mb-0">Companies List / قائمة الشركات</h4>
                        </div>
                        <div class="col text-right">
                            <a href="{{ route('companies.create') }}" class="btn btn-primary btn-sm">
                                <i class="tim-icons icon-simple-add"></i> Add New Company
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

                    @if($companies->isEmpty())
                        <div class="alert alert-info text-center">
                            <i class="tim-icons icon-alert-circle-exc"></i>
                            No companies found. Click "Add New Company" to create one.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Logo / الشعار</th>
                                        <th>Name / الإسم</th>
                                        <th>Phone / الهاتف</th>
                                        <th>Address / العنوان</th>
                                        <th>Workers / العمال</th>
                                        <th>Created / تاريخ الإنشاء</th>
                                        <th class="text-center">Actions / الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($companies as $company)
                                        <tr>
                                            <td>{{ $loop->iteration + ($companies->currentPage() - 1) * $companies->perPage() }}</td>
                                            <td>
                                                @if($company->logo)
                                                    <img src="{{ asset('storage/' . $company->logo) }}" 
                                                         alt="{{ $company->name }}" 
                                                         class="img-thumbnail" 
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <div class="bg-secondary text-white text-center" 
                                                         style="width: 50px; height: 50px; line-height: 50px; border-radius: 4px;">
                                                        <i class="tim-icons icon-badge"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $company->name }}</strong>
                                            </td>
                                            <td>{{ $company->phone ?? 'N/A' }}</td>
                                            <td>{{ Str::limit($company->address ?? 'N/A', 30) }}</td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $company->workers_count ?? $company->workers->count() }}
                                                </span>
                                            </td>
                                            <td>{{ $company->created_at->format('Y-m-d') }}</td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('companies.show', $company->id) }}" 
                                                       class="btn btn-info btn-sm" 
                                                       title="View">
                                                        <i class="tim-icons icon-notes"></i>
                                                    </a>
                                                    <a href="{{ route('companies.edit', $company->id) }}" 
                                                       class="btn btn-warning btn-sm" 
                                                       title="Edit">
                                                        <i class="tim-icons icon-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('companies.destroy', $company->id) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to delete this company?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-danger btn-sm" 
                                                                title="Delete">
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

                        <div class="mt-3">
                            {{ $companies->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
