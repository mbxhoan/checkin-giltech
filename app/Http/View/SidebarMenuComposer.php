<?php
namespace App\Http\View;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class SidebarMenuComposer
{
    public function compose(View $view)
    {
        /* $menus = config('sidebar-menu.admin');
        $user = Auth::user();

        // Filter menus based on permissions
        $filteredMenus = array_filter($menus, function ($menu) use ($user) {
            if (isset($menu['permission'])) {
                if ($menu['permission'] === 'admin') {
                    return $user->is_admin || $user->hasRole('admin');
                }
                if ($menu['permission'] === 'all') {
                    return true;
                }
            }
            return false;
        });

        $view->with('menus', $filteredMenus); */
    }
}
