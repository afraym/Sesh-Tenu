<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCompanyOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super admin can access everything
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Company owners can only access their company's resources
        if ($user->isCompanyOwner()) {
            // If there's a company ID in the route, verify ownership
            $companyId = $request->route('company') ? 
                        $request->route('company')->id ?? $request->route('company') : 
                        null;
            
            if ($companyId && $user->company_id != $companyId) {
                abort(403, 'Unauthorized. You can only manage your own company.');
            }

            return $next($request);
        }

        abort(403, 'Unauthorized. Company Owner access required.');
    }
}
