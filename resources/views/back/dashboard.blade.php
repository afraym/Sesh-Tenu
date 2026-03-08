@extends('layouts.back')

@section('content')
@php
	$counts = [
		'workers' => \App\Models\Worker::count(),
		'equipment' => \App\Models\Equipment::count(),
		'equipment_types' => \App\Models\EquipmentType::count(),
		'deliveries' => \App\Models\WorkerDocumentDelivery::count(),
		'today_receipts' => \App\Models\WorkerDocumentDelivery::whereDate('created_at', now()->toDateString())->count(),
		'companies' => \App\Models\Company::count(),
		'projects' => \App\Models\Project::count(),
		'jobtypes' => \App\Models\JobType::count(),
		'users' => \App\Models\User::count(),
	];

	$modules = [
		[
			'title' => 'العمال',
			'subtitle' => 'إدارة بيانات العمال والتعديل والبحث',
			'icon' => 'fa-solid fa-person-digging',
			'icon_color' => '#3f8cff',
			'icon_bg' => 'rgba(63, 140, 255, 0.18)',
			'route' => 'workers.index',
			'action' => 'عرض العمال',
			'count' => $counts['workers'],
		],
		[
			'title' => 'متابعة السيركي',
			'subtitle' => 'إجمالي التسليمات',
			'icon' => 'far fa-calendar-check',
			'icon_color' => '#18b67f',
			'icon_bg' => 'rgba(24, 182, 127, 0.18)',
			'route' => 'worker-document-deliveries.index',
			'action' => 'فتح المتابعة',
			'count' => $counts['deliveries'],
		],
		[
			'title' => 'استلام سيركي',
			'subtitle' => 'استلامات اليوم',
			'icon' => 'fas fa-user-check',
			'icon_color' => '#f2994a',
			'icon_bg' => 'rgba(242, 153, 74, 0.18)',
			'route' => 'worker-document-deliveries.receive',
			'action' => 'فتح الاستلام',
			'count' => $counts['today_receipts'],
		],
		[
			'title' => 'المعدات',
			'subtitle' => 'إدارة المعدات وحالتها التشغيلية',
			'icon' => 'tim-icons icon-delivery-fast',
			'icon_color' => '#00bcd4',
			'icon_bg' => 'rgba(0, 188, 212, 0.18)',
			'route' => 'equipment.index',
			'action' => 'عرض المعدات',
			'count' => $counts['equipment'],
		],
		[
			'title' => 'أنواع المعدات',
			'subtitle' => 'تصنيف المعدات حسب النوع',
			'icon' => 'fa-solid fa-tractor',
			'icon_color' => '#8b6df0',
			'icon_bg' => 'rgba(139, 109, 240, 0.18)',
			'route' => 'equipment-types.index',
			'action' => 'عرض الأنواع',
			'count' => $counts['equipment_types'],
		],
	];

	if (auth()->check() && auth()->user()->isSuperAdmin()) {
		$modules = array_merge($modules, [
			[
				'title' => 'المشاريع',
				'subtitle' => 'إدارة المشاريع وربطها بالشركات',
				'icon' => 'tim-icons icon-chart-pie-36',
				'icon_color' => '#ff5b7f',
				'icon_bg' => 'rgba(255, 91, 127, 0.18)',
				'route' => 'projects.index',
				'action' => 'عرض المشاريع',
				'count' => $counts['projects'],
			],
			[
				'title' => 'الشركات',
				'subtitle' => 'إدارة الشركات والبيانات الأساسية',
				'icon' => 'tim-icons icon-bank',
				'icon_color' => '#14b8a6',
				'icon_bg' => 'rgba(20, 184, 166, 0.18)',
				'route' => 'companies.index',
				'action' => 'عرض الشركات',
				'count' => $counts['companies'],
			],
			[
				'title' => 'أنواع الوظائف',
				'subtitle' => 'ضبط قائمة الوظائف المتاحة',
				'icon' => 'tim-icons icon-bullet-list-67',
				'icon_color' => '#6366f1',
				'icon_bg' => 'rgba(99, 102, 241, 0.18)',
				'route' => 'jobtypes.index',
				'action' => 'عرض الوظائف',
				'count' => $counts['jobtypes'],
			],
			[
				'title' => 'المستخدمين',
				'subtitle' => 'إدارة حسابات وصلاحيات المستخدمين',
				'icon' => 'tim-icons icon-single-02',
				'icon_color' => '#ef4444',
				'icon_bg' => 'rgba(239, 68, 68, 0.18)',
				'route' => 'users.index',
				'action' => 'عرض المستخدمين',
				'count' => $counts['users'],
			],
		]);
	}
