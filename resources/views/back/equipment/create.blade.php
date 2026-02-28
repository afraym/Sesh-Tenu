@extends('layouts.back')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Add Equipment / إضافة معدة</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('equipment.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="project_name">اسم المشروع / Project Name <span class="text-danger">*</span></label>
                                        <select name="project_id" id="project_id" class="form-control" required>
                                        <option value="">-- اختار مشروع --</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" {{ (string)$selectedProjectId === (string)$project->id ? 'selected' : '' }}>
                                                {{ $project->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company_id">اسم الشركة / Company <span class="text-danger">*</span></label>
                                       <select class="form-control @error('company_id') is-invalid @enderror" 
                                            id="company_id" name="company_id" required>
                                        <option value="">اختر شركة</option>
                                        
                                            
                                        @if( !auth()->user()->isSuperAdmin())
                                            <option value="{{ auth()->user()->company_id }}" selected>
                                                {{ auth()->user()->company->name }}
                                            </option>
                                            @else
                                            @foreach($companies ?? [] as $company)
                                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                    {{ $company->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="previous_driver">اسم السائق السابق / Previous Driver</label>
                                    <input type="text" class="form-control" id="previous_driver" name="previous_driver">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="current_driver">اسم السائق الحالي / Current Driver</label>
                                    <input type="text" class="form-control" id="current_driver" name="current_driver">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="equipment_type">نوع المعدة / Equipment Type <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="equipment_type" name="equipment_type" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="model_year">موديل المعدة / Model Year</label>
                                    <input type="text" class="form-control" id="model_year" name="model_year">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="equipment_code">كود المعدة / Equipment Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="equipment_code" name="equipment_code" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="equipment_number">رقم شاسية المعدة / Equipment Number</label>
                                    <input type="text" class="form-control" id="equipment_number" name="equipment_number">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="manufacture">المصنع / Manufacture</label>
                                    <input type="text" class="form-control" id="manufacture" name="manufacture">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="entry_per_ser">تصريح الدخول / Entry Per. Ser.</label>
                                    <input type="text" class="form-control" id="entry_per_ser" name="entry_per_ser">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reg_no">رقم التسجيل / Reg. No.</label>
                                    <input type="text" class="form-control" id="reg_no" name="reg_no">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="equip_reg_issue">رقم رخصة المعدة / Equip. Reg. Issue</label>
                                    <input type="text" class="form-control" id="equip_reg_issue" name="equip_reg_issue">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="custom_clearance">الافراج الجمركي / Custom Clearance</label>
                                    <input type="text" class="form-control" id="custom_clearance" name="custom_clearance">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary">Save Equipment</button>
                            <a href="{{ route('equipment.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

