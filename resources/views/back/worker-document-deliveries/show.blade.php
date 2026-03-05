@extends('layouts.back')
@section('content')
@php
    $monthNames = [
        1 => 'يناير',
        2 => 'فبراير',
        3 => 'مارس',
        4 => 'أبريل',
        5 => 'مايو',
        6 => 'يونيو',
        7 => 'يوليو',
        8 => 'أغسطس',
        9 => 'سبتمبر',
        10 => 'أكتوبر',
        11 => 'نوفمبر',
        12 => 'ديسمبر',
    ];
    $shiftNames = [
        'morning' => 'صباحية',
        'evening' => 'مسائية',
        'mixed' => 'مختلط',
    ];
@endphp
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">تفاصيل تسليم السيركي</h4>
                        </div>
                        <div class="col text-right">
                            <a href="{{ route('worker-document-deliveries.edit', $delivery->id) }}" class="btn btn-sm btn-warning">
                                <i class="tim-icons icon-pencil"></i> تعديل
                            </a>
                            <a href="{{ route('worker-document-deliveries.index') }}" class="btn btn-sm btn-secondary">
                                <i class="tim-icons icon-simple-remove"></i> عودة
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">العامل</label>
                                <p>
                                    <a href="{{ route('workers.show', $delivery->worker_id) }}">
                                        {{ $delivery->worker->name ?? '-' }}
                                    </a>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">الرقم القومي</label>
                                <p>{{ $delivery->worker->national_id ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">الشركة</label>
                                <p>{{ $delivery->worker->company->name ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">الوظيفة</label>
                                <p>{{ $delivery->worker->jobType->name ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">السنة</label>
                                <p>{{ $delivery->year }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">الشهر</label>
                                <p>{{ $monthNames[$delivery->month] ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">الفترة</label>
                                <p>{{ $shiftNames[$delivery->shift] ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">الحالة</label>
                                <p>
                                    @if($delivery->morning_delivery_date || $delivery->evening_delivery_date)
                                        <span class="badge badge-success">تم التسليم</span>
                                    @else
                                        <span class="badge badge-danger">لم يتم التسليم</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">تاريخ التسليم الصباحية</label>
                                @if($delivery->morning_delivery_date)
                                    <p><span class="badge badge-success">{{ $delivery->morning_delivery_date->format('m-d') }}</span></p>
                                @else
                                    <p><span class="badge badge-secondary">لم يتم التسليم</span></p>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">تاريخ التسليم المسائية</label>
                                @if($delivery->evening_delivery_date)
                                    <p><span class="badge badge-success">{{ $delivery->evening_delivery_date->format('m-d') }}</span></p>
                                @else
                                    <p><span class="badge badge-secondary">لم يتم التسليم</span></p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($delivery->notes)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold">ملاحظات</label>
                                <p>{{ $delivery->notes }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">أضيف بواسطة</label>
                                <p>{{ $delivery->creator->name ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">تاريخ الإضافة</label>
                                <p>{{ $delivery->created_at->format('Y-m-d H:i:s') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <form action="{{ route('worker-document-deliveries.destroy', $delivery->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا التسليم؟')">
                                <i class="tim-icons icon-simple-remove"></i> حذف
                            </button>
                        </form>
                        <a href="{{ route('worker-document-deliveries.index') }}" class="btn btn-secondary">
                            <i class="tim-icons icon-simple-remove"></i> عودة
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
