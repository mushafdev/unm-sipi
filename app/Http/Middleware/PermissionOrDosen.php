<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class PermissionOrDosen
{
    /**
     * Izinkan akses jika user memegang permission yang diminta ATAU user adalah dosen.
     * Dosen hanya dipakai untuk akses lihat (list/show), pembatasan datanya
     * dilakukan di controller berdasarkan groups.pembimbing_id.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (isDosen()) {
            return $next($request);
        }

        if (Gate::allows($permission)) {
            return $next($request);
        }

        abort(403, 'User does not have the right permissions.');
    }
}
