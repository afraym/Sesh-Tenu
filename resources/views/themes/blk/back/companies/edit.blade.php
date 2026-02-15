@extends('themes.blk.back.app')
@section('content')
<div class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<h4 class="card-title">Edit Company / تعديل الشركة</h4>
				</div>
				<div class="card-body">
					<form action="{{ route('companies.update', $company->id) }}" method="POST" enctype="multipart/form-data">
						@csrf
						@method('PUT')

						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="name">Company Name / إسم الشركة <span class="text-danger">*</span></label>
									<input type="text" class="form-control @error('name') is-invalid @enderror"
										   id="name" name="name"
										   value="{{ old('name', $company->name) }}"
										   placeholder="Enter company name" required>
									@error('name')
										<span class="invalid-feedback">{{ $message }}</span>
									@enderror
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-group">
									<label for="email">Email</label>
									<input type="email" class="form-control @error('email') is-invalid @enderror"
										   id="email" name="email"
										   value="{{ old('email', $company->email) }}"
										   placeholder="Enter company email">
									@error('email')
										<span class="invalid-feedback">{{ $message }}</span>
									@enderror
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="phone_number">Phone Number / رقم الهاتف</label>
									<input type="text" class="form-control @error('phone_number') is-invalid @enderror"
										   id="phone_number" name="phone_number"
										   value="{{ old('phone_number', $company->phone_number ?? $company->phone) }}"
										   placeholder="Enter phone number">
									@error('phone_number')
										<span class="invalid-feedback">{{ $message }}</span>
									@enderror
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-group">
									<label for="logo">Company Logo / شعار الشركة</label>
									<input type="file" class="form-control @error('logo') is-invalid @enderror"
										   id="logo" name="logo" accept="image/*">
									<small class="form-text text-muted">Accepted formats: JPG, PNG, GIF, SVG (Max: 2MB)</small>
									@error('logo')
										<span class="invalid-feedback d-block">{{ $message }}</span>
									@enderror
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label for="address">Address / العنوان</label>
									<textarea class="form-control @error('address') is-invalid @enderror"
											  id="address" name="address"
											  rows="3"
											  placeholder="Enter company address">{{ old('address', $company->address) }}</textarea>
									@error('address')
										<span class="invalid-feedback">{{ $message }}</span>
									@enderror
								</div>
							</div>
						</div>

						@if($company->logo)
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label>Current Logo</label>
										<div>
											<img src="{{ asset($company->logo) }}" alt="Company Logo" style="max-height: 80px;" class="img-fluid rounded">
										</div>
									</div>
								</div>
							</div>
						@endif

						<div class="form-group mt-3">
							<button type="submit" class="btn btn-primary">
								<i class="tim-icons icon-refresh-02"></i> Update Company
							</button>
							<a href="{{ route('companies.index') }}" class="btn btn-secondary">
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
