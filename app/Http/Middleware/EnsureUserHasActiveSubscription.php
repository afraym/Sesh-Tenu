<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasActiveSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->canManageAll()) {
            return $next($request);
        }

        if (!$user->company) {
            return redirect()->route('billing.index')->with('error', __('billing.error_account_not_linked'));
        }

        if ($user->hasActiveSubscription()) {
            return $next($request);
        }

        return redirect()->route('billing.index')->with('error', __('billing.error_company_subscription_inactive'));
    }
}