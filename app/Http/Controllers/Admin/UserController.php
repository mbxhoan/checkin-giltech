<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Users\CreateRequest;
use App\Http\Requests\Admin\Users\UpdateRequest;
use App\Models\Company;
use App\Models\Event;
use App\Services\Admin\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    /**
     * Show the application users index.
     */
    public function index(): View
    {
        $filters = [
            'is_admin'      => false,
        ];

        if (auth()->user()->isSysAdmin()) {
            $events = $this->service->event()->getListByAttributes();
            $companys = $this->service->company()->getListByAttributes([
                'status'    => [
                    Company::STATUS_ACTIVE,
                ],
            ]);
        } else {
            $filters['company_id'] = auth()->user()->company_id;
            $events = auth()->user()->company->events;
        }

        return view('admin.users.index', [
            'users'                 => $this->service->applyFilters($filters, 10),
            // 'users'                 => $this->service->getListByAttributes($filters, [
            //     /* excepts */
            //     'id'                => auth()->user()->id
            // ], [], 10),
            'companyArray'          => !empty($companys) ? $companys->mapWithKeys(function ($company) {
                    return [$company->id => "{$company->code} - {$company->name}"];
                })->toArray() : [],
            'eventArray'            => $events->mapWithKeys(function ($event) {
                    return [
                        $event->id  => "{$event->code} - {$event->name}"
                    ];
                })->toArray(),
        ]);
    }

    /**
     * Display the specified resource edit form.
     */
    public function create(): View
    {
        $companys = $this->service->company()->getListByAttributes([
            'status'    => [
                Company::STATUS_ACTIVE,
            ],
        ]);

        if (!auth()->user()->isSysAdmin()) {
            $company = auth()->user()->company;
            $eventFilters = [
                'company_id' => $company->id,
            ];
        } else {
            $companyId = request()->company_id;
            $company = $this->service->company()->findByAttributes([
                'id' => $companyId
            ]);
        }

        $eventId = request()->event_id;
        $event = $this->service->event()->findByAttributes([
            'id' => $eventId
        ]);

        $events = $this->service->event()->getListByAttributes(array_merge([
            'status'    => [
                Event::STATUS_ACTIVE,
            ]
        ], $eventFilters ?? []));
        // $areas = $this->service->event_area()->getListByAttributes([
        //     'event_id'   => $event->id
        // ]);
        $packages = $this->service->package()->getListByAttributes();

        return view('admin.users.edit', [
            'user'          => $this->service->init(),
            'roles'         => $this->service->role()->getRoles(),
            'company'       => $company,
            'event'         => $event ?? null,
            'companyArray'  => $companys->mapWithKeys(function ($company) {
                    return [$company->id => "{$company->code} - {$company->name}"];
                })->toArray(),
            'eventArray'  => ["" => " - "] + $events->mapWithKeys(function ($event) {
                    return [$event->id => "{$event->code} - {$event->name}"];
                })->toArray(),
            // 'areasArray'    => ["" => " - "] + $areas->pluck('name', 'id')->toArray(),
            'packagesArray' => $packages->mapWithKeys(function ($package) {
                return [
                    $package->id    => config("info.packages.{$package->code}.name")
                ];
            })->toArray(),
        ]);
    }

    /**
     * Display the specified resource edit form.
     */
    public function edit(User $user): View
    {
        $this->authorize('edit', $user);

        $companys = $this->service->company()->getListByAttributes([
            'status'    => [
                Company::STATUS_ACTIVE,
            ],
        ]);

        if (!auth()->user()->isSysAdmin()) {
            $company = auth()->user()->company;
            $eventFilters = [
                'company_id' => $company->id,
            ];
        }

        $events = $this->service->event()->getListByAttributes(array_merge([
            'status'    => [
                Event::STATUS_ACTIVE,
            ],
        ], $eventFilters ?? []));
        if ($user->event) {
            $areas = $this->service->event_area()->getListByAttributes([
                'event_id'   => $user->event->id
            ]);
        }
        $packages = $this->service->package()->getListByAttributes();

        return view('admin.users.edit', [
            'user'          => $user,
            'roles'         => $this->service->role()->getRoles(),
            'company'       => $company ?? null,
            'companyArray'  => $companys->mapWithKeys(function ($company) {
                    return [$company->id => "{$company->code} - {$company->name}"];
                })->toArray(),
            'eventArray'    => ["" => " - "] + $events->mapWithKeys(function ($event) {
                    return [$event->id  => "{$event->code} - {$event->name}"];
                })->toArray(),
            'areasArray'    => !empty($areas) ? ["" => "-"] + $areas->pluck('name', 'id')->toArray() : [],
            'packagesArray' => ["" => "-"] + $packages->mapWithKeys(function ($package) {
                    return [
                        $package->id    => config("info.packages.{$package->code}.name")
                    ];
                })->toArray(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRequest $request): RedirectResponse
    {
        if ($request->filled('password')) {
            $request->merge([
                'password' => Hash::make($request->input('password'))
            ]);
        }

        /* if ($request->filled('company_id')) {
            if (!$this->service->ensureLimited($request->company_id)) {
                return back()->withErrors("ĐÃ VƯỢT QUÁ SỐ LƯỢNG TÀI KHOẢN CHO PHÉP");
            }
        } */
        $attributes = array_merge(array_filter($request->only([
            'is_checkout',
            'company_id',
            'event_id',
            // 'area_id',
            'username',
            'name',
            'email',
            'password',
            'gender',
            'status',
            'type',
            'expire_date'
        ])), [
            'created_by'        => auth()->user()->id,
            'updated_by'        => auth()->user()->id,
            'package_id'        => $request->package_id,
            'email_verified_at' => now()
        ]);

        /* set for boolean columns */
        foreach ([
            'is_checkout',
        ] as $field) {
            if (isset($attributes[$field])) {
                $attributes[$field] = (($attributes[$field] == "true" || $attributes[$field] == "1") ? 1 : 0);
            } else {
                $attributes[$field] = 0;
            }
        }

        if (!auth()->user()->isSysAdmin()) {
            $attributes['company_id'] = auth()->user()->company_id;
        }

        $user = $this->service->create($attributes);
        $role_ids = array_values($request->get('roles', []));
        $user->roles()->sync($role_ids);
        return redirect()->route('admin.users.edit', $user)->withSuccess(__('users.created'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, User $user): RedirectResponse
    {
        if ($request->filled('password')) {
            $request->merge([
                'password' => Hash::make($request->input('password'))
            ]);
        }

        $attributes = array_merge(array_filter($request->only([
            'is_checkout',
            'company_id',
            'event_id',
            'name',
            // 'username',
            // 'email',
            'password',
            'gender',
            'status',
            'type',
        ])), $request->only([
            /* nếu không nhập là nhận null luôn */
            'package_id',
            'expire_date',
            'area_id',
        ]));

        /* set for boolean columns */
        foreach ([
            'is_checkout',
        ] as $field) {
            if (isset($attributes[$field])) {
                $attributes[$field] = (($attributes[$field] == "true" || $attributes[$field] == "1") ? 1 : 0);
            } else {
                $attributes[$field] = 0;
            }
        }

        if (!auth()->user()->isSysAdmin()) {
            $attributes['company_id'] = auth()->user()->company_id;
        }

        $attributes['updated_by'] = auth()->user()->id;
        $this->service->update($user->id, $attributes);
        $role_ids = array_values($request->get('roles', []));
        $user->roles()->sync($role_ids);
        return redirect()->route('admin.users.edit', $user)->withSuccess(__('users.updated'));
    }

    public function sendVerification(User $user)
    {
        if (empty($user->email_verified_at)) {
            if ($this->service->sendVerification($user)) {
                return back()->withSuccess("Đã gửi xác thực thành công");
            }

            return back()->withErrors("Đã gửi xác thực không thành công");
        }

        return back()->withErrors("Tài khoản đã được xác thực");
    }

    public function signOut(User $user)
    {
        $this->service->signOut($user);
        return back()->withSuccess("Đã đăng xuất user {$user->email}");
    }

    public function generateLoginQrcode(User $user)
    {
        $this->authorize('edit', $user);
        $loginQrcode = Str::random(48);
        $this->service->update($user->id, [
            'login_code' => $loginQrcode
        ]);

        return redirect()->route('admin.users.edit', $user);
    }
}