@endphp

<div class="content">
	<div class="row">
		<div class="col-12">
			<div class="card dashboard-modules-card">
				<div class="card-header">
					<h4 class="card-title mb-1">المنصات الرئيسية</h4>
					<p class="card-category mb-0">قائمة وحدات النظام الرئيسية للوصول السريع</p>
				</div>
				<div class="card-body pt-2">
					<div class="row dashboard-module-grid">
						@foreach($modules as $module)
							<div class="col-12 col-md-6 col-xl-4 d-flex">
								<div class="module-box w-100 d-flex flex-column justify-content-between">
									<div class="d-flex align-items-center justify-content-between mb-3">
										<span class="module-count-badge">{{ number_format($module['count'] ?? 0) }}</span>
									</div>
									<div class="text-center mb-3">
										<div class="module-icon-wrap mx-auto" style="--module-icon-color: {{ $module['icon_color'] ?? '#1d8cf8' }}; --module-icon-bg: {{ $module['icon_bg'] ?? 'rgba(29, 140, 248, 0.18)' }};">
											<i class="{{ $module['icon'] }}"></i>
										</div>
									</div>
									<h5 class="module-title mb-2 text-center">{{ $module['title'] }}</h5>
									<p class="module-subtitle mb-3 text-center">{{ $module['subtitle'] }}</p>
									<div class="text-center">
										<a href="{{ route($module['route']) }}" class="btn btn-info btn-sm mb-0">
											{{ $module['action'] }}
										</a>
									</div>
								</div>
							</div>
						@endforeach
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	.dashboard-modules-card {
		border-radius: 14px;
	}

	.dashboard-module-grid {
		margin-top: 0;
	}

	.module-box {
		background: rgba(255, 255, 255, 0.02);
		border: 1px solid rgba(255, 255, 255, 0.08);
		border-radius: 10px;
		padding: 16px;
		margin-bottom: 16px;
		min-height: 180px;
	}

	.module-box:hover {
		border-color: rgba(29, 140, 248, 0.45);
		background: rgba(29, 140, 248, 0.08);
	}

	.module-title {
		color: #ffffff;
	}

	.module-subtitle {
		color: rgba(255, 255, 255, 0.7);
	}

	.module-count-badge {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 44px;
		height: 28px;
		padding: 0 10px;
		border-radius: 999px;
		background: rgba(29, 140, 248, 0.2);
		border: 1px solid rgba(29, 140, 248, 0.45);
		color: #7fc0ff;
		font-weight: 700;
		font-size: 12px;
	}

	.module-icon-wrap {
		width: 72px;
		height: 72px;
		border-radius: 16px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: var(--module-icon-bg, rgba(29, 140, 248, 0.18));
		color: var(--module-icon-color, #1d8cf8);
		font-size: 30px;
	}

	.module-icon-wrap i {
		line-height: 1;
	}

	body.white-content .module-box {
		background: #ffffff;
		border-color: #dbe4f0;
	}

	body.white-content .module-box:hover {
		background: #f3f8ff;
		border-color: #9fc7f4;
	}

	body.white-content .module-title {
		color: #2b3553;
	}

	body.white-content .module-subtitle {
		color: #5f6b8a;
	}

	body.white-content .module-count-badge {
		background: #e8f2ff;
		border-color: #b5d4fa;
		color: #1d5ea8;
	}

	@media (max-width: 767px) {
		.module-box {
			min-height: auto;
			margin-bottom: 12px;
		}

		.module-icon-wrap {
			width: 62px;
			height: 62px;
			font-size: 26px;
		}
	}
</style>
@endsection
