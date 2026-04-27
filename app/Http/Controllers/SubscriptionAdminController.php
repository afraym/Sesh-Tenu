<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionAdminController extends Controller
{
    public function index()
    {
        $plans = Plan::with(['createdBy:id,name', 'updatedBy:id,name'])->orderBy('price')->get();
        $companies = Company::with(['subscriptions' => function ($query) {
            $query->with(['plan', 'createdBy:id,name', 'updatedBy:id,name', 'canceledBy:id,name'])->latest();
        }])->orderBy('name')->get();

        return view('back.subscriptions.index', compact('plans', 'companies'));
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $actorId = $request->user()?->id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:plans,slug'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_cycle_months' => ['required', 'integer', 'min:1', 'max:12'],
            'features_text' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Plan::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'billing_cycle_months' => $validated['billing_cycle_months'],
            'features' => $this->parseFeatures($validated['features_text'] ?? null),
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);

        return back()->with('success', __('subscriptions.success_plan_created'));
    }

    public function updatePlan(Request $request, Plan $plan): RedirectResponse
    {
        $actorId = $request->user()?->id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:plans,slug,' . $plan->id],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_cycle_months' => ['required', 'integer', 'min:1', 'max:12'],
            'features_text' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $plan->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'billing_cycle_months' => $validated['billing_cycle_months'],
            'features' => $this->parseFeatures($validated['features_text'] ?? null),
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'updated_by' => $actorId,
        ]);

        return back()->with('success', __('subscriptions.success_plan_updated'));
    }

    public function destroyPlan(Plan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->exists()) {
            return back()->with('error', __('subscriptions.error_plan_has_subscriptions'));
        }

        $plan->delete();

        return back()->with('success', __('subscriptions.success_plan_deleted'));
    }

    public function assignCompanySubscription(Request $request, Company $company): RedirectResponse
    {
        $actorId = $request->user()?->id;

        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'status' => ['required', 'in:active,pending,expired,canceled'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'notes' => ['nullable', 'string'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $startsAt = $validated['starts_at'] ?? now()->toDateString();
        $endsAt = $validated['ends_at'] ?? now()->addMonthsNoOverflow($plan->billing_cycle_months)->toDateString();

        DB::transaction(function () use ($company, $validated, $startsAt, $endsAt, $actorId) {
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
                'plan_id' => $validated['plan_id'],
                'status' => $validated['status'],
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'notes' => $validated['notes'] ?? __('subscriptions.note_assigned_by_admin'),
                'canceled_at' => $validated['status'] === 'canceled' ? now() : null,
                'created_by' => $actorId,
                'updated_by' => $actorId,
                'canceled_by' => $validated['status'] === 'canceled' ? $actorId : null,
            ]);
        });

        return back()->with('success', __('subscriptions.success_assigned_to_company'));
    }

    public function cancelCompanySubscription(Request $request, Company $company): RedirectResponse
    {
        $actorId = $request->user()?->id;
        $subscription = $company->currentSubscription();

        if (!$subscription) {
            return back()->with('error', __('subscriptions.error_company_no_active_subscription'));
        }

        $subscription->update([
            'status' => 'canceled',
            'canceled_at' => now(),
            'ends_at' => now(),
            'updated_by' => $actorId,
            'canceled_by' => $actorId,
        ]);

        return back()->with('success', __('subscriptions.success_company_subscription_canceled'));
    }

    private function parseFeatures(?string $featuresText): array
    {
        if (!$featuresText) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $featuresText))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }
}