@extends('layouts.back')
@section('content')
<div class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<h4 class="card-title">Add New Job Type / إضافة نوع وظيفة جديد</h4>
				</div>
				<div class="card-body">
					<form action="{{ route('jobtypes.store') }}" method="POST">
						@csrf

						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="name">Job Type Name / اسم الوظيفة <span class="text-danger">*</span></label>
									<input type="text" class="form-control @error('name') is-invalid @enderror"
										   id="name" name="name"
										   value="{{ old('name') }}"
										   placeholder="Enter job type name" required>
									@error('name')
										<span class="invalid-feedback">{{ $message }}</span>
									@enderror
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-group">
									<label class="d-block">Status / الحالة</label>
									<div class="form-check form-check-inline">
										<label class="form-check-label" for="is_active">
											Active / نشط
											<input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
											<span class="form-check-sign"></span>
										</label>
									</div>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label for="description">Description / الوصف</label>
									<textarea class="form-control @error('description') is-invalid @enderror"
											  id="description" name="description"
											  rows="4"
											  placeholder="Enter description">{{ old('description') }}</textarea>
									@error('description')
										<span class="invalid-feedback">{{ $message }}</span>
									@enderror
								</div>
							</div>
						</div>

						<div class="form-group mt-3">
							<button type="submit" class="btn btn-primary">
								<i class="tim-icons icon-check-2"></i> Save Job Type
							</button>
							<a href="{{ route('jobtypes.index') }}" class="btn btn-secondary">
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
