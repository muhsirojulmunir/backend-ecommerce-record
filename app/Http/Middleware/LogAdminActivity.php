<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Activitylog\Models\Activity;

class LogAdminActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();

        // Only log write actions for logged-in admin users
        if ($user && $user->isAdmin() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $subject = 'Admin Action';
            $description = "Admin {$user->name} performed {$request->method()} on {$request->path()}";

            activity()
                ->causedBy($user)
                ->withProperties([
                    'ip' => $request->ip(),
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'input' => $request->except(['password', 'password_confirmation']),
                ])
                ->log($description);
        }

        return $response;
    }
}
