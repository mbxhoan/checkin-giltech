<?php
namespace App\Services\Admin;

use App\Models\Role;
use App\Services\BaseService;

class RoleService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Role::class);
    }

    public function getRoles()
    {
        if (auth()->user()->isSysAdmin()) {
            return $this->model->all();
        }

        if (auth()->user()->isAdmin()) {
            return $this->model->whereIn('name', $this->model->getRolesByAdmin())->get();
        }

        return null;
    }
}
