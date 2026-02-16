<?php

namespace App\Modules\Tenant\Controllers;

use Illuminate\Routing\Controller;

/**
 * Muestra las páginas de estado del tenant (suspendido, pendiente, cancelado).
 * Usado por EnsureTenantActive cuando el ISP del usuario no está activo.
 */
class TenantStatusController extends Controller
{
    public function suspended()
    {
        return view('tenant.suspended');
    }

    public function pending()
    {
        return view('tenant.pending');
    }

    public function cancelled()
    {
        return view('tenant.cancelled');
    }
}
