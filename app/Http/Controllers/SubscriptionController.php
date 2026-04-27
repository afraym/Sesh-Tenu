<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $company = $user?->company()->with(['subscriptions.plan'])->first();
        $plans = Plan::active()->orderBy('price')->get();
        $currentSubscription = $company?->currentSubscription();

        return view('billing.index', compact('company', 'plans', 'currentSubscription'));
    }

    public function store(Request $request, Plan $plan)
    {
        $user = $request->user();
        $actorId = $user?->id;

        abort_unless($user && $user->canManageSubscription(), 403);

        if (!$plan->is_active) {
            return back()->withErrors(['plan' => __('billing.error_plan_unavailable')]);
        }

        $company = $user->company;

        if (!$company) {
            return back()->withErrors(['company' => __('billing.error_company_required_activate')]);
        }

        DB::transaction(function () use ($company, $plan, $actorId) {
            $company->subscriptions()
                ->where('status', 'active')
                ->update([
                    'status' => 'canceled',
                    'canceled_at' => now(),
                    'ends_at' => now(),
                    'updated_by' => $actorId,
                    'canceled_by' => $actorId,
                ]);

            Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addMonthsNoOverflow($plan->billing_cycle_months),
                'notes' => 'Activated from billing page.',
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
        });

        return redirect()->route('billing.index')->with('success', __('billing.success_subscription_activated'));
    }

    public function destroy(Request $request)
    {
        $user = $request->user();
        $actorId = $user?->id;

        abort_unless($user && $user->canManageSubscription(), 403);

        $company = $user->company;

        if (!$company) {
            return back()->withErrors(['company' => __('billing.error_company_required_manage')]);
        }

        $subscription = $company->currentSubscription();

        if (!$subscription) {
            return back()->withErrors(['subscription' => __('billing.error_no_active_subscription')]);
        }

        $subscription->update([
            'status' => 'canceled',
            'canceled_at' => now(),
            'ends_at' => now(),
            'updated_by' => $actorId,
            'canceled_by' => $actorId,
        ]);

        return redirect()->route('billing.index')->with('success', __('billing.success_subscription_canceled'));
    }
}