@extends('layouts.back')
@section('content')
<div class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<h4 class="card-title">تفاصيل العامل</h4>
				</div>
				<div class="card-body">
					<table class="table table-bordered">
						<tbody>
							<tr>
								<th>الشركة</th>
								<td>{{ $worker->company ? $worker->company->name : 'غير متوفر' }}</td>
							</tr>
							<tr>
								<th>الاسم</th>
								<td>{{ $worker->name }}</td>
							</tr>
							<tr>
								<th>الهيئة</th>
								<td>{{ $worker->entity ?? 'غير متوفر' }}</td>
							</tr>
							<tr>
								<th>نوع الوظيفة</th>
								<td>{{ $worker->jobType ? $worker->jobType->name : 'غير متوفر' }}</td>
							</tr>
							<tr>
								<th>الرقم القومي</th>
								<td>{{ $worker->national_id }}</td>
							</tr>
							<tr>
								<th>رقم الهاتف</th>
								<td>{{ $worker->phone_number }}</td>
							</tr>
							<tr>
								<th>متوفر له سكن</th>
								<td>{{ $worker->has_housing ? 'نعم' : 'لا' }}</td>
							</tr>
							<tr>
								<th>من المجتمع المحلي</th>
								<td>{{ $worker->is_local_community ? 'نعم' : 'لا' }}</td>
							</tr>
							<tr>
								<th>العنوان</th>
								<td>{{ $worker->address ?? 'غير متوفر' }}</td>
							</tr>
							<tr>
								<th>تاريخ الانضمام</th>
								<td>{{ $worker->join_date ? $worker->join_date->format('Y-m-d') : 'غير متوفر' }}</td>
							</tr>
							<tr>
								<th>تاريخ الانهاء</th>
								<td>{{ $worker->end_date ? $worker->end_date->format('Y-m-d') : 'غير متوفر' }}</td>
							</tr>
							<tr>
								<th>على قوة الشركة</th>
								<td>{{ $worker->is_on_company_payroll ? 'نعم' : 'لا' }}</td>
							</tr>
							<tr>
								<th>الراتب</th>
								<td>{{ $worker->salary ?? 'غير متوفر' }}</td>
							</tr>
						</tbody>
					</table>
					<a href="{{ route('workers.index') }}" class="btn btn-secondary mt-3">
						<i class="tim-icons icon-simple-remove"></i> رجوع
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
