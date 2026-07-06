<?php

namespace App\Http\Middleware;

use App\Models\Module;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks a route when its owning addon/module is disabled. Applied as
 * `module:live_classes`, `module:quizzes`, etc. When the module is off the
 * API responds 404 — so toggling a module truly hides its endpoints.
 */
class EnsureModuleEnabled
{
    public function handle(Request $request, Closure $next, string $key): Response
    {
        if (! Module::isEnabled($key)) {
            return response()->json([
                'message' => 'This feature is not available.',
            ], 404);
        }

        return $next($request);
    }
}
