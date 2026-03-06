@extends('layouts.back')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">تعديل تسليم السيركي</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('worker-document-deliveries.update', $delivery->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="worker_id">العامل <span class="text-danger">*</span></label>
                                    <select class="form-control @error('worker_id') is-invalid @enderror" id="worker_id" name="worker_id" required>
                                        <option value="">-- اختر عامل --</option>
                                        @foreach($workers ?? [] as $worker)
                                            <option value="{{ $worker->id }}" {{ old('worker_id', $delivery->worker_id) == $worker->id ? 'selected' : '' }}>
                                                {{ $worker->name }} - {{ $worker->national_id }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('worker_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="year">السنة <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('year') is-invalid @enderror" id="year" name="year" 
                                           value="{{ old('year', $delivery->year) }}" min="2000" max="2100" required>
                                    @error('year')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="month">الشهر <span class="text-danger">*</span></label>
                                    <select class="form-control @error('month') is-invalid @enderror" id="month" name="month" required>
                                        <option value="">-- اختر --</option>
                                        <option value="1" {{ old('month', $delivery->month) == 1 ? 'selected' : '' }}>يناير</option>
                                        <option value="2" {{ old('month', $delivery->month) == 2 ? 'selected' : '' }}>فبراير</option>
                                        <option value="3" {{ old('month', $delivery->month) == 3 ? 'selected' : '' }}>مارس</option>
                                        <option value="4" {{ old('month', $delivery->month) == 4 ? 'selected' : '' }}>أبريل</option>
                                        <option value="5" {{ old('month', $delivery->month) == 5 ? 'selected' : '' }}>مايو</option>
                                        <option value="6" {{ old('month', $delivery->month) == 6 ? 'selected' : '' }}>يونيو</option>
                                        <option value="7" {{ old('month', $delivery->month) == 7 ? 'selected' : '' }}>يوليو</option>
                                        <option value="8" {{ old('month', $delivery->month) == 8 ? 'selected' : '' }}>أغسطس</option>
                                        <option value="9" {{ old('month', $delivery->month) == 9 ? 'selected' : '' }}>سبتمبر</option>
                                        <option value="10" {{ old('month', $delivery->month) == 10 ? 'selected' : '' }}>أكتوبر</option>
                                        <option value="11" {{ old('month', $delivery->month) == 11 ? 'selected' : '' }}>نوفمبر</option>
                                        <option value="12" {{ old('month', $delivery->month) == 12 ? 'selected' : '' }}>ديسمبر</option>
                                    </select>
                                    @error('month')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="shift">الفترة <span class="text-danger">*</span></label>
                                    <select class="form-control @error('shift') is-invalid @enderror" id="shift" name="shift" required>
                                        <option value="">-- اختر --</option>
                                        <option value="morning" {{ old('shift', $delivery->shift) == 'morning' ? 'selected' : '' }}>صباحية</option>
                                        <option value="night" {{ in_array(old('shift', $delivery->shift), ['night', 'evening'], true) ? 'selected' : '' }}>ليلية</option>
                                        <option value="both" {{ in_array(old('shift', $delivery->shift), ['both', 'mixed'], true) ? 'selected' : '' }}>كلاهما</option>
                                    </select>
                                    @error('shift')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="morning_delivery_date">تاريخ التسليم الصباحية</label>
                                    <input type="date" class="form-control @error('morning_delivery_date') is-invalid @enderror" 
                                           id="morning_delivery_date" name="morning_delivery_date" lang="ar" dir="rtl"
                                           value="{{ old('morning_delivery_date', optional($delivery->morning_delivery_date)->format('Y-m-d')) }}">
                                    @error('morning_delivery_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="evening_delivery_date">تاريخ التسليم المسائية</label>
                                    <input type="date" class="form-control @error('evening_delivery_date') is-invalid @enderror" 
                                           id="evening_delivery_date" name="evening_delivery_date" lang="ar" dir="rtl"
                                           value="{{ old('evening_delivery_date', optional($delivery->evening_delivery_date)->format('Y-m-d')) }}">
                                    @error('evening_delivery_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">ملاحظات</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="3" placeholder="أدخل أي ملاحظات">{{ old('notes', $delivery->notes) }}</textarea>
                                    @error('notes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="tim-icons icon-single-copy-04"></i> تحديث
                            </button>
                            <a href="{{ route('worker-document-deliveries.index') }}" class="btn btn-secondary">
                                <i class="tim-icons icon-simple-remove"></i> إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
