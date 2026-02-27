@extends('themes.blk.back.app')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title mb-0">Users List / قائمة المستخدمين</h4>
                        </div>
                        <div class="col text-right">
                            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                                <i class="tim-icons icon-simple-add"></i> Add New User
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

                    @if($users->isEmpty())
                        <div class="alert alert-info text-center">
                            <i class="tim-icons icon-alert-circle-exc"></i>
                            No users found. Click "Add New User" to create one.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name / الاسم</th>
                                        <th>Email / البريد الإلكتروني</th>
                                        <th>Role / الدور</th>
                                        <th>Company / الشركة</th>
                                        <th>Joined / تاريخ الانضمام</th>
                                        <th class="text-center">Actions / الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                            <td>
                                                <strong>{{ $user->name }}</strong>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @if($user->role === 'super_admin')
                                                    <span class="badge badge-danger">Super Admin</span>
                                                @elseif($user->role === 'admin')
                                                    <span class="badge badge-warning">Admin</span>
                                                @elseif($user->role === 'company_owner')
                                                    <span class="badge badge-info">Company Owner</span>
                                                @else
                                                    <span class="badge badge-secondary">Employee</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($user->company)
                                                    {{ $user->company->name }}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('users.edit', $user->id) }}" 
                                                       class="btn btn-sm btn-info" 
                                                       title="Edit">
                                                        <i class="tim-icons icon-pencil"></i>
                                                    </a>
                                                    
                                                    @if(auth()->check() && auth()->user()->id !== $user->id)
                                                        <form action="{{ route('users.destroy', $user->id) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-danger" 
                                                                    title="Delete">
                                                                <i class="tim-icons icon-trash-simple"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $users->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
