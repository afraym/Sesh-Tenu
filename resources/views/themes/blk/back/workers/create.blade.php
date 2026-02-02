@extends('themes.blk.back.app')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Add New Worker</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('workers.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="project_name">Project Name / إسم المشروع</label>
                                    <input type="text" class="form-control @error('project_name') is-invalid @enderror" 
                                           id="project_name" name="project_name" 
                                           value="{{ old('project_name') }}" 
                                           placeholder="Enter project name">
                                    @error('project_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company_name">Company Name / إسم الشركة</label>
                                    <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                           id="company_name" name="company_name" 
                                           value="{{ old('company_name') }}" 
                                           placeholder="Enter company name">
                                    @error('company_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="worker_name">Worker Name / إسم العامل</label>
                                    <input type="text" class="form-control @error('worker_name') is-invalid @enderror" 
                                           id="worker_name" name="worker_name" 
                                           value="{{ old('worker_name') }}" 
                                           placeholder="Enter worker name">
                                    @error('worker_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mobile_number">Mobile Number / موبايل</label>
                                    <input type="text" class="form-control @error('mobile_number') is-invalid @enderror" 
                                           id="mobile_number" name="mobile_number" 
                                           value="{{ old('mobile_number') }}" 
                                           placeholder="Enter mobile number">
                                    @error('mobile_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_number">ID Number / رقم البطاقة</label>
                                    <input type="text" class="form-control @error('id_number') is-invalid @enderror" 
                                           id="id_number" name="id_number" 
                                           value="{{ old('id_number') }}" 
                                           placeholder="Enter ID number">
                                    @error('id_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="worker_job">Worker Job / وظيفة العامل</label>
                                    <input type="text" class="form-control @error('worker_job') is-invalid @enderror" 
                                           id="worker_job" name="worker_job" 
                                           value="{{ old('worker_job') }}" 
                                           placeholder="Enter worker job">
                                    @error('worker_job')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="access_code">Access Code / كود الدخول</label>
                                    <input type="text" class="form-control @error('access_code') is-invalid @enderror" 
                                           id="access_code" name="access_code" 
                                           value="{{ old('access_code') }}" 
                                           placeholder="Enter access code">
                                    @error('access_code')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="tim-icons icon-check-2"></i> Save Worker
                            </button>
                            <a href="{{ route('workers.index') }}" class="btn btn-secondary">
                                <i class="tim-icons icon-simple-remove"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection