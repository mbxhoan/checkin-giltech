<?php
namespace App\Services\Admin;

use App\Models\User;
use App\Services\BaseService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use App\Services\Middleware\EmailService as MiddlewareEmailService;

class UserService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(User::class);
    }

    public function company()
    {
        return app(CompanyService::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function event_area()
    {
        return app(EventAreaService::class);
    }

    public function role()
    {
        return app(RoleService::class);
    }

    public function package()
    {
        return app(PackageService::class);
    }

    public function middleware_email()
    {
        return app(MiddlewareEmailService::class);
    }

    public function applyFilters(array $filters = [], int $paginate = 0, $query = null)
    {
        if (empty($query)) {
            $query = $this->getQuery(); // Get the base query
        }

        if (count($filters)) {
            foreach ($filters as $key => $value) {
                $query->where($key, $value);
            }
        }

        // if (!auth()->user()->isSysAdmin()) {
        //     $query->where('company_id', auth()->user()->company->id);
        // } else {
        //     if (request()->filled('company_id')) {
        //         $attributes['company_id'] = request()->input('company_id');
        //     }
        // }

        $query->where('status', '!=', User::STATUS_DELETED);
        $query->where('id', '!=', auth()->user()->id);
        $query->orderBy('updated_at', 'DESC');
        $query->orderBy('status', 'ASC');

        /* lọc */
        if (request()->filled('company_id')) {
            $attributes['company_id'] = request()->input('company_id');
        }

        if (request()->filled('event_id')) {
            $attributes['event_id'] = request()->input('event_id');
        }

        if (request()->filled('status')) {
            $attributes['status'] = request()->input('status');
        }

        if (request()->filled('type')) {
            $attributes['type'] = request()->input('type');
        }

        $dateField = request()->input('field_date');

        if ($dateField) {
            if (request()->filled('from_date')) {
                $query->whereDate(request()->input('field_date'), '>=', request()->input('from_date'));
            }

            if (request()->filled('to_date')) {
                $query->whereDate(request()->input('field_date'), '<=', request()->input('to_date'));
            }
        }

        if (isset($attributes) && count($attributes)) {
            foreach ($attributes as $key => $value) {
                $query->where($key, $value);
            }
        }

        return $paginate > 0 ? $query->paginate($paginate) : ($query->get() ?? collect());
    }

    public function ensureLimited(int $companyId, string $field)
    {
        $company = $this->company()->findById($companyId);

        if (isset($company->$field) && $company->$field > 0) {
            $list = $this->getListByAttributes([
                'company_id' => $companyId,
            ]);

            if (!empty($list) && $list->count() >= $company->$field) {
                return false;
            }
        }

        return true;
    }

    public function sendVerification($user)
    {
        $templateId = 39930831;
        $url = URL::temporarySignedRoute(
            'users.verify', // The route name
            Carbon::now()->addMinutes(config('app.verification_expire') ?? 5), // Expiration time
            [
                'prefix'        => $user->username,
                'verify_token'  => $user->verify_token
            ]
        );

        $variables = [
            "name"          => $user->name,
            "email"         => $user->email,
            "product_name"  => env('APP_NAME'),
            "action_url"    => $url,
            // "action_url"    => route('users.verify', [
            //     'prefix'    => $user->username,
            //     'verify_token'     => $user->verify_token,
            // ]),
            "package"       => $user->package_id ? config('info.packages')[$user->package->code]['name'] : null,
            "start_date"    => now()->format('d-m-Y'),
            "end_date"      => $user->expire_date ? humanize_date($user->expire_date, 'd-m-Y') : null,
            "support_email" => env('FROM_MAIL'),
        ];

        $this->middleware_email()->sendMailTestCurl($user->email, $templateId, $variables);
        return true;
    }

    public function signOut(User $user)
    {
        $user->tokens()->delete();

        if ($user->session_id) {
            $path = storage_path('framework/sessions/' . $user->session_id);
            if (file_exists($path)) {
                unlink($path);
            }

            $user->session_id = null;

        }

        $user->last_login_at = null;
        $user->save();
        return true;
    }
}
