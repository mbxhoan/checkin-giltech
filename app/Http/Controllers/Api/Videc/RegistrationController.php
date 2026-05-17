<?php

namespace App\Http\Controllers\Api\Videc;

use App\Http\Controllers\Controller;
use App\Services\Videc\RegistrationService;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function __construct(private readonly RegistrationService $registrationService)
    {
    }

    public function draft(Request $request)
    {
        $data = $request->validate([
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'email' => ['required', 'email'],
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
            'source' => ['nullable', 'string', 'max:50'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        $registration = $this->registrationService->draft($data);

        return $this->responseSuccess($registration, 'Draft registration saved');
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
            'source' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ticket_id' => ['required', 'integer', 'exists:tickets,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        $registration = $this->registrationService->submit($data);

        return $this->responseSuccess($registration, 'Registration submitted');
    }
}
