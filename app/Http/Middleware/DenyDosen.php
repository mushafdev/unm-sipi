<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DenyDosen
{
    /**
     * Tutup aksi tulis (create/update/delete/verifikasi) untuk dosen.
     * Dosen hanya punya hak lihat.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (isDosen()) {
            abort(403, 'Dosen hanya memiliki akses lihat.');
        }

        return $next($request);
    }
}
