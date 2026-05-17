<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\CompanyDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanysRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\Company;
use App\Services\Admin\CompanyService;

class CompanyController extends Controller
{
    public function __construct(CompanyService $service)
    {
        $this->service = $service;
    }

    /**
     * Show the application companys index.
     */
    public function index(CompanyDataTable $dataTable)
    {
        if (!auth()->user()->isSysAdmin()) {
            return back()->withErrors(__('auth.not_authorized'));
        }

        $total = $dataTable->getFilter();
        return $dataTable->render('admin.companys.index', [
            'total' => $total->count(),
        ]);
    }

    /**
     * Display the specified resource edit form.
     */
    public function edit(Company $company)
    {
        if (!auth()->user()->isSysAdmin()) {
            return back()->withErrors(__('auth.not_authorized'));
        }

        return view('admin.companys.detail', [
            'model'             => $company,
            'languages'         => $this->service->language()->getListByAttributes(),
            'settings'          => $this->service->getConfigEventSettings(),
            'currentSettings'   => $company->settings ? json_decode($company->settings, true) : [],
            'templates'         => $this->service->email_template()->getPostmarkTemplates()['Templates'] ?? [],
            'senders'           => $this->service->email_sender()->getPostmarkSenders(true)['SenderSignatures'] ?? [],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->isSysAdmin()) {
            return back()->withErrors(__('auth.not_authorized'));
        }

        return view('admin.companys.detail', [
            'model'             => $this->service->init(),
            'languages'         => $this->service->language()->getListByAttributes(),
            'settings'          => $this->service->getConfigEventSettings(),
            'currentSettings'   => [],
            'templates'         => $this->service->email_template()->getPostmarkTemplates()['Templates'] ?? [],
            'senders'           => $this->service->email_sender()->getPostmarkSenders(true)['SenderSignatures'] ?? [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompanysRequest $request): RedirectResponse
    {
        if (!auth()->user()->isSysAdmin()) {
            return back()->withErrors(__('auth.not_authorized'));
        }

        $settings = $this->service->handleSettings(null, $request->settings);
        $request->merge([
            'settings' => json_encode($settings)
        ]);

        $jsonFields = [
            'languages',
            'templates',
            'senders',
        ];

        foreach ($jsonFields as $field) {
            if ($request->filled($field)) {
                $request->merge([
                    $field => json_encode($request->$field)
                ]);
            }
        }

        // if ($request->filled('languages')) {
        //     $request->merge([
        //         'languages' => json_encode($request->languages)
        //     ]);
        // }

        $attributes = array_merge(
            array_filter($request->only([
                'code',
                'name',
                'status',
                'limited_users',
                'limited_clients',
                'limited_emails',
                'limited_events',
            ])),
            $request->only([
                'limited_users',
                'limited_clients',
                'limited_emails',
                'limited_events',
                'languages',
                'settings',
                'templates',
                'senders',
            ])
        );

        $company = $this->service->create($attributes);
        return redirect()->route('admin.companys.edit', $company)->withSuccess("Tạo mới thành công");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CompanysRequest $request, Company $company): RedirectResponse
    {
        if (!auth()->user()->isSysAdmin()) {
            return back()->withErrors(__('auth.not_authorized'));
        }

        $settings = $this->service->handleSettings($company, $request->settings);
        $request->merge([
            'settings' => json_encode($settings)
        ]);

        $jsonFields = [
            'languages',
            'templates',
            'senders',
        ];

        foreach ($jsonFields as $field) {
            if ($request->filled($field)) {
                $request->merge([
                    $field => json_encode($request->$field)
                ]);
            }
        }

        $attributes = array_merge(
            array_filter($request->only([
                'code',
                'name',
                'status',
            ])),
            $request->only([
                'limited_users',
                'limited_clients',
                'limited_emails',
                'limited_events',
                'languages',
                'settings',
                'templates',
                'senders',
            ])
        );

        $this->service->update($company->id, $attributes);
        return redirect()->route('admin.companys.edit', $company)
            ->withSuccess("Cập nhật thành công");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        if (!auth()->user()->isSysAdmin()) {
            return back()->withErrors(__('auth.not_authorized'));
        }

        if ($company->events && $company->events->count()) {
            return back()->withErrors("Không thể xoá công ty vì đã có sự kiện khởi tạo cùng công ty này");
        }

        $this->service->delete($company->id);
        return redirect()->route('admin.companys.index')->withSuccess("Đã xoá thành công");
    }

    public function syncEventSetting(Company $company)
    {
        $events = $company->events;
        foreach ($events as $event) {
            $this->service->event_setting()->syncByEvent($event, true);
        }
        return $this->responseSuccess(null, "Đã đồng bộ cấu hình cho sự kiện thuộc công ty {$company->name}");
    }
}
