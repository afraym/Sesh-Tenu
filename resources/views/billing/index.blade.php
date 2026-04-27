@extends('layouts.back')

@section('content')
@php
    $companyName = $company?->name ?? __('billing.your_company');
    $status = $currentSubscription ? __('billing.status_active') : __('billing.status_inactive');
    $daysLeft = $currentSubscription && $currentSubscription->ends_at
        ? now()->diffInDays($currentSubscription->ends_at, false)
        : null;
@endphp

<div class="content">
    <div class="row">
        <div class="col-12 col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-2">{{ __('billing.subscription_status') }}</p>
                    <h3 class="mb-2">{{ $companyName }}</h3>
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge badge-{{ $currentSubscription && $currentSubscription->isActive() ? 'success' : 'danger' }} badge-pill px-3 py-2">
                            {{ $status }}
                        </span>
                    </div>

                    @if($currentSubscription && $currentSubscription->plan)
                        <p class="mb-1"><strong>{{ __('billing.plan') }}:</strong> {{ $currentSubscription->plan->name }}</p>
                        <p class="mb-1"><strong>{{ __('billing.started') }}:</strong> {{ optional($currentSubscription->starts_at)->format('Y-m-d') }}</p>
                        <p class="mb-1"><strong>{{ __('billing.expires') }}:</strong> {{ optional($currentSubscription->ends_at)->format('Y-m-d') }}</p>
                        <p class="mb-0"><strong>{{ __('billing.days_left') }}:</strong> {{ $daysLeft !== null ? max($daysLeft, 0) : __('billing.na') }}</p>
                    @else
                        <p class="mb-0">{{ __('billing.choose_plan_hint') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="mb-3">{{ __('billing.available_plans') }}</h4>
                    <div class="row">
                        @foreach($plans as $plan)
                            @php
                                $isCurrent = $currentSubscription && $currentSubscription->plan_id === $plan->id && $currentSubscription->isActive();
                            @endphp
                            <div class="col-12 col-md-4 mb-3">
                                <div class="border rounded p-3 h-100 d-flex flex-column">
                                    <div class="mb-3">
                                        <h5 class="mb-1">{{ $plan->name }}</h5>
                                        <p class="text-muted mb-2">{{ $plan->description }}</p>
                                        <div class="d-flex align-items-end">
                                            <span class="h3 mb-0">{{ number_format($plan->price, 2) }}</span>
                                            <span class="ml-2 text-muted">/ {{ $plan->billing_cycle_months }} {{ __('billing.month_unit') }}</span>
                                        </div>
                                    </div>

                                    @if(!empty($plan->features))
                                        <ul class="pl-3 mb-3 text-left">
                                            @foreach($plan->features as $feature)
                                                <li>{{ $feature }}</li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    <div class="mt-auto">
                                        @if($isCurrent)
                                            <button class="btn btn-success btn-block" disabled>{{ __('billing.current_plan') }}</button>
                                        @else
                                            <form method="POST" action="{{ route('billing.subscribe', $plan) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-info btn-block">{{ __('billing.subscribe') }}</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($currentSubscription && $currentSubscription->isActive())
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4 class="mb-1">{{ __('billing.stop_subscription_title') }}</h4>
                            <p class="mb-0 text-muted">{{ __('billing.stop_subscription_hint') }}</p>
                        </div>
                        <form method="POST" action="{{ route('billing.cancel') }}">
                            @csrf
                            <button type="submit" class="btn btn-danger mt-3 mt-md-0">{{ __('billing.cancel_subscription') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection