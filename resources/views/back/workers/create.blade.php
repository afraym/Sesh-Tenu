@extends('layouts.back')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header  align-items-center text-center">
                    <a class="navbar-brand">
            @if(auth()->check() && auth()->user()->company)
              <img src="{{ asset(auth()->user()->company->logo)  }}" alt="{{ auth()->user()->company->name }}" class="company-logo" style="width: 90px;height: 90px;">
            @endif
          </a>
                    <h4 class="card-title">اضافة عامل جديد</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('workers.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            @if( auth()->user()->isSuperAdmin())
                            <div class="col-md 4"></div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="company_id">Company / الشركة <span class="text-danger">*</span></label>
                                    <select class="form-control @error('company_id') is-invalid @enderror" 
                                            id="company_id" name="company_id" required>
                                        <option value="">اختر شركة</option>                                   
                                            {{-- <option value="{{ auth()->user()->company_id }}" selected>
                                                {{ auth()->user()->company->name }}
                                            </option> --}}
                                            {{-- @else --}}
                                            @foreach($companies ?? [] as $company)
                                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                    {{ $company->name }}
                                                </option>
                                            @endforeach
                                    </select>
                                    @error('company_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md 4"></div>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name / الاسم <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" 
                                           value="{{ old('name') }}" 
                                           placeholder="Enter worker name" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                                    <div class="col-md-6">
                                <div class="form-group">
                                    <label for="national_id">National ID / الرقم القومي <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('national_id') is-invalid @enderror" 
                                           id="national_id" name="national_id" 
                                           value="{{ old('national_id') }}" 
                                           placeholder="Enter national ID" required>
                                    @error('national_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone_number">Phone Number / رقم الهاتف <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('phone_number') is-invalid @enderror" 
                                           id="phone_number" name="phone_number" 
                                           value="{{ old('phone_number') }}" 
                                           placeholder="Enter phone number" required>
                                    @error('phone_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address">Address / العنوان</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                           id="address" name="address" 
                                           value="{{ old('address') }}" 
                                           placeholder="Enter address">
                                    @error('address')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="join_date">Join Date / تاريخ الانضمام</label>
                                    <input type="date" class="form-control @error('join_date') is-invalid @enderror" 
                                           id="join_date" name="join_date" 
                                         lang="ar" dir="rtl"
                                         value="{{ old('join_date', now()->toDateString()) }}">
                                    @error('join_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">End Date / تاريخ الانهاء</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" name="end_date" 
                                         lang="ar" dir="rtl"
                                           value="{{ old('end_date') }}">
                                    @error('end_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="job_type_id">Job Type / الوظيفة</label>
                                    <select class="form-control @error('job_type_id') is-invalid @enderror" 
                                            id="job_type_id" name="job_type_id">
                                        <option value="">Select Job Type</option>
                                        @foreach($jobtypes ?? [] as $jobType)
                                            <option value="{{ $jobType->id }}" {{ old('job_type_id') == $jobType->id ? 'selected' : '' }}>
                                                {{ $jobType->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('job_type_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="salary">Salary / الراتب</label>
                                    <input type="number" step="50" class="form-control @error('salary') is-invalid @enderror" 
                                           id="salary" name="salary" 
                                           value="{{ old('salary', 300) }}" 
                                           placeholder="Enter salary">
                                    @error('salary')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="d-block">Options / الخيارات</label>
                                    <div class="form-check form-check-inline">
                                        
                                        <label class="form-check-label" for="has_housing">
                                            Has Housing / متوفر له سكن
                                            <input class="form-check-input" type="checkbox" id="has_housing" 
                                               name="has_housing" value="1" {{ old('has_housing') ? 'checked' : '' }}>
                                            <span class="form-check-sign"></span>
                                            
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label" for="has_training_course">
                                            Training Course / دورة تدريبية
                                            <input class="form-check-input" type="checkbox" id="has_training_course"
                                               name="has_training_course" value="1" {{ old('has_training_course') ? 'checked' : '' }}>
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        
                                        <label class="form-check-label" for="is_local_community">
                                            Local Community / من المجتمع المحلي
                                            <input class="form-check-input" type="checkbox" id="is_local_community" 
                                               name="is_local_community" value="1" {{ old('is_local_community') ? 'checked' : '' }}>
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        
                                        <label class="form-check-label" for="is_on_company_payroll">
                                            On Company Payroll / على قوة الشركة
                                            <input class="form-check-input" type="checkbox" id="is_on_company_payroll" 
                                               name="is_on_company_payroll" value="1" {{ old('is_on_company_payroll', true) ? 'checked' : '' }}>
                                               <span class="form-check-sign"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2"></div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="tim-icons icon-check-2"></i> {{ __('حفظ العامل') }}
                            </button>
                            <a href="{{ route('workers.index') }}" class="btn btn-secondary">
                                <i class="tim-icons icon-simple-remove"></i> {{ __('الغاء') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection