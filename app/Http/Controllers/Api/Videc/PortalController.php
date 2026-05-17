<?php

namespace App\Http\Controllers\Api\Videc;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Videc\PortalProfileUpdateRequest;
use App\Http\Requests\Api\Videc\PortalLoginRequest;
use App\Http\Requests\Api\Videc\PortalPasswordUpdateRequest;
use App\Models\Client;
use App\Models\Order;
use App\Models\PortalUser;
use App\Services\Videc\PaymentService;
use App\Services\Videc\PortalSessionService;
use App\Services\Videc\RegistrationFileService;
use App\Services\Videc\RegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class PortalController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly RegistrationService $registrationService,
        private readonly PortalSessionService $portalSessionService,
        private readonly RegistrationFileService $registrationFileService,
    ) {
    }

    public function login(PortalLoginRequest $request)
    {
        $data = $request->validated();
        $password = blank($data['password'] ?? null) ? PortalUser::DEFAULT_PASSWORD : $data['password'];

        $portalUser = PortalUser::query()
            ->where('email', Str::lower($data['email']))
            ->with(['registrations' => function ($query) use ($data) {
                if (!empty($data['event_id'])) {
                    $query->where('event_id', $data['event_id']);
                }

                $query->with(['event', 'currentOrder', 'items']);
            }, 'orders.paymentAttempts', 'orders.invoice', 'orders.ticketIssuances'])
            ->first();

        if (!$portalUser || !$this->portalPasswordMatches($portalUser, $password)) {
            throw ValidationException::withMessages([
                'password' => 'Email hoặc mật khẩu không đúng.',
            ]);
        }

        $loginToken = Str::random(40);
        $portalUser->forceFill([
            'last_login_at' => now(),
            'metadata' => array_merge($portalUser->metadata ?? [], [
                'last_login_token_hash' => hash('sha256', $loginToken),
                'last_login_token_created_at' => now()->toISOString(),
            ]),
        ])->save();

        return $this->responseSuccess([
            'portal_user' => $portalUser->fresh(['registrations.event', 'orders.paymentAttempts', 'orders.invoice', 'orders.ticketIssuances']),
            'custom_fields' => $this->resolveClientCustomFields($data),
            'client_id' => $this->resolveClientId($data),
            'login_token' => $loginToken,
            'auth_mode' => 'password_login',
        ], 'Portal login accepted');
    }

    public function updatePassword(PortalPasswordUpdateRequest $request)
    {
        $data = $request->validated();

        $portalUser = PortalUser::query()
            ->where('email', Str::lower($data['email']))
            ->firstOrFail();

        if (!$this->portalPasswordMatches($portalUser, $data['current_password'])) {
            throw ValidationException::withMessages([
                'current_password' => 'Mật khẩu hiện tại không đúng.',
            ]);
        }

        $portalUser->forceFill([
            'password' => Hash::make($data['password']),
            'metadata' => array_merge($portalUser->metadata ?? [], [
                'password_updated_at' => now()->toISOString(),
            ]),
        ])->save();

        return $this->responseSuccess([
            'portal_user' => $portalUser->fresh(['registrations.event', 'orders.paymentAttempts', 'orders.invoice', 'orders.ticketIssuances']),
            'auth_mode' => 'password_login',
        ], 'Portal password updated');
    }

    public function orders(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
        ]);

        $portalUser = PortalUser::query()->where('email', Str::lower($data['email']))->firstOrFail();
        $orders = $portalUser->orders()
            ->when(!empty($data['event_id']), fn ($query) => $query->where('event_id', $data['event_id']))
            ->with(['registration.items', 'paymentAttempts', 'invoice', 'refundRequests', 'ticketIssuances'])
            ->latest()
            ->get();

        return $this->responseSuccess([
            'portal_user' => $portalUser,
            'orders' => $orders,
        ], 'Portal orders');
    }

    public function showOrder(Order $order)
    {
        return $this->responseSuccess($this->paymentService->getOrderSnapshot($order), 'Portal order snapshot');
    }

    public function repay(Request $request, Order $order)
    {
        return $this->responseSuccess(
            $this->paymentService->createAttempt($order, $request->ip()),
            'Payment URL generated'
        );
    }

    public function buyMore(Request $request, Order $order)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.ticket_id' => ['required', 'integer', 'exists:tickets,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $newOrder = $this->registrationService->createSupplementalOrder($order, $data['items']);

        return $this->responseSuccess(
            $this->paymentService->getOrderSnapshot($newOrder),
            'Supplemental order created'
        );
    }

    public function updateProfile(PortalProfileUpdateRequest $request)
    {
        $data = $request->validated();

        $portalUser = $this->portalSessionService->authenticate(
            $data['email'],
            $data['login_token'],
        );

        $registration = $portalUser->registrations()
            ->where('event_id', (int) $data['event_id'])
            ->firstOrFail();

        $event = $registration->event()->firstOrFail();
        $client = Client::query()
            ->where('event_id', $event->id)
            ->whereRaw('LOWER(email) = ?', [Str::lower($portalUser->email)])
            ->latest('id')
            ->first();

        $customFields = (array) ($data['custom_fields'] ?? []);

        if (!empty($customFields)) {
            $customFields = $this->registrationFileService->attachSubmittedFiles(
                $event,
                $portalUser,
                $registration,
                $customFields,
                $client,
                false,
            );
        }

        $portalUser->fill([
            'name' => $data['name'] ?? $portalUser->name,
            'phone' => $data['phone'] ?? $portalUser->phone,
        ])->save();

        if (!empty($customFields)) {
            $registrationMetadata = $registration->metadata ?? [];
            $registrationMetadata['custom_fields'] = array_merge(
                (array) ($registrationMetadata['custom_fields'] ?? []),
                $customFields
            );

            $registration->forceFill([
                'metadata' => $registrationMetadata,
            ])->save();

            if ($client) {
                $client->forceFill([
                    'custom_fields' => array_merge((array) ($client->custom_fields ?? []), $customFields),
                ])->save();
            }
        }

        return $this->responseSuccess([
            'portal_user' => $portalUser->fresh(['registrations.event', 'orders.paymentAttempts', 'orders.invoice', 'orders.ticketIssuances']),
            'registration' => $registration->fresh(),
            'custom_fields' => $client?->fresh()?->custom_fields ?? [],
            'files' => $this->registrationFileService->mapActiveFilesByField($registration),
            'auth_mode' => 'password_login',
        ], 'Portal profile updated');
    }

    private function resolveClientCustomFields(array $data): array
    {
        $query = Client::query()
            ->where('email', Str::lower($data['email']));

        if (!empty($data['event_id'])) {
            $query->where('event_id', $data['event_id']);
        }

        return $query->latest('id')->first()?->custom_fields ?? [];
    }

    private function resolveClientId(array $data): ?int
    {
        $query = Client::query()
            ->where('email', Str::lower($data['email']));

        if (!empty($data['event_id'])) {
            $query->where('event_id', $data['event_id']);
        }

        return $query->latest('id')->first()?->id;
    }

    private function portalPasswordMatches(PortalUser $portalUser, string $password): bool
    {
        if (empty($portalUser->password)) {
            return hash_equals(PortalUser::DEFAULT_PASSWORD, $password);
        }

        if (Hash::check($password, $portalUser->password)) {
            return true;
        }

        return hash_equals((string) $portalUser->password, $password);
    }
}
