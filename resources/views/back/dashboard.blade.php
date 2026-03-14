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
			'title' => 'افراد امن',
			'subtitle' => 'عرض عمال أفراد الأمن',
			'icon' => 'fa-solid fa-shield-halved',
			'icon_color' => '#3987f6',
			'icon_bg' => 'rgba(57, 135, 246, 0.18)',
			'url' => url('admin/workers') . '?job_type_id=4&sort=created_at&direction=desc',
			'action' => 'عرض افراد الامن',
			'count' => 0,
		],
		[
			'title' => 'مشغل مُعدة',
			'subtitle' => 'عرض عمال مشغلي المعدات',
			'icon' => 'fa-solid fa-tractor',
			'icon_color' => '#14b8a6',
			'icon_bg' => 'rgba(20, 184, 166, 0.18)',
			'url' => url('admin/workers') . '?job_type_id=equipment_operator&sort=created_at&direction=desc',
			'action' => 'عرض مشغلي المعدات',
			'count' => 0,
		],
		[
			'title' => 'فحص يومي',
			'subtitle' => 'الفحص اليومي للعمال',
			'icon' => 'fa-solid fa-clipboard-check',
			'icon_color' => '#22c55e',
			'icon_bg' => 'rgba(34, 197, 94, 0.18)',
			'url' => url('admin/equipment'),
			'action' => 'فحص يومي',
			'count' => 0,
		],
		[
			'title' => 'اخلاء طرف',
			'subtitle' => 'إجراءات إخلاء طرف العمال',
			'icon' => 'fa-solid fa-file-circle-check',
			'icon_color' => '#f59e0b',
			'icon_bg' => 'rgba(245, 158, 11, 0.18)',
			'url' => url('admin/workers') . '?sort=created_at&direction=desc',
			'action' => 'اخلاء طرف',
			'count' => 0,
		],
		[
			'title' => 'ادخال جديد',
			'subtitle' => 'إضافة عامل جديد للنظام',
			'icon' => 'fa-solid fa-user-plus',
			'icon_color' => '#00b8d9',
			'icon_bg' => 'rgba(0, 184, 217, 0.18)',
			'route' => 'workers.create',
			'action' => 'إضافة عامل',
			'count' => 0,
		],
		[
			'title' => 'العمال',
			'subtitle' => 'إدارة بيانات العمال والتعديل والبحث',
			'icon' => 'fa-solid fa-person-digging',
			'icon_color' => '#3f8cff',
			'icon_bg' => 'rgba(63, 140, 255, 0.18)',
			'url' => url('admin/workers') . '?job_type_id=1&sort=created_at&direction=desc',
			'action' => 'عرض العمال',
			'count' => $counts['workers'],
		],
		[
			'title' => 'متابعة السركي',
			'subtitle' => 'إجمالي التسليمات',
			'icon' => 'far fa-calendar-check',
			'icon_color' => '#18b67f',
			'icon_bg' => 'rgba(24, 182, 127, 0.18)',
			'route' => 'worker-document-deliveries.index',
			'action' => 'فتح المتابعة',
			'count' => $counts['deliveries'],
		],
		[
			'title' => 'استلام سركي',
			'subtitle' => 'استلامات اليوم',
			'icon' => 'fas fa-user-check',
			'icon_color' => '#f2994a',
			'icon_bg' => 'rgba(242, 153, 74, 0.18)',
			'route' => 'worker-document-deliveries.receive',
			'action' => 'فتح الاستلام',
			'count' => $counts['today_receipts'],
		],
		[
			'title' => 'المُعدات',
			'subtitle' => 'إدارة المعدات وحالتها التشغيلية',
			'icon' => 'tim-icons icon-delivery-fast',
			'icon_color' => '#00bcd4',
			'icon_bg' => 'rgba(0, 188, 212, 0.18)',
			'route' => 'equipment.index',
			'action' => 'عرض المعدات',
			'count' => $counts['equipment'],
		],
		[
			'title' => 'شهادة معايرة',
			'subtitle' => 'متابعة شهادات معايرة المعدات',
			'icon' => 'fa-solid fa-certificate',
			'icon_color' => '#f59e0b',
			'icon_bg' => 'rgba(245, 158, 11, 0.18)',
			'url' => url('admin/equipment') . '?sort=created_at&direction=desc',
			'action' => 'شهادة معايرة',
			'count' => 0,
		],
		[
			'title' => 'تصريح دخول وخروج',
			'subtitle' => 'إدارة تصاريح دخول وخروج المعدات',
			'icon' => 'fa-solid fa-right-left',
			'icon_color' => '#22c55e',
			'icon_bg' => 'rgba(34, 197, 94, 0.18)',
			'url' => url('admin/equipment') . '?sort=created_at&direction=desc',
			'action' => 'تصريح دخول وخروج',
			'count' => 0,
		],
		// [
		// 	'title' => 'أنواع المعدات',
		// 	'subtitle' => 'تصنيف المعدات حسب النوع',
		// 	'icon' => 'fa-solid fa-tractor',
		// 	'icon_color' => '#8b6df0',
		// 	'icon_bg' => 'rgba(139, 109, 240, 0.18)',
		// 	'route' => 'equipment-types.index',
		// 	'action' => 'عرض الأنواع',
		// 	'count' => $counts['equipment_types'],
		// ],
	];

	if (auth()->check() && auth()->user()->isSuperAdmin()) {
		$modules = array_merge($modules, [
			// [
			// 	'title' => 'المشاريع',
			// 	'subtitle' => 'إدارة المشاريع وربطها بالشركات',
			// 	'icon' => 'tim-icons icon-chart-pie-36',
			// 	'icon_color' => '#ff5b7f',
			// 	'icon_bg' => 'rgba(255, 91, 127, 0.18)',
			// 	'route' => 'projects.index',
			// 	'action' => 'عرض المشاريع',
			// 	'count' => $counts['projects'],
			// ],
			// [
			// 	'title' => 'الشركات',
			// 	'subtitle' => 'إدارة الشركات والبيانات الأساسية',
			// 	'icon' => 'tim-icons icon-bank',
			// 	'icon_color' => '#14b8a6',
			// 	'icon_bg' => 'rgba(20, 184, 166, 0.18)',
			// 	'route' => 'companies.index',
			// 	'action' => 'عرض الشركات',
			// 	'count' => $counts['companies'],
			// ],
			// [
			// 	'title' => 'أنواع الوظائف',
			// 	'subtitle' => 'ضبط قائمة الوظائف المتاحة',
			// 	'icon' => 'tim-icons icon-bullet-list-67',
			// 	'icon_color' => '#6366f1',
			// 	'icon_bg' => 'rgba(99, 102, 241, 0.18)',
			// 	'route' => 'jobtypes.index',
			// 	'action' => 'عرض الوظائف',
			// 	'count' => $counts['jobtypes'],
			// ],
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

	// Reorder buttons by frequent workflow usage (manual priority map).
	$usagePriority = [
		'المُعدات' => 300,
		'العمال' => 299,
		'افراد امن' => 298,
		'ادخال جديد' => 97,
		'استلام سركي' => 95,
		'متابعة السركي' => 94,
		'فحص يومي' => 85,
		'شهادة معايرة' => 84,
		'تصريح دخول وخروج' => 83,
		'مشغل مُعدة' => 96,
		'اخلاء طرف' => 65,
		'أنواع المعدات' => 60,
		'الشركات' => 50,
		'المشاريع' => 45,
		'أنواع الوظائف' => 40,
		'المستخدمين' => 35,
	];

	usort($modules, function (array $a, array $b) use ($usagePriority) {
		$priorityA = (int) ($usagePriority[$a['title'] ?? ''] ?? 0);
		$priorityB = (int) ($usagePriority[$b['title'] ?? ''] ?? 0);

		if ($priorityA === $priorityB) {
			return strcmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''));
		}

		return $priorityB <=> $priorityA;
	});
@endphp

<div class="content">
	<div class="row">
		<div class="col-12">
			<div class="card dashboard-modules-card">
				<div class="card-header  align-items-center text-center">
					 <a class="navbar-brand">
            @if(auth()->check() && auth()->user()->company)
              <img src="{{ asset(auth()->user()->company->logo)  }}" alt="{{ auth()->user()->company->name }}" class="company-logo" style="width: 90px;height: 90px;">
            @endif
          </a>
					<h4 class="card-title mb-1">المنصات الرئيسية</h4>
					<p class="card-category mb-0">قائمة وحدات النظام الرئيسية للوصول السريع</p>
				</div>
				<div class="card-body pt-3">
					<div class="dashboard-button-grid">
						@foreach($modules as $module)
							<a href="{{ isset($module['url']) ? $module['url'] : route($module['route']) }}" class="dashboard-glass-btn" title="{{ $module['subtitle'] }}">
								<i class="{{ $module['icon'] }} dashboard-glass-btn__icon"></i>
								<span class="dashboard-glass-btn__label">{{ $module['title'] }}</span>
							</a>
						@endforeach
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	.dashboard-modules-card {
		border-radius: 18px;
	}

	.dashboard-button-grid {
		display: grid;
		grid-template-columns: repeat(4, minmax(130px, 1fr));
		gap: 16px 14px;
		padding: 8px 0 2px;
	}

	.dashboard-glass-btn {
		position: relative;
		display: inline-flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		gap: 6px;
		width: 100%;
		max-width: 170px;
		margin: 0 auto;
		min-height: 72px;
		border-radius: 12px;
		padding: 12px 14px 10px;
		text-align: center;
		text-decoration: none;
		color: #e8f0ff;
		background: linear-gradient(180deg, #6aaaff 0%, #3987f6 50%, #1f6de0 100%);
		border: 3px solid #1a69d6;
		box-shadow: inset 0 2px 0 rgba(255, 255, 255, 0.55), inset 0 -3px 0 rgba(15, 55, 130, 0.45), 0 5px 12px rgba(25, 90, 200, 0.24);
		transition: transform 0.18s ease, filter 0.18s ease, box-shadow 0.18s ease;
	}

	.dashboard-glass-btn__icon {
		position: relative;
		z-index: 1;
		font-size: 1.5rem;
		line-height: 1;
		color: rgba(255, 255, 255, 0.92);
		text-shadow: 0 2px 4px rgba(15, 50, 130, 0.45);
	}

	.dashboard-glass-btn::before {
		content: '';
		position: absolute;
		top: 8px;
		left: 14px;
		right: 14px;
		height: 28%;
		border-radius: 10px;
		background: linear-gradient(180deg, rgba(255, 255, 255, 0.32), rgba(255, 255, 255, 0.03));
		pointer-events: none;
	}

	.dashboard-glass-btn:hover,
	.dashboard-glass-btn:focus {
		text-decoration: none;
		color: #ffffff;
		filter: brightness(1.04);
		transform: translateY(-2px);
		box-shadow: inset 0 2px 0 rgba(255, 255, 255, 0.65), inset 0 -3px 0 rgba(15, 55, 130, 0.42), 0 10px 16px rgba(25, 90, 200, 0.28);
	}

	.dashboard-glass-btn:active {
		transform: translateY(1px);
	}

	.dashboard-glass-btn__label {
		position: relative;
		z-index: 1;
		font-size: clamp(16px, 1.35vw, 24px);
		font-weight: 700;
		line-height: 1.15;
		letter-spacing: 0;
		text-shadow: 0 2px 0 rgba(15, 50, 130, 0.55);
	}

	body.white-content .dashboard-modules-card {
		background: #ffffff;
	}

	@media (max-width: 1199px) {
		.dashboard-button-grid {
			grid-template-columns: repeat(3, minmax(120px, 1fr));
		}
	}

	@media (max-width: 991px) {
		.dashboard-button-grid {
			grid-template-columns: repeat(2, minmax(120px, 1fr));
			gap: 12px;
		}

		.dashboard-glass-btn {
			min-height: 64px;
		}

		.dashboard-glass-btn__icon {
			font-size: 1.3rem;
		}

		.dashboard-glass-btn__label {
			font-size: clamp(15px, 3.2vw, 20px);
		}
	}

	@media (max-width: 575px) {
		.dashboard-button-grid {
			grid-template-columns: 1fr;
		}

		.dashboard-glass-btn__label {
			font-size: clamp(14px, 6vw, 18px);
		}
	}
</style>
@endsection
