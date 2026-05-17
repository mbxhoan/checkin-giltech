<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Session;
use App\Traits\ApiResponser;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, ApiResponser;

    public $service;

    public function keepUploading(string $key = 'import_errors')
    {
        Session::forget($key);
        return true;
        return redirect()->route("admin.".$this->service->init()->getTable().".index")->withSuccess(__("".$this->service->init()->getTable().".partial_success"));
    }

    public function cancelImport(string $key = 'import_errors')
    {
        Session::forget($key);
        return true;
        return redirect()->route("admin.".$this->service->init()->getTable().".index")->withError(__($this->service->init()->getTable().".canceled"));
    }
}
