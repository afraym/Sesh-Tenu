@extends('layouts.back')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="tim-icons icon-check-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="tim-icons icon-alert-circle-exc"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>{{ __('subscriptions.error_title') }}</strong>
                    <ul class="mb-0 pl-3 text-left">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-5 mb-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ __('subscriptions.create_plan') }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.subscriptions.plans.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>{{ __('subscriptions.plan_name') }}</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>{{ __('subscriptions.slug') }}</label>
                            <input type="text" class="form-control" name="slug" placeholder="starter" required>
                        </div>
                        <div class="form-group">
                            <label>{{ __('subscriptions.description') }}</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ __('subscriptions.price') }}</label>
                                <input type="number" min="0" step="0.01" class="form-control" name="price" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ __('subscriptions.billing_cycle_months') }}</label>
                                <input type="number" min="1" max="12" class="form-control" name="billing_cycle_months" value="1" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('subscriptions.features_per_line') }}</label>
                            <textarea class="form-control" name="features_text" rows="4" placeholder="{{ __('subscriptions.features_placeholder') }}"></textarea>
                        </div>
                        <div class="form-check mb-3 text-left">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                <span class="form-check-sign"></span>
                                {{ __('subscriptions.active') }}
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">{{ __('subscriptions.create_plan_btn') }}</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7 mb-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ __('subscriptions.plans') }}</h4>
                </div>
                <div class="card-body">
                    @if($plans->isEmpty())
                        <div class="alert alert-info mb-0">{{ __('subscriptions.no_plans') }}</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('subscriptions.name') }}</th>
                                        <th>{{ __('subscriptions.slug') }}</th>
                                        <th>{{ __('subscriptions.price') }}</th>
                                        <th>{{ __('subscriptions.cycle') }}</th>
                                        <th>{{ __('subscriptions.status') }}</th>
                                        <th>{{ __('subscriptions.last_updated_by') }}</th>
                                        <th>{{ __('subscriptions.updated_at') }}</th>
                                        <th>{{ __('subscriptions.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($plans as $plan)
                                        <tr>
                                            <td>{{ $plan->name }}</td>
                                            <td>{{ $plan->slug }}</td>
                                            <td>{{ number_format($plan->price, 2) }}</td>
                                            <td>{{ $plan->billing_cycle_months }} {{ __('subscriptions.months_short') }}</td>
                                            <td>
                                                @if($plan->is_active)
                                                    <span class="badge badge-success">{{ __('subscriptions.active') }}</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ __('subscriptions.inactive') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $plan->updatedBy?->name ?? $plan->createdBy?->name ?? __('subscriptions.na') }}</td>
                                            <td>{{ optional($plan->updated_at)->format('Y-m-d H:i') ?? __('subscriptions.na') }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" data-toggle="collapse" data-target="#edit-plan-{{ $plan->id }}">
                                                    {{ __('subscriptions.edit') }}
                                                </button>
                                                <form method="POST" action="{{ route('admin.subscriptions.plans.destroy', $plan) }}" class="d-inline" onsubmit="return confirm('{{ __('subscriptions.delete_plan_confirm') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">{{ __('subscriptions.delete') }}</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <tr class="collapse" id="edit-plan-{{ $plan->id }}">
                                            <td colspan="8">
                                                <form method="POST" action="{{ route('admin.subscriptions.plans.update', $plan) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="form-row">
                                                        <div class="form-group col-md-3">
                                                            <label>{{ __('subscriptions.name') }}</label>
                                                            <input type="text" class="form-control" name="name" value="{{ $plan->name }}" required>
                                                        </div>
                                                        <div class="form-group col-md-2">
                                                            <label>{{ __('subscriptions.slug') }}</label>
                                                            <input type="text" class="form-control" name="slug" value="{{ $plan->slug }}" required>
                                                        </div>
                                                        <div class="form-group col-md-2">
                                                            <label>{{ __('subscriptions.price') }}</label>
                                                            <input type="number" step="0.01" min="0" class="form-control" name="price" value="{{ $plan->price }}" required>
                                                        </div>
                                                        <div class="form-group col-md-2">
                                                            <label>{{ __('subscriptions.months') }}</label>
                                                            <input type="number" min="1" max="12" class="form-control" name="billing_cycle_months" value="{{ $plan->billing_cycle_months }}" required>
                                                        </div>
                                                        <div class="form-group col-md-3">
                                                            <label>{{ __('subscriptions.description') }}</label>
                                                            <input type="text" class="form-control" name="description" value="{{ $plan->description }}">
                                                        </div>
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="form-group col-md-9">
                                                            <label>{{ __('subscriptions.features_per_line') }}</label>
                                                            <textarea class="form-control" name="features_text" rows="2">{{ is_array($plan->features) ? implode("\n", $plan->features) : '' }}</textarea>
                                                        </div>
                                                        <div class="form-group col-md-3 d-flex align-items-end">
                                                            <div class="form-check text-left mb-0">
                                                                <label class="form-check-label">
                                                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $plan->is_active ? 'checked' : '' }}>
                                                                    <span class="form-check-sign"></span>
                                                                    {{ __('subscriptions.active') }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-info btn-sm">{{ __('subscriptions.save_plan') }}</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ __('subscriptions.company_subscriptions') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('subscriptions.company') }}</th>
                                    <th>{{ __('subscriptions.current_plan') }}</th>
                                    <th>{{ __('subscriptions.status') }}</th>
                                    <th>{{ __('subscriptions.expires') }}</th>
                                    <th>{{ __('subscriptions.last_changed_by') }}</th>
                                    <th>{{ __('subscriptions.changed_at') }}</th>
                                    <th>{{ __('subscriptions.assign_replace') }}</th>
                                    <th>{{ __('subscriptions.cancel') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($companies as $company)
                                    @php
                                        $current = $company->subscriptions
                                            ->first(function ($subscription) {
                                                return $subscription->status === 'active'
                                                    && $subscription->ends_at
                                                    && $subscription->ends_at->isFuture();
                                            });
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $company->name }}</strong>
                                        </td>
                                        <td>{{ $current?->plan?->name ?? __('subscriptions.no_active_plan') }}</td>
                                        <td>
                                            @if($current && $current->isActive())
                                                <span class="badge badge-success">{{ __('subscriptions.active') }}</span>
                                            @else
                                                <span class="badge badge-danger">{{ __('subscriptions.inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ optional($current?->ends_at)->format('Y-m-d') ?? __('subscriptions.na') }}</td>
                                        <td>{{ $current?->updatedBy?->name ?? $current?->createdBy?->name ?? __('subscriptions.na') }}</td>
                                        <td>{{ optional($current?->updated_at)->format('Y-m-d H:i') ?? __('subscriptions.na') }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.subscriptions.companies.assign', $company) }}">
                                                @csrf
                                                <div class="form-row">
                                                    <div class="col-md-4 mb-2">
                                                        <select name="plan_id" class="form-control" required>
                                                            <option value="">{{ __('subscriptions.assign_plan_placeholder') }}</option>
                                                            @foreach($plans as $plan)
                                                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <select name="status" class="form-control" required>
                                                            <option value="active">{{ __('subscriptions.active') }}</option>
                                                            <option value="pending">{{ __('subscriptions.pending') }}</option>
                                                            <option value="expired">{{ __('subscriptions.expired') }}</option>
                                                            <option value="canceled">{{ __('subscriptions.canceled') }}</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 mb-2">
                                                        <input type="date" name="starts_at" class="form-control" title="{{ __('subscriptions.start_date') }}">
                                                    </div>
                                                    <div class="col-md-2 mb-2">
                                                        <input type="date" name="ends_at" class="form-control" title="{{ __('subscriptions.end_date') }}">
                                                    </div>
                                                    <div class="col-md-1 mb-2">
                                                        <button type="submit" class="btn btn-info btn-sm btn-block">{{ __('subscriptions.save') }}</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.subscriptions.companies.cancel', $company) }}" onsubmit="return confirm('{{ __('subscriptions.cancel_subscription_confirm') }}');">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm">{{ __('subscriptions.cancel') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
