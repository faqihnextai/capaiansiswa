<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Jika request bukan AJAX dan mengharapkan JSON
        if (! $request->expectsJson()) {
            // Periksa apakah URL yang diakses mengandung 'admin'
            // Ini adalah cara sederhana untuk membedakan rute admin vs publik
            if ($request->is('admin/*')) {
                return route('admin.login'); // Arahkan ke rute login admin
            }
            // Jika bukan rute admin, arahkan ke halaman utama atau rute login publik jika ada
            // Karena Anda belum punya login publik, kita arahkan ke halaman utama '/'
            return '/'; // Mengarahkan ke halaman utama jika tidak terautentikasi di area non-admin
        }
        return null;
    }
}