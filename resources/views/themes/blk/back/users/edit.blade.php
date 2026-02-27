@extends('themes.blk.back.app')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit User / تعديل المستخدم</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Full Name / الاسم الكامل <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" 
                                           value="{{ old('name', $user->name) }}" 
                                           placeholder="Enter full name" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address / البريد الإلكتروني <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email"
                                           value="{{ old('email', $user->email) }}"
                                           placeholder="Enter email address" required>
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">New Password / كلمة المرور الجديدة</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" 
                                           placeholder="Leave blank to keep current password">
                                    <small class="form-text text-muted">Leave blank if you don't want to change the password</small>
                                    @error('password')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm New Password / تأكيد كلمة المرور</label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation" 
                                           placeholder="Confirm new password">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role">Role / الدور <span class="text-danger">*</span></label>
                                    <select class="form-control @error('role') is-invalid @enderror" 
                                            id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        @if(auth()->check() && auth()->user()->isSuperAdmin())
                                            <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                        @endif
                                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="company_owner" {{ old('role', $user->role) == 'company_owner' ? 'selected' : '' }}>Company Owner</option>
                                        <option value="employee" {{ old('role', $user->role) == 'employee' ? 'selected' : '' }}>Employee</option>
                                    </select>
                                    @error('role')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company_id">Company / الشركة</label>
                                    <select class="form-control @error('company_id') is-invalid @enderror" 
                                            id="company_id" name="company_id">
                                        <option value="">No Company (For Admins)</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Required for Company Owners and Employees</small>
                                    @error('company_id')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="tim-icons icon-check-2"></i> Update User
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="tim-icons icon-simple-remove"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const companySelect = document.getElementById('company_id');
    
    roleSelect.addEventListener('change', function() {
        const role = this.value;
        
        // Make company required for company_owner and employee roles
        if (role === 'company_owner' || role === 'employee') {
            companySelect.required = true;
            companySelect.parentElement.querySelector('label').innerHTML = 
                'Company / الشركة <span class="text-danger">*</span>';
        } else {
            companySelect.required = false;
            companySelect.parentElement.querySelector('label').innerHTML = 
                'Company / الشركة';
        }
    });
    
    // Trigger on page load
    roleSelect.dispatchEvent(new Event('change'));
});
</script>
@endsection
