@extends('themes.blk.back.app')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Equipment Details / تفاصيل المعدة</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr><th>اسم المشروع</th><td>{{ $equipment->project_name }}</td></tr>
                            <tr><th>اسم الشركة</th><td>{{ optional($equipment->company)->name ?? 'غير متوفر' }}</td></tr>
                            <tr><th>اسم السائق السابق</th><td>{{ $equipment->previous_driver ?? 'غير متوفر' }}</td></tr>
                            <tr><th>اسم السائق الحالي</th><td>{{ $equipment->current_driver ?? 'غير متوفر' }}</td></tr>
                            <tr><th>نوع المعدة</th><td>{{ $equipment->equipment_type }}</td></tr>
                            <tr><th>موديل المعدة</th><td>{{ $equipment->model_year ?? 'غير متوفر' }}</td></tr>
                            <tr><th>كود المعدة</th><td>{{ $equipment->equipment_code }}</td></tr>
                            <tr><th>رقم شاسية المعدة</th><td>{{ $equipment->equipment_number ?? 'غير متوفر' }}</td></tr>
                            <tr><th>المصنع</th><td>{{ $equipment->manufacture ?? 'غير متوفر' }}</td></tr>
                            <tr><th>تصريح الدخول</th><td>{{ $equipment->entry_per_ser ?? 'غير متوفر' }}</td></tr>
                            <tr><th>رقم التسجيل</th><td>{{ $equipment->reg_no ?? 'غير متوفر' }}</td></tr>
                            <tr><th>رقم رخصة المعدة</th><td>{{ $equipment->equip_reg_issue ?? 'غير متوفر' }}</td></tr>
                            <tr><th>الافراج الجمركي</th><td>{{ $equipment->custom_clearance ?? 'غير متوفر' }}</td></tr>
                        </tbody>
                    </table>
                    <a href="{{ route('equipment.index') }}" class="btn btn-secondary mt-3">رجوع</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
