<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\ApiClientLog;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\CustomFieldTemplate;
use App\Models\EventSetting;
use App\Models\Email;
use App\Models\Event;
use App\Models\Order;
use App\Models\PortalUser;
use App\Models\Province;
use App\Models\PaymentAttempt;
use App\Models\PromoCode;
use App\Models\RegistrationFile;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Videc\OnePayGatewayService;
use Database\Seeders\Videc\Videc2026Seeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class VidecPaymentFlowTest extends TestCase
{
    private string $databasePath;

    protected function setUp(): void
    {
        $this->databasePath = tempnam(sys_get_temp_dir(), 'videc-test-') . '.sqlite';
        if (file_exists($this->databasePath)) {
            unlink($this->databasePath);
        }
        touch($this->databasePath);

        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=' . $this->databasePath);
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = $this->databasePath;
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = $this->databasePath;

        $this->createMediaLibraryTable();

        parent::setUp();

        config()->set('onepay.merchant_id', 'TESTONEPAY');
        config()->set('onepay.access_code', '6BEB2546');
        config()->set('onepay.secure_secret', '6D0870CDE5F24F34F3915FB0045120DB');
        config()->set('onepay.return_url', 'http://localhost/payment/return');
        config()->set('onepay.callback_url', 'http://localhost/api/payments/onepay/ipn');
        config()->set('onepay.payment_url', 'https://mtf.onepay.vn/paygate/vpcpay.op');
        config()->set('onepay.querydr_url', 'https://mtf.onepay.vn/paygate/Vpcdps.op');
        config()->set('onepay.order_expiry_minutes', 15);

        $this->createVidecSchema();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (isset($this->databasePath) && file_exists($this->databasePath)) {
            unlink($this->databasePath);
        }
    }

    public function test_registration_submit_is_idempotent_by_email_and_event(): void
    {
        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000);

        $payload = $this->registrationPayload($event, $ticket);

        $first = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $payload)
            ->assertOk();

        $second = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $payload)
            ->assertOk();

        $this->assertDatabaseCount('registrations', 1);
        $this->assertDatabaseCount('portal_users', 1);
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(
            $first->json('data.id'),
            $second->json('data.id')
        );
    }

    public function test_videc_ticket_catalog_returns_grouped_bilingual_data(): void
    {
        $this->seedVidecCatalog();

        $response = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->getJson('/api/events/106/tickets')
            ->assertOk();

        $response->assertJsonPath('data.event.code', 'videc-2026');
        $response->assertJsonPath('data.rules.min_select', 1);
        $response->assertJsonPath('data.rules.max_select', 3);
        $response->assertJsonPath('data.rules.one_per_group', true);
        $response->assertJsonPath('data.ticket_groups.0.label_vi', 'Tiền Hội nghị');
        $response->assertJsonPath('data.ticket_groups.0.label_en', 'Pre-Congress Ticket');
        $response->assertJsonPath('data.ticket_groups.1.label_vi', 'Hội nghị');
        $response->assertJsonPath('data.ticket_groups.2.label_vi', 'Triển lãm');
        $response->assertJsonPath('data.ticket_groups.2.tickets.0.price_usd_display', 'USD 20');
        $response->assertJsonPath('data.ticket_groups.0.tickets.0.name_vi', 'Phiên Cấy ghép Implant');
        $response->assertJsonPath('data.ticket_groups.0.tickets.0.name_en', 'Session Implantology');

        $this->assertCount(3, $response->json('data.ticket_groups'));
        $this->assertCount(7, $response->json('data.tickets'));
    }

    public function test_registration_ignores_public_cash_hint_and_defaults_to_onepay(): void
    {
        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'CASH-REG', 'Cash Registration Ticket', 1000000);

        $payload = $this->registrationPayload($event, $ticket);
        $payload['payment_method'] = 'cash_at_event';

        $first = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $payload)
            ->assertOk();

        $second = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $payload)
            ->assertOk();

        $this->assertSame($first->json('data.current_order_id'), $second->json('data.current_order_id'));
        $this->assertSame('unpaid', $first->json('data.current_order.status'));
        $this->assertSame('onepay', $first->json('data.current_order.payment_method'));

        $orderId = (int) $first->json('data.current_order.id');
        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/orders/{$orderId}/payment-attempts")
            ->assertOk();

        $order = Order::query()->with('paymentAttempts')->findOrFail($orderId);
        $this->assertCount(1, $order->paymentAttempts);
        $this->assertSame('onepay', $order->paymentAttempts->first()->gateway);
    }

    public function test_cash_payment_confirmation_marks_order_paid_and_creates_cash_log(): void
    {
        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'CASH-PAY', 'Cash Payment Ticket', 1000000);
        $this->createVidecMailCampaigns($event);

        $payload = $this->registrationPayload($event, $ticket);

        $created = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $payload)
            ->assertOk();

        $orderId = (int) $created->json('data.current_order.id');

        $this->actingAs($this->createAdminUser($event))
            ->post(route('admin.orders.mark-paid', $orderId), [
                'notes' => 'Cash received at counter',
                'amount_received' => 1000000,
                'receipt_code' => 'CASH-000001',
            ])
            ->assertRedirect();

        $order = Order::query()->with(['paymentAttempts', 'cashPaymentLogs', 'ticketIssuances', 'invoice'])->findOrFail($orderId);
        $this->assertSame('paid', $order->status);
        $this->assertSame('cash_at_event', $order->payment_method);
        $this->assertNotNull($order->paid_at);
        $this->assertCount(1, $order->cashPaymentLogs);
        $this->assertCount(1, $order->paymentAttempts);
        $this->assertSame('success', $order->paymentAttempts->first()->status);
        $this->assertCount(1, $order->ticketIssuances);
        $this->assertNotNull($order->invoice);
        $this->assertNotNull($order->client_id);
        $this->assertSame(1, Email::query()->where('campaign_id', 308)->count());

        $this->assertDatabaseHas('cash_payment_logs', [
            'order_id' => $orderId,
            'receipt_code' => 'CASH-000001',
            'amount_received' => 1000000,
            'change_amount' => 0,
        ]);
    }

    public function test_videc_ticket_crud_page_can_create_update_and_delete_tickets(): void
    {
        $this->withoutMiddleware();

        $event = $this->createEvent();
        $this->actingAs($this->createAdminUser($event));
        app('view')->share('errors', new \Illuminate\Support\ViewErrorBag());

        $this->get(route('admin.events.tickets.index', $event))
            ->assertOk()
            ->assertSee('Danh sách vé');

        $this->post(route('admin.events.tickets.store', $event), $this->ticketCrudPayload())
            ->assertRedirect();

        $ticket = Ticket::query()->firstOrFail();
        $this->assertSame('VC26-CF-VOSA', $ticket->code);
        $this->assertSame('1500000', $ticket->price);
        $this->assertSame('conference', $ticket->type);
        $this->assertSame('Hội nghị', data_get($ticket->metadata, 'group.label_vi'));

        $this->put(route('admin.events.tickets.update', ['event' => $event, 'ticket' => $ticket]), $this->ticketCrudPayload([
            'name' => 'Hội viên VOSA Premium / VOSA Member Premium',
            'price' => 1750000,
            'group_label_en' => 'Conference Pass',
            'display_name_en' => 'VOSA Member Premium',
            'display_price_usd' => 90,
        ]))
            ->assertRedirect();

        $ticket->refresh();
        $this->assertSame('Hội viên VOSA Premium / VOSA Member Premium', $ticket->name);
        $this->assertSame('1750000', $ticket->price);
        $this->assertSame('Conference Pass', data_get($ticket->metadata, 'group.label_en'));
        $this->assertEquals(90, data_get($ticket->metadata, 'display.price_usd'));

        $this->delete(route('admin.events.tickets.destroy', ['event' => $event, 'ticket' => $ticket]))
            ->assertRedirect();

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_videc_ticket_sidebar_entry_opens_event_selector(): void
    {
        $this->withoutMiddleware();

        $event = $this->createEvent();
        $this->actingAs($this->createAdminUser($event));
        app('view')->share('errors', new \Illuminate\Support\ViewErrorBag());

        $this->get(route('admin.tickets.index'))
            ->assertOk()
            ->assertSee('Chọn sự kiện để quản lý vé');

        $this->get(route('admin.tickets.select-event-to-manage', ['event_id' => $event->id]))
            ->assertRedirect(route('admin.events.tickets.index', $event));
    }

    public function test_videc_promo_code_crud_page_can_create_update_and_delete_codes(): void
    {
        $this->withoutMiddleware();

        $event = $this->createEvent();
        $this->actingAs($this->createAdminUser($event));
        app('view')->share('errors', new \Illuminate\Support\ViewErrorBag());

        $this->get(route('admin.events.tickets.index', $event))
            ->assertOk()
            ->assertSee('Khuyến mãi');

        $this->get(route('admin.events.promo-codes.index', $event))
            ->assertOk()
            ->assertSee('Danh sách promo code');

        $this->post(route('admin.events.promo-codes.store', $event), $this->promoCodeCrudPayload([
            'event_id' => $event->id,
        ]))
            ->assertRedirect();

        $promoCode = \App\Models\PromoCode::query()->firstOrFail();
        $this->assertSame('VIDEC10', $promoCode->code);
        $this->assertSame('10.00', $promoCode->discount_value);
        $this->assertSame('ACTIVE', $promoCode->status);

        $this->put(route('admin.events.promo-codes.update', ['event' => $event, 'promoCode' => $promoCode]), $this->promoCodeCrudPayload([
            'event_id' => $event->id,
            'code' => 'VIDEC15',
            'discount_value' => 15,
            'max_discount_amount' => 300000,
            'usage_limit' => 50,
            'status' => 'INACTIVE',
        ]))
            ->assertRedirect();

        $promoCode->refresh();
        $this->assertSame('VIDEC15', $promoCode->code);
        $this->assertSame('15.00', $promoCode->discount_value);
        $this->assertSame('INACTIVE', $promoCode->status);

        $this->delete(route('admin.events.promo-codes.destroy', ['event' => $event, 'promoCode' => $promoCode]))
            ->assertRedirect();

        $this->assertDatabaseCount('promo_codes', 0);
    }

    public function test_videc_ticket_catalog_enforces_one_ticket_per_category_and_one_quantity(): void
    {
        $this->seedVidecCatalog();

        $tickets = Ticket::query()
            ->where('event_code', 'videc-2026')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $primaryTicket = $tickets->first();
        $secondaryTicket = $tickets->get(1);

        $registration = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', [
                'event_id' => 106,
                'email' => 'alice@example.com',
                'name' => 'Alice Nguyen',
                'phone' => '0900000000',
                'items' => [
                    [
                        'ticket_id' => $primaryTicket->id,
                        'quantity' => 1,
                    ],
                ],
            ])
            ->assertOk();

        $this->assertNotEmpty($registration->json('data.current_order_id'));

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', [
                'event_id' => 106,
                'email' => 'bob@example.com',
                'name' => 'Bob Nguyen',
                'phone' => '0900000001',
                'items' => [
                    [
                        'ticket_id' => $primaryTicket->id,
                        'quantity' => 2,
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', [
                'event_id' => 106,
                'email' => 'carol@example.com',
                'name' => 'Carol Nguyen',
                'phone' => '0900000002',
                'items' => [
                    [
                        'ticket_id' => $primaryTicket->id,
                        'quantity' => 1,
                    ],
                    [
                        'ticket_id' => $secondaryTicket->id,
                        'quantity' => 1,
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_payment_attempt_creation_and_successful_ipn_flow(): void
    {
        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000);
        $payload = $this->registrationPayload($event, $ticket);

        $registration = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $payload)
            ->assertOk()
            ->json('data');

        $order = Order::query()->findOrFail($registration['current_order_id']);

        $payment = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/orders/{$order->id}/payment-attempts")
            ->assertOk()
            ->json('data');

        $this->assertStringContainsString('vpc_MerchTxnRef', $payment['payment_url']);
        $attempt = PaymentAttempt::query()->findOrFail($payment['attempt']['id']);

        $gateway = app(OnePayGatewayService::class);
        $ipnPayload = array_merge($payment['payment_params'], [
            'vpc_TxnResponseCode' => '0',
            'vpc_Message' => 'Approved',
            'vpc_Amount' => $gateway->formatAmount($attempt->amount),
            'vpc_SecureHash' => null,
        ]);
        $ipnPayload['vpc_SecureHash'] = $gateway->sign($ipnPayload);

        $this->post('/api/payments/onepay/ipn', $ipnPayload)
            ->assertOk()
            ->assertSee('responsecode=1&desc=confirm-success', false);

        $this->assertSame('paid', $order->refresh()->status);
        $this->assertSame('success', $attempt->refresh()->status);
        $this->assertDatabaseHas('invoices', [
            'order_id' => $order->id,
            'status' => 'issued',
        ]);
        $this->assertDatabaseCount('ticket_issuances', 1);
        $this->assertDatabaseHas('email_logs', [
            'order_id' => $order->id,
            'type' => 'payment_success',
            'status' => 'queued',
        ]);
    }

    public function test_legacy_payment_create_alias_accepts_order_id_body(): void
    {
        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000);
        $payload = $this->registrationPayload($event, $ticket);

        $registration = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $payload)
            ->assertOk()
            ->json('data');

        $order = Order::query()->findOrFail($registration['current_order_id']);

        $payment = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/payments/create', [
                'order_id' => $order->id,
            ])
            ->assertOk()
            ->json('data');

        $this->assertStringContainsString('vpc_MerchTxnRef', $payment['payment_url']);
        $this->assertSame($order->id, $payment['attempt']['order_id']);
    }

    public function test_invalid_ipn_signature_does_not_mark_order_paid(): void
    {
        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000);
        $payload = $this->registrationPayload($event, $ticket);

        $registration = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $payload)
            ->assertOk()
            ->json('data');

        $order = Order::query()->findOrFail($registration['current_order_id']);

        $payment = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/orders/{$order->id}/payment-attempts")
            ->assertOk()
            ->json('data');

        $attempt = PaymentAttempt::query()->findOrFail($payment['attempt']['id']);

        $gateway = app(OnePayGatewayService::class);
        $ipnPayload = array_merge($payment['payment_params'], [
            'vpc_TxnResponseCode' => '0',
            'vpc_Message' => 'Approved',
            'vpc_Amount' => $gateway->formatAmount($attempt->amount),
            'vpc_SecureHash' => 'BADHASH',
        ]);

        $this->post('/api/payments/onepay/ipn', $ipnPayload)
            ->assertOk()
            ->assertSee('responsecode=1&desc=confirm-success', false);

        $this->assertSame('pending_payment', $order->refresh()->status);
        $this->assertSame('pending_reconcile', $attempt->refresh()->status);
    }

    public function test_duplicate_successful_ipn_is_idempotent(): void
    {
        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000);
        $payload = $this->registrationPayload($event, $ticket);

        $registration = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $payload)
            ->assertOk()
            ->json('data');

        $order = Order::query()->findOrFail($registration['current_order_id']);

        $payment = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/orders/{$order->id}/payment-attempts")
            ->assertOk()
            ->json('data');

        $attempt = PaymentAttempt::query()->findOrFail($payment['attempt']['id']);

        $gateway = app(OnePayGatewayService::class);
        $ipnPayload = array_merge($payment['payment_params'], [
            'vpc_TxnResponseCode' => '0',
            'vpc_Message' => 'Approved',
            'vpc_Amount' => $gateway->formatAmount($attempt->amount),
            'vpc_SecureHash' => null,
        ]);
        $ipnPayload['vpc_SecureHash'] = $gateway->sign($ipnPayload);

        $this->post('/api/payments/onepay/ipn', $ipnPayload)
            ->assertOk()
            ->assertSee('responsecode=1&desc=confirm-success', false);

        $this->post('/api/onepay/ipn', $ipnPayload)
            ->assertOk()
            ->assertSee('responsecode=1&desc=confirm-success', false);

        $this->assertSame('paid', $order->refresh()->status);
        $this->assertSame('success', $attempt->refresh()->status);
        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseCount('ticket_issuances', 1);
    }

    public function test_amount_mismatch_goes_to_pending_reconcile_without_paid_order(): void
    {
        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000);

        $registration = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $this->registrationPayload($event, $ticket))
            ->assertOk()
            ->json('data');

        $order = Order::query()->findOrFail($registration['current_order_id']);
        $payment = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/orders/{$order->id}/payment-attempts")
            ->assertOk()
            ->json('data');

        $attempt = PaymentAttempt::query()->findOrFail($payment['attempt']['id']);
        $gateway = app(OnePayGatewayService::class);
        $ipnPayload = array_merge($payment['payment_params'], [
            'vpc_TxnResponseCode' => '0',
            'vpc_Message' => 'Approved',
            'vpc_Amount' => $gateway->formatAmount($attempt->amount + 1),
            'vpc_SecureHash' => null,
        ]);
        $ipnPayload['vpc_SecureHash'] = $gateway->sign($ipnPayload);

        $this->post('/api/payments/onepay/ipn', $ipnPayload)
            ->assertOk();

        $this->assertSame('pending_payment', $order->refresh()->status);
        $this->assertSame('pending_reconcile', $attempt->refresh()->status);
        $this->assertFalse((bool) $attempt->amount_valid);
    }

    public function test_promo_code_is_applied_server_side_before_payment_attempt(): void
    {
        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000);
        $promo = $this->createPromoCode($event, 'VIDEC10', 10);

        $registration = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $this->registrationPayload($event, $ticket, 2))
            ->assertOk()
            ->json('data');

        $order = Order::query()->findOrFail($registration['current_order_id']);

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/orders/{$order->id}/apply-promo", [
                'promo_code' => 'videc10',
            ])
            ->assertOk()
            ->assertJsonPath('data.discount_amount', 20000)
            ->assertJsonPath('data.total_amount', 180000);

        $this->assertSame(1, $promo->refresh()->usage_count);
        $this->assertSame('180000.00', $order->refresh()->total_amount);
        $this->assertDatabaseHas('promo_code_usages', [
            'order_id' => $order->id,
            'promo_code_id' => $promo->id,
            'discount_amount' => 20000,
        ]);

        $payment = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/orders/{$order->id}/payment-attempts")
            ->assertOk()
            ->json('data');

        $attempt = PaymentAttempt::query()->findOrFail($payment['attempt']['id']);
        $this->assertSame('180000.00', $attempt->amount);

        $gateway = app(OnePayGatewayService::class);
        $ipnPayload = array_merge($payment['payment_params'], [
            'vpc_TxnResponseCode' => '0',
            'vpc_Message' => 'Approved',
            'vpc_Amount' => $gateway->formatAmount($attempt->amount),
            'vpc_SecureHash' => null,
        ]);
        $ipnPayload['vpc_SecureHash'] = $gateway->sign($ipnPayload);

        $this->post('/api/payments/onepay/ipn', $ipnPayload)->assertOk();

        $this->assertSame('paid', $order->refresh()->status);
        $this->assertDatabaseCount('ticket_issuances', 2);
    }

    public function test_promo_code_can_be_removed_before_payment_attempt_and_rolls_back_amount(): void
    {
        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000);
        $promo = $this->createPromoCode($event, 'VIDEC10', 10);

        $registration = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $this->registrationPayload($event, $ticket, 2))
            ->assertOk()
            ->json('data');

        $order = Order::query()->findOrFail($registration['current_order_id']);

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/orders/{$order->id}/apply-promo", [
                'promo_code' => 'VIDEC10',
            ])
            ->assertOk()
            ->assertJsonPath('data.total_amount', 180000);

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/orders/{$order->id}/remove-promo")
            ->assertOk()
            ->assertJsonPath('data.discount_amount', 0)
            ->assertJsonPath('data.total_amount', 200000)
            ->assertJsonPath('data.order.promo_code_id', null);

        $order->refresh();
        $this->assertSame('200000.00', $order->subtotal_amount);
        $this->assertSame('0.00', $order->discount_amount);
        $this->assertSame('200000.00', $order->total_amount);
        $this->assertSame('200000.00', $order->price);
        $this->assertNull($order->promo_code_id);
        $this->assertDatabaseMissing('promo_code_usages', [
            'order_id' => $order->id,
            'promo_code_id' => $promo->id,
        ]);
        $this->assertSame(0, $promo->refresh()->usage_count);
    }

    public function test_multi_quantity_payment_issues_one_qr_per_ticket_unit(): void
    {
        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000);

        $registration = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $this->registrationPayload($event, $ticket, 3))
            ->assertOk()
            ->json('data');

        $order = Order::query()->findOrFail($registration['current_order_id']);
        $payment = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/orders/{$order->id}/payment-attempts")
            ->assertOk()
            ->json('data');

        $attempt = PaymentAttempt::query()->findOrFail($payment['attempt']['id']);
        $gateway = app(OnePayGatewayService::class);
        $ipnPayload = array_merge($payment['payment_params'], [
            'vpc_TxnResponseCode' => '0',
            'vpc_Message' => 'Approved',
            'vpc_Amount' => $gateway->formatAmount($attempt->amount),
            'vpc_SecureHash' => null,
        ]);
        $ipnPayload['vpc_SecureHash'] = $gateway->sign($ipnPayload);

        $this->post('/api/payments/onepay/ipn', $ipnPayload)->assertOk();

        $this->assertDatabaseCount('ticket_issuances', 3);
    }

    public function test_legacy_client_upsert_by_id_creates_registration_and_order_from_legacy_payload(): void
    {
        $this->withoutMiddleware();

        $event = $this->createEvent();
        $this->createApiUser($event);
        $this->createVidecMailCampaigns($event);
        $ticket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000);

        $response = $this->withServerVariables($this->legacyApiServerVariables())
            ->postJson('/api/v1/clients/upsert-by-id', $this->legacyUpsertPayload($event, $ticket))
            ->assertOk();

        $response->assertJsonPath('data.name', 'NGUYỄN VĂN A');
        $response->assertJsonPath('data.registration.status', 'registered_unpaid');
        $response->assertJsonPath('data.order.status', 'unpaid');
        $response->assertJsonPath('data.order.total_amount', '100000.00');
        $response->assertJsonPath('data.tickets.0.ticket_code', 'VIP');
        $response->assertJsonPath('data.registration.items.0.ticket_code', 'VIP');
        $response->assertJsonPath('data.order.registration_items.0.ticket_code', 'VIP');

        $this->assertDatabaseCount('clients', 1);
        $this->assertDatabaseCount('portal_users', 1);
        $this->assertDatabaseCount('registrations', 1);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('registration_items', 1);
        $this->assertDatabaseHas('orders', [
            'event_id' => $event->id,
            'total_amount' => '100000.00',
            'status' => 'unpaid',
        ]);

        $email = Email::query()->where('campaign_id', 307)->firstOrFail();
        $this->assertSame('WAITING', $email->status);
        $this->assertSame('duonghoangchau345@gmail.com', $email->to_email);
        $this->assertSame('NGUYỄN VĂN A', data_get(json_decode($email->param, true), 'name'));
        $this->assertSame('duonghoangchau345@gmail.com', data_get(json_decode($email->param, true), 'email'));

        $client = Client::query()->first();
        if (!$client) {
            $client = Client::query()->create([
                'event_id' => $event->id,
                'event_code' => $event->code,
                'qrcode' => 'VIDEC-TEST-CLIENT-1',
                'name' => 'Alice',
                'email' => 'alice@example.com',
                'status' => Client::STATUS_ACTIVE,
                'register_source' => Client::REGISTER_API,
            ]);
        }
        $response->assertJsonPath('data.order.client_id', $client->id);
        $this->assertSame('099900000', data_get($client->custom_fields, 'phone'));
        $this->assertSame('Ba.Solution', data_get($client->custom_fields, 'job'));
    }

    public function test_legacy_client_upsert_by_id_updates_existing_client_and_replaces_draft_order_items(): void
    {
        $this->withoutMiddleware();

        $event = $this->createEvent();
        $this->createApiUser($event);
        $this->createVidecMailCampaigns($event);
        $firstTicket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000, 'vip', 10);
        $secondTicket = $this->createTicket($event, 'STD', 'Standard Ticket', 200000, 'standard', 20);

        $firstResponse = $this->withServerVariables($this->legacyApiServerVariables())
            ->postJson('/api/v1/clients/upsert-by-id', $this->legacyUpsertPayload($event, $firstTicket))
            ->assertOk();

        $clientId = $firstResponse->json('data.id');
        $orderId = $firstResponse->json('data.order.id');

        $secondResponse = $this->withServerVariables($this->legacyApiServerVariables())
            ->postJson('/api/v1/clients/upsert-by-id', $this->legacyUpsertPayload(
                $event,
                $secondTicket,
                $clientId,
                'NGUYỄN VĂN A - UPDATED'
            ))
            ->assertOk();

        $secondResponse->assertJsonPath('data.id', $clientId);
        $secondResponse->assertJsonPath('data.order.id', $orderId);
        $secondResponse->assertJsonPath('data.order.client_id', $clientId);
        $secondResponse->assertJsonPath('data.order.total_amount', '200000.00');
        $secondResponse->assertJsonPath('data.tickets.0.ticket_code', 'STD');
        $secondResponse->assertJsonPath('data.order.registration_items.0.ticket_code', 'STD');

        $this->assertDatabaseCount('clients', 1);
        $this->assertDatabaseCount('registrations', 1);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('registration_items', 1);
        $this->assertSame(1, Email::query()->where('campaign_id', 307)->count());
        $this->assertDatabaseHas('registration_items', [
            'order_id' => $orderId,
            'ticket_id' => $secondTicket->id,
            'total_amount' => '200000.00',
        ]);

        $this->assertSame('NGUYỄN VĂN A - UPDATED', Client::query()->findOrFail($clientId)->name);
    }

    public function test_legacy_client_upsert_by_id_rejects_duplicate_email_within_same_event(): void
    {
        $this->withoutMiddleware();

        $event = $this->createEvent();
        $this->createApiUser($event);
        $firstTicket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000, 'vip', 10);
        $secondTicket = $this->createTicket($event, 'STD', 'Standard Ticket', 200000, 'standard', 20);

        $firstResponse = $this->withServerVariables($this->legacyApiServerVariables())
            ->postJson('/api/v1/clients/upsert-by-id', $this->legacyUpsertPayload($event, $firstTicket, null, 'NGUYỄN VĂN A'))
            ->assertOk();

        $secondResponse = $this->withServerVariables($this->legacyApiServerVariables())
            ->postJson('/api/v1/clients/upsert-by-id', $this->legacyUpsertPayload(
                $event,
                $secondTicket,
                null,
                'NGUYỄN VĂN B'
            , 'other@example.com'))
            ->assertOk();

        $clientId = $firstResponse->json('data.id');

        $this->withServerVariables($this->legacyApiServerVariables())
            ->postJson('/api/v1/clients/upsert-by-id', $this->legacyUpsertPayload(
                $event,
                $firstTicket,
                $clientId,
                'NGUYỄN VĂN A - UPDATED',
                'other@example.com'
            ))
            ->assertStatus(422)
            ->assertJsonPath('message.email.0', 'Email đã có người dùng');

        $this->assertDatabaseCount('clients', 2);
    }

    public function test_videc_dashboard_report_and_client_pages_show_ticket_analytics_and_history(): void
    {
        $event = $this->createEvent();
        $this->createApiUser($event);
        $paidTicket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000, 'REGULAR', 10);
        $extraTicket = $this->createTicket($event, 'STD', 'Standard Ticket', 50000, 'REGULAR', 20);

        $registration = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $this->registrationPayload($event, $paidTicket))
            ->assertOk()
            ->json('data');

        $order = Order::query()->findOrFail($registration['current_order_id']);
        $order->forceFill([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_url' => null,
            'token' => null,
        ])->save();

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/portal/orders/{$order->id}/buy-more", [
                'items' => [
                    [
                        'ticket_id' => $extraTicket->id,
                        'quantity' => 1,
                    ],
                ],
            ])
            ->assertOk();

        $adminUser = $this->createAdminUser($event);
        $event->forceFill([
            'updated_by' => $adminUser->id,
        ])->save();

        $client = Client::query()->create([
            'event_id' => $event->id,
            'event_code' => $event->code,
            'qrcode' => 'VIDEC-TEST-CLIENT-1',
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'status' => Client::STATUS_ACTIVE,
            'register_source' => Client::REGISTER_API,
            'updated_by' => $adminUser->id,
        ]);

        $this->actingAs($adminUser);
        app('view')->share('errors', new \Illuminate\Support\ViewErrorBag());

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Phân tích vé')
            ->assertSee('Doanh thu đã nhận')
            ->assertSee('VIP Ticket')
            ->assertSee('Standard Ticket');

        $this->get(route('admin.reports.report', $event))
            ->assertOk()
            ->assertSee('Phân tích vé')
            ->assertSee('Tổng hợp theo vé')
            ->assertSee('VIP Ticket')
            ->assertSee('Standard Ticket');

        $this->get(route('admin.clients.index', $event))
            ->assertOk()
            ->assertSee('Vé');

        $clientsTableHtml = $this->get(route('admin.clients.data', $event))->json('data.html');
        $this->assertStringContainsString('VIP Ticket', $clientsTableHtml);
        $this->assertStringContainsString('Standard Ticket', $clientsTableHtml);

        $this->get(route('admin.clients.edit', $client))
            ->assertOk()
            ->assertSee('Vé đã và đang mua')
            ->assertSee('VIP Ticket')
            ->assertSee('Standard Ticket');
    }

    public function test_client_edit_page_shows_quick_cash_confirmation_for_latest_unpaid_order(): void
    {
        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000);
        $extraTicket = $this->createTicket($event, 'STD', 'Standard Ticket', 50000);

        $registration = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $this->registrationPayload($event, $ticket))
            ->assertOk()
            ->json('data');

        $order = Order::query()->findOrFail($registration['current_order_id']);
        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/portal/orders/{$order->id}/buy-more", [
                'items' => [
                    [
                        'ticket_id' => $extraTicket->id,
                        'quantity' => 1,
                    ],
                ],
            ])
            ->assertOk();

        $orders = Order::query()->where('event_id', $event->id)->orderByDesc('created_at')->get();
        $newerOrder = $orders->first();
        $olderOrder = $orders->last();

        $client = $this->createClient($event, 'alice@example.com');
        $adminUser = $this->createAdminUser($event);

        $this->actingAs($adminUser);
        app('view')->share('errors', new \Illuminate\Support\ViewErrorBag());

        $this->get(route('admin.clients.edit', $client))
            ->assertOk()
            ->assertSee('Xác nhận tiền mặt')
            ->assertSee($newerOrder->no)
            ->assertSee($olderOrder->no)
            ->assertSee("quickCashConfirmModal-{$newerOrder->id}")
            ->assertSee("quickCashConfirmModal-{$olderOrder->id}");
    }

    public function test_payment_status_return_and_portal_buy_more_flow(): void
    {
        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000);
        $extraTicket = $this->createTicket($event, 'STD', 'Standard Ticket', 50000);

        $registration = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $this->registrationPayload($event, $ticket))
            ->assertOk()
            ->json('data');

        $order = Order::query()->findOrFail($registration['current_order_id']);
        $client = $this->createClient($event, 'alice@example.com');
        $payment = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/orders/{$order->id}/payment-attempts")
            ->assertOk()
            ->json('data');
        $attempt = PaymentAttempt::query()->findOrFail($payment['attempt']['id']);

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->getJson("/api/payments/status/{$attempt->id}")
            ->assertOk()
            ->assertJsonPath('data.attempt.id', $attempt->id);

        $this->get('/payment/return?' . http_build_query([
            'vpc_MerchTxnRef' => $attempt->merchant_txn_ref,
            'vpc_TxnResponseCode' => '0',
            'vpc_Message' => 'Approved',
        ]))
            ->assertOk()
            ->assertSee('Chúc mừng bạn đã thanh toán thành công', false)
            ->assertSee('Đóng cửa sổ ngay', false)
            ->assertSee($attempt->merchant_txn_ref, false);

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/portal/login', [
                'email' => 'alice@example.com',
                'password' => PortalUser::DEFAULT_PASSWORD,
                'event_id' => $event->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.auth_mode', 'password_login')
            ->assertJsonPath('data.custom_fields.title', $client->custom_fields['title'])
            ->assertJsonPath('data.custom_fields.reference_id', $client->custom_fields['reference_id']);

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/portal/login', [
                'email' => 'alice@example.com',
                'event_id' => $event->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.auth_mode', 'password_login');

        $portalUser = PortalUser::query()->where('email', 'alice@example.com')->firstOrFail();
        $this->assertTrue(Hash::check(PortalUser::DEFAULT_PASSWORD, $portalUser->password));

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/portal/password', [
                'email' => 'alice@example.com',
                'current_password' => PortalUser::DEFAULT_PASSWORD,
                'password' => 'NewPass123!',
                'password_confirmation' => 'NewPass123!',
            ])
            ->assertOk()
            ->assertJsonPath('data.auth_mode', 'password_login');

        $portalUser->refresh();
        $this->assertTrue(Hash::check('NewPass123!', $portalUser->password));
        $this->assertFalse(Hash::check(PortalUser::DEFAULT_PASSWORD, $portalUser->password));

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/portal/login', [
                'email' => 'alice@example.com',
                'password' => PortalUser::DEFAULT_PASSWORD,
                'event_id' => $event->id,
            ])
            ->assertStatus(422);

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/portal/login', [
                'email' => 'alice@example.com',
                'password' => 'NewPass123!',
                'event_id' => $event->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.auth_mode', 'password_login');

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/portal/login', [
                'email' => 'alice@example.com',
                'password' => PortalUser::DEFAULT_PASSWORD,
                'event_id' => $event->id,
            ])
            ->assertStatus(422);

        $buyMore = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/portal/orders/{$order->id}/buy-more", [
                'items' => [
                    [
                        'ticket_id' => $extraTicket->id,
                        'quantity' => 2,
                    ],
                ],
            ])
            ->assertOk()
            ->json('data.order');

        $this->assertNotSame($order->id, $buyMore['id']);
        $this->assertSame('unpaid', $buyMore['status']);
        $this->assertDatabaseCount('orders', 2);
    }

    public function test_videc_upsert_payment_success_sends_qrcode_campaign_email_once(): void
    {
        $this->withoutMiddleware();

        $event = $this->createEvent();
        $this->createApiUser($event);
        $this->createVidecMailCampaigns($event);
        $ticket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000);

        $upsertResponse = $this->withServerVariables($this->legacyApiServerVariables())
            ->postJson('/api/v1/clients/upsert-by-id', $this->legacyUpsertPayload($event, $ticket))
            ->assertOk();

        $clientId = $upsertResponse->json('data.id');
        $orderId = $upsertResponse->json('data.order.id');

        $this->withMiddleware();

        $this->assertSame(1, Email::query()->where('campaign_id', 307)->count());

        $order = Order::query()->findOrFail($orderId);
        $this->assertTrue(
            in_array($order->refresh()->status, ['unpaid', 'pending_payment'], true),
            'Unexpected order status before payment attempt: ' . $order->refresh()->status
        );
        $payment = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/orders/{$order->id}/payment-attempts")
            ->assertOk()
            ->json('data');

        $attempt = PaymentAttempt::query()->findOrFail($payment['attempt']['id']);
        $gateway = app(OnePayGatewayService::class);
        $ipnPayload = array_merge($payment['payment_params'], [
            'vpc_TxnResponseCode' => '0',
            'vpc_Message' => 'Approved',
            'vpc_Amount' => $gateway->formatAmount($attempt->amount),
            'vpc_SecureHash' => null,
        ]);
        $ipnPayload['vpc_SecureHash'] = $gateway->sign($ipnPayload);

        $this->post('/api/payments/onepay/ipn', $ipnPayload)
            ->assertOk()
            ->assertSee('responsecode=1&desc=confirm-success', false);

        $paymentEmail = Email::query()->where('campaign_id', 308)->firstOrFail();
        $payload = json_decode((string) $paymentEmail->param, true);
        $client = Client::query()->findOrFail($clientId);

        $this->assertSame('WAITING', $paymentEmail->status);
        $this->assertSame('duonghoangchau345@gmail.com', $paymentEmail->to_email);
        $this->assertSame('NGUYỄN VĂN A', data_get($payload, 'name'));
        $this->assertSame($client->qrcode, data_get($payload, 'qrcode'));
        $this->assertNotEmpty(data_get($payload, 'img_qrcode'));

        $this->post('/api/onepay/ipn', $ipnPayload)
            ->assertOk()
            ->assertSee('responsecode=1&desc=confirm-success', false);

        $this->assertSame(1, Email::query()->where('campaign_id', 308)->count());
        $this->assertSame('paid', Order::query()->findOrFail($orderId)->status);
        $this->assertDatabaseCount('ticket_issuances', 1);
    }

    public function test_onepay_expired_marks_order_unpaid_and_logs_email(): void
    {
        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000);

        $registration = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $this->registrationPayload($event, $ticket))
            ->assertOk()
            ->json('data');

        $order = Order::query()->findOrFail($registration['current_order_id']);
        $payment = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/orders/{$order->id}/payment-attempts")
            ->assertOk()
            ->json('data');

        $attempt = PaymentAttempt::query()->findOrFail($payment['attempt']['id']);
        $gateway = app(OnePayGatewayService::class);
        $ipnPayload = array_merge($payment['payment_params'], [
            'vpc_TxnResponseCode' => '7',
            'vpc_Message' => 'Expired',
            'vpc_Amount' => $gateway->formatAmount($attempt->amount),
            'vpc_SecureHash' => null,
        ]);
        $ipnPayload['vpc_SecureHash'] = $gateway->sign($ipnPayload);

        $this->post('/api/payments/onepay/ipn', $ipnPayload)->assertOk();

        $this->assertSame('unpaid', $order->refresh()->status);
        $this->assertSame('expired', $attempt->refresh()->status);
        $this->assertDatabaseHas('email_logs', [
            'order_id' => $order->id,
            'type' => 'payment_expired',
        ]);
    }

    public function test_onepay_cancel_keeps_registration_and_marks_order_unpaid(): void
    {
        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000);

        $registration = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $this->registrationPayload($event, $ticket))
            ->assertOk()
            ->json('data');

        $order = Order::query()->findOrFail($registration['current_order_id']);
        $payment = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/orders/{$order->id}/payment-attempts")
            ->assertOk()
            ->json('data');

        $attempt = PaymentAttempt::query()->findOrFail($payment['attempt']['id']);
        $gateway = app(OnePayGatewayService::class);
        $ipnPayload = array_merge($payment['payment_params'], [
            'vpc_TxnResponseCode' => '1',
            'vpc_Message' => 'User cancelled',
            'vpc_Amount' => $gateway->formatAmount($attempt->amount),
            'vpc_SecureHash' => null,
        ]);
        $ipnPayload['vpc_SecureHash'] = $gateway->sign($ipnPayload);

        $this->post('/api/payments/onepay/ipn', $ipnPayload)->assertOk();

        $this->assertSame('unpaid', $order->refresh()->status);
        $this->assertSame('failed', $attempt->refresh()->status);
        $this->assertDatabaseHas('registrations', [
            'id' => $order->registration_id,
            'status' => 'registered_unpaid',
        ]);
    }

    public function test_cancel_revokes_issued_tickets(): void
    {
        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000);

        $registration = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $this->registrationPayload($event, $ticket, 2))
            ->assertOk()
            ->json('data');

        $order = Order::query()->findOrFail($registration['current_order_id']);
        $payment = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/orders/{$order->id}/payment-attempts")
            ->assertOk()
            ->json('data');

        $attempt = PaymentAttempt::query()->findOrFail($payment['attempt']['id']);
        $gateway = app(OnePayGatewayService::class);
        $ipnPayload = array_merge($payment['payment_params'], [
            'vpc_TxnResponseCode' => '0',
            'vpc_Message' => 'Approved',
            'vpc_Amount' => $gateway->formatAmount($attempt->amount),
            'vpc_SecureHash' => null,
        ]);
        $ipnPayload['vpc_SecureHash'] = $gateway->sign($ipnPayload);
        $this->post('/api/payments/onepay/ipn', $ipnPayload)->assertOk();

        $this->assertDatabaseCount('ticket_issuances', 2);

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson("/api/orders/{$order->id}/cancel", [
                'reason' => 'Customer request',
            ])
            ->assertOk();

        $this->assertDatabaseMissing('ticket_issuances', [
            'order_id' => $order->id,
            'status' => 'issued',
        ]);
        $this->assertDatabaseHas('ticket_issuances', [
            'order_id' => $order->id,
            'status' => 'revoked',
        ]);
    }

    public function test_api_logs_page_is_visible_to_sys_admin_and_records_external_posts(): void
    {
        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'VIP', 'VIP Ticket', 100000);

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $this->registrationPayload($event, $ticket))
            ->assertOk();

        $log = ApiClientLog::query()->latest('id')->firstOrFail();
        $this->assertSame('POST', $log->method);
        $this->assertSame('/api/registrations/submit', $log->endpoint);
        $this->assertSame('SUCCESS', $log->status);
        $this->assertSame('Web đăng ký', data_get($log->request, 'source'));
        $this->assertSame('alice@example.com', data_get($log->request, 'body.email'));
        $this->assertSame(200, data_get($log->response, 'http_status'));

        $this->actingAs($this->createAdminUser($event))
            ->get(route('admin.api-client-logs.index'))
            ->assertOk()
            ->assertSee('API Logs');

        $regularUser = User::query()->create([
            'name' => 'Regular Admin',
            'email' => 'regular-admin@example.com',
            'password' => 'password',
            'email_verified_at' => now(),
            'is_admin' => false,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->withoutMiddleware(\App\Http\Middleware\EnsureUserIsValid::class)
            ->actingAs($regularUser)
            ->get(route('admin.api-client-logs.index'))
            ->assertStatus(404);
    }

    public function test_registration_file_upload_validates_size_and_mime_type(): void
    {
        Storage::fake('local');

        $event = $this->createEvent();
        $this->createFileCustomFieldTemplate($event, 'degree_certificate', true);

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->post('/api/registrations/files/upload', [
                'event_id' => $event->id,
                'email' => 'alice@example.com',
                'field_key' => 'degree_certificate',
                'file' => UploadedFile::fake()->create('degree.txt', 200, 'text/plain'),
            ])
            ->assertStatus(422);

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->post('/api/registrations/files/upload', [
                'event_id' => $event->id,
                'email' => 'alice@example.com',
                'field_key' => 'degree_certificate',
                'file' => UploadedFile::fake()->create('degree.pdf', 2500, 'application/pdf'),
            ])
            ->assertStatus(422);
    }

    public function test_registration_submit_attaches_uploaded_file_id_only(): void
    {
        Storage::fake('local');

        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'FILE-STD', 'File Standard', 100000);
        $this->createFileCustomFieldTemplate($event, 'degree_certificate', true);

        $fileId = $this->uploadRegistrationFile($event, 'alice@example.com', 'degree_certificate');

        $payload = $this->registrationPayload($event, $ticket);
        $payload['custom_fields'] = [
            'degree_certificate' => $fileId,
        ];

        $response = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $payload)
            ->assertOk();

        $registrationId = (int) $response->json('data.id');

        $registrationFile = RegistrationFile::query()->where('file_id', $fileId)->firstOrFail();
        $this->assertSame(RegistrationFile::STATUS_ACTIVE, $registrationFile->status);
        $this->assertSame($registrationId, (int) $registrationFile->registration_id);
        $this->assertNotNull($registrationFile->attached_at);
        $this->assertStringStartsWith('private/registration-files/', $registrationFile->path);
        $this->assertNotNull($registrationFile->portal_user_id);
    }

    public function test_portal_profile_update_replaces_file_and_archives_previous_file(): void
    {
        Storage::fake('local');

        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'FILE-RPL', 'File Replace', 100000);
        $this->createFileCustomFieldTemplate($event, 'degree_certificate', true);

        $firstFileId = $this->uploadRegistrationFile($event, 'alice@example.com', 'degree_certificate', 'first.pdf');

        $payload = $this->registrationPayload($event, $ticket);
        $payload['custom_fields'] = [
            'degree_certificate' => $firstFileId,
        ];

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $payload)
            ->assertOk();

        $loginToken = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/portal/login', [
                'email' => 'alice@example.com',
                'event_id' => $event->id,
            ])
            ->assertOk()
            ->json('data.login_token');

        $secondUploadResponse = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->post('/api/portal/files/upload', [
                'event_id' => $event->id,
                'email' => 'alice@example.com',
                'login_token' => $loginToken,
                'field_key' => 'degree_certificate',
                'file' => UploadedFile::fake()->create('second.pdf', 120, 'application/pdf'),
            ])
            ->assertOk();

        $secondFileId = (string) $secondUploadResponse->json('data.file_id');

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/portal/profile', [
                'event_id' => $event->id,
                'email' => 'alice@example.com',
                'login_token' => $loginToken,
                'custom_fields' => [
                    'degree_certificate' => $secondFileId,
                ],
            ])
            ->assertOk();

        $firstFile = RegistrationFile::query()->where('file_id', $firstFileId)->firstOrFail();
        $secondFile = RegistrationFile::query()->where('file_id', $secondFileId)->firstOrFail();

        $this->assertSame(RegistrationFile::STATUS_REPLACED, $firstFile->status);
        $this->assertSame($secondFile->id, (int) $firstFile->replaced_by_id);
        $this->assertSame(RegistrationFile::STATUS_ACTIVE, $secondFile->status);
        $this->assertNotNull($secondFile->registration_id);
    }

    public function test_portal_user_cannot_download_other_users_registration_file(): void
    {
        Storage::fake('local');

        $event = $this->createEvent();
        $ticket = $this->createTicket($event, 'FILE-PERM', 'File Permission', 100000);
        $this->createFileCustomFieldTemplate($event, 'degree_certificate', true);

        $aliceFileId = $this->uploadRegistrationFile($event, 'alice@example.com', 'degree_certificate', 'alice.pdf');
        $alicePayload = $this->registrationPayload($event, $ticket);
        $alicePayload['custom_fields'] = ['degree_certificate' => $aliceFileId];
        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $alicePayload)
            ->assertOk();

        $bobFileId = $this->uploadRegistrationFile($event, 'bob@example.com', 'degree_certificate', 'bob.pdf');
        $bobPayload = $this->registrationPayload($event, $ticket);
        $bobPayload['email'] = 'bob@example.com';
        $bobPayload['name'] = 'Bob';
        $bobPayload['custom_fields'] = ['degree_certificate' => $bobFileId];
        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/registrations/submit', $bobPayload)
            ->assertOk();

        $aliceToken = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->postJson('/api/portal/login', [
                'email' => 'alice@example.com',
                'event_id' => $event->id,
            ])
            ->assertOk()
            ->json('data.login_token');

        $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->getJson('/api/portal/files/' . $bobFileId . '/download?' . http_build_query([
                'event_id' => $event->id,
                'email' => 'alice@example.com',
                'login_token' => $aliceToken,
            ]))
            ->assertStatus(422);
    }

    public function test_cleanup_command_expires_unattached_temporary_uploads_older_than_24h(): void
    {
        Storage::fake('local');

        $event = $this->createEvent();
        $this->createFileCustomFieldTemplate($event, 'degree_certificate', true);

        $fileId = $this->uploadRegistrationFile($event, 'alice@example.com', 'degree_certificate', 'cleanup.pdf');

        $registrationFile = RegistrationFile::query()->where('file_id', $fileId)->firstOrFail();
        $registrationFile->forceFill([
            'created_at' => now()->subHours(30),
            'expires_at' => now()->subHours(1),
        ])->save();

        $this->assertTrue(Storage::disk('local')->exists($registrationFile->path));

        Artisan::call('videc:cleanup-registration-files', [
            '--hours' => 24,
        ]);

        $registrationFile->refresh();
        $this->assertSame(RegistrationFile::STATUS_EXPIRED, $registrationFile->status);
        $this->assertFalse(Storage::disk('local')->exists($registrationFile->path));
    }

    private function createMediaLibraryTable(): void
    {
        $pdo = new \PDO('sqlite:' . $this->databasePath);
        $pdo->exec('CREATE TABLE IF NOT EXISTS media_libraries (id INTEGER PRIMARY KEY AUTOINCREMENT, created_at TEXT NULL, updated_at TEXT NULL)');
    }

    private function createVidecSchema(): void
    {
        foreach ([
            'registration_file_logs',
            'registration_files',
            'ticket_issuances',
            'invoices',
            'payment_attempts',
            'registration_items',
            'orders',
            'registrations',
            'clients',
            'portal_users',
            'provinces',
            'campaigns',
            'landing_pages',
            'emails',
            'checkins',
            'custom_field_templates',
            'cards',
            'label_details',
            'labels',
            'role_user',
            'roles',
            'tickets',
            'events',
            'companys',
            'audit_logs',
            'refund_requests',
            'promo_code_usages',
            'promo_codes',
            'email_logs',
            'api_client_logs',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('companys', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('name');
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('code');
            $table->string('name');
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_default')->default(false);
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('username')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->string('type')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('role_id');
            $table->timestamps();
        });

        Schema::create('event_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('event_id');
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->text('value')->nullable();
            $table->text('options')->nullable();
            $table->string('group')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->string('input_type')->nullable();
            $table->timestamps();
        });

        Schema::create('custom_field_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->boolean('is_default')->default(false);
            $table->boolean('required')->default(false);
            $table->boolean('unique')->default(false);
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('type')->nullable();
            $table->text('accepts')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();
        });

        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->boolean('is_default')->default(false);
            $table->string('name');
            $table->decimal('width', 8, 4)->default(0);
            $table->decimal('height', 8, 4)->default(0);
            $table->string('unit')->default('%');
            $table->string('font')->nullable();
            $table->string('font_link')->nullable();
            $table->string('rotate')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('label_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('label_id');
            $table->string('field');
            $table->string('type')->nullable();
            $table->decimal('pos_x', 8, 4)->default(10);
            $table->decimal('pos_y', 8, 4)->default(10);
            $table->string('v_align')->default('left');
            $table->string('h_align')->default('top');
            $table->string('color')->default('#000000');
            $table->string('font')->nullable();
            $table->decimal('size', 8, 4)->default(15);
            $table->string('unit')->default('px');
            $table->string('width')->default('50');
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('event_code');
            $table->string('client_type')->nullable();
            $table->string('code');
            $table->string('name')->nullable();
            $table->string('background')->nullable();
            $table->string('extension')->nullable();
            $table->boolean('scaled')->default(false);
            $table->string('device')->nullable();
            $table->string('type')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('NEW');
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('event_code')->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->unsignedBigInteger('lp_id')->nullable();
            $table->string('qrcode');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('img_qrcode')->nullable();
            $table->string('type')->nullable();
            $table->string('register_source')->nullable();
            $table->json('custom_fields')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->boolean('is_online')->nullable();
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->string('subject')->nullable();
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->unsignedInteger('total_emails')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();
            $table->string('message_stream')->nullable();
            $table->string('limitation_per_time')->nullable();
            $table->string('hold_time')->nullable();
            $table->text('fixed_attachments')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->boolean('show_language_selection')->nullable();
            $table->string('slug')->nullable();
            $table->boolean('tracking')->nullable();
            $table->json('customs')->nullable();
            $table->json('orders')->nullable();
            $table->string('align')->nullable();
            $table->string('form_width')->nullable();
            $table->json('languages')->nullable();
            $table->unsignedBigInteger('banner_id')->nullable();
            $table->unsignedBigInteger('header_id')->nullable();
            $table->unsignedBigInteger('footer_id')->nullable();
            $table->unsignedBigInteger('bg_desktop_id')->nullable();
            $table->unsignedBigInteger('bg_tablet_id')->nullable();
            $table->unsignedBigInteger('bg_mobile_id')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_address')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->string('message_id')->nullable();
            $table->string('subject')->nullable();
            $table->string('email')->nullable();
            $table->string('qrcode')->nullable();
            $table->text('content')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->boolean('is_online')->nullable();
            $table->string('supplier')->nullable();
            $table->json('param')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('server_response')->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('to_name')->nullable();
            $table->string('to_email')->nullable();
            $table->string('status')->default('NEW');
            $table->text('error_log')->nullable();
            $table->timestamps();
        });

        Schema::create('checkins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('event_code')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('qrcode')->nullable();
            $table->string('client_name')->nullable();
            $table->timestamp('scan_time')->nullable();
            $table->json('custom_fields')->nullable();
            $table->string('type')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('NEW');
            $table->timestamps();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_id')->nullable();
            $table->string('event_code');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('code');
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->string('price');
            $table->string('dates_string')->nullable();
            $table->json('dates_valid')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('portal_users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('portal_user_id');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('current_order_id')->nullable();
            $table->string('code')->unique();
            $table->string('status')->default('draft');
            $table->string('checkin_sync_status')->default('pending');
            $table->string('checkin_reference')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('checkin_synced_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('registration_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_id', 40)->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('portal_user_id')->nullable();
            $table->unsignedBigInteger('registration_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('replaced_by_id')->nullable();
            $table->string('field_key', 100);
            $table->string('owner_email')->nullable();
            $table->string('disk', 50)->default('local');
            $table->string('path', 500);
            $table->string('original_name');
            $table->string('extension', 20);
            $table->string('mime_type', 120);
            $table->unsignedInteger('size_bytes');
            $table->string('sha256', 64)->nullable();
            $table->string('status', 30)->default('temp');
            $table->string('uploaded_by_type', 40)->default('public_registration');
            $table->unsignedBigInteger('uploaded_by_id')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('attached_at')->nullable();
            $table->timestamp('replaced_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('registration_file_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registration_file_id');
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('portal_user_id')->nullable();
            $table->string('action', 50);
            $table->string('actor_type', 50)->nullable();
            $table->string('actor_ref', 120)->nullable();
            $table->string('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('code');
            $table->string('discount_type')->default('percentage');
            $table->decimal('discount_value', 8, 2)->default(0);
            $table->decimal('max_discount_amount', 15, 2)->nullable();
            $table->decimal('min_order_amount', 15, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('promo_code_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promo_code_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('registration_id')->nullable();
            $table->unsignedBigInteger('portal_user_id')->nullable();
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->timestamp('applied_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->string('no');
            $table->string('code');
            $table->string('token')->nullable();
            $table->string('payment_url')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->dateTime('expiry_date');
            $table->json('ipn')->nullable();
            $table->string('status')->default('NEW');
            $table->unsignedBigInteger('portal_user_id')->nullable();
            $table->unsignedBigInteger('registration_id')->nullable();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('promo_code_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('currency', 3)->default('VND');
            $table->decimal('subtotal_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->string('checkin_sync_status')->default('pending');
            $table->string('checkin_reference')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('registration_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registration_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->string('ticket_code')->nullable();
            $table->string('ticket_name')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('ACTIVE');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('registration_id')->nullable();
            $table->unsignedInteger('attempt_no')->default(1);
            $table->string('gateway')->default('onepay');
            $table->string('payment_method')->nullable();
            $table->string('merchant_txn_ref')->unique();
            $table->string('onepay_transaction_no')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('VND');
            $table->string('status')->default('created');
            $table->string('response_code')->nullable();
            $table->string('response_message')->nullable();
            $table->boolean('secure_hash_valid')->nullable();
            $table->boolean('amount_valid')->nullable();
            $table->boolean('merchant_valid')->nullable();
            $table->boolean('order_info_valid')->nullable();
            $table->boolean('order_state_valid')->nullable();
            $table->string('return_url')->nullable();
            $table->string('callback_url')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('redirected_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('ipn_received_at')->nullable();
            $table->timestamp('succeeded_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('return_payload')->nullable();
            $table->json('callback_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->unique();
            $table->unsignedBigInteger('registration_id')->nullable();
            $table->string('invoice_no')->unique();
            $table->string('invoice_series')->nullable();
            $table->string('status')->default('draft');
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('VND');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->string('file_path')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('cash_payment_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('payment_attempt_id')->nullable();
            $table->unsignedBigInteger('cashier_user_id');
            $table->decimal('amount_due', 15, 2);
            $table->decimal('amount_received', 15, 2);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->string('receipt_code')->nullable()->unique();
            $table->text('note')->nullable();
            $table->timestamp('confirmed_at');
            $table->timestamp('voided_at')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('ticket_issuances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registration_item_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('client_ticket_id')->nullable();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->string('ticket_code')->unique();
            $table->string('qr_code')->unique();
            $table->string('status')->default('issued');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('registration_id')->nullable();
            $table->unsignedBigInteger('requested_by_portal_user_id')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status')->default('requested');
            $table->text('reason')->nullable();
            $table->string('external_ref')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('actor_type')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('level')->default('info');
            $table->text('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('portal_user_id')->nullable();
            $table->unsignedBigInteger('registration_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('type')->nullable();
            $table->string('subject')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->text('content')->nullable();
            $table->string('status')->default('queued');
            $table->string('provider_message_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('api_client_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method', 50);
            $table->string('endpoint', 200);
            $table->json('request');
            $table->json('response');
            $table->string('user_agent', 255)->nullable();
            $table->string('status', 50)->default('NEW');
            $table->timestamps();
        });
    }

    private function createEvent(): Event
    {
        $province = Province::query()->create([
            'is_default' => false,
            'name' => 'TP. Hồ Chí Minh',
        ]);

        $company = Company::query()->create([
            'code' => 'videc-2026-test',
            'is_default' => false,
            'name' => 'VIDEC 2026',
            'status' => 'ACTIVE',
        ]);

        return Event::query()->create([
            'company_id' => $company->id,
            'province_id' => $province->id,
            'from_date' => '2026-04-01',
            'to_date' => '2026-04-30',
            'code' => 'videc-2026',
            'name' => 'VIDEC 2026',
            'status' => 'ACTIVE',
        ]);
    }

    private function createTicket(Event $event, string $code, string $name, int $price, string $type = 'REGULAR', int $sortOrder = 0, array $metadata = []): Ticket
    {
        return Ticket::query()->create([
            'event_code' => $event->code,
            'sort_order' => $sortOrder,
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'price' => (string) $price,
            'dates_string' => null,
            'dates_valid' => [],
            'metadata' => $metadata,
        ]);
    }

    private function createClient(Event $event, string $email): Client
    {
        return Client::query()->create([
            'event_id' => $event->id,
            'event_code' => $event->code,
            'qrcode' => 'CLIENT-' . strtoupper(Str::random(8)),
            'name' => 'Alice Nguyen',
            'email' => $email,
            'img_qrcode' => null,
            'type' => 'API_TEST',
            'register_source' => 'API',
            'custom_fields' => [
                'title' => 'mr',
                'address' => '38, Phan Đình Giót, Tân Bình, Hồ Chí Minh',
                'country' => 'singapore',
                'job_title' => 'Ba.Solution',
                'cme_method' => 'email',
                'company_name' => 'Giltech Solutions',
                'country_code' => 'sg',
                'phone_number' => '099900000',
                'reference_id' => '20260423080646-f2H8l',
                'date_of_birth' => '01/01/2026',
            ],
            'status' => 'ACTIVE',
        ]);
    }

    private function createFileCustomFieldTemplate(Event $event, string $name = 'degree_certificate', bool $required = true): CustomFieldTemplate
    {
        return CustomFieldTemplate::query()->create([
            'event_id' => $event->id,
            'is_default' => false,
            'required' => $required,
            'unique' => false,
            'name' => $name,
            'description' => 'Degree / Certificate',
            'type' => CustomFieldTemplate::TYPE_FILE,
            'accepts' => json_encode(['pdf', 'jpg', 'jpeg', 'png']),
            'options' => null,
        ]);
    }

    private function uploadRegistrationFile(
        Event $event,
        string $email,
        string $fieldKey,
        string $filename = 'degree.pdf',
        int $sizeKb = 120,
        string $mimeType = 'application/pdf',
    ): string {
        $response = $this->withBasicAuth(env('REGISTER_AUTH_USER', 'your_username'), env('REGISTER_AUTH_PASS', 'your_password'))
            ->post('/api/registrations/files/upload', [
                'event_id' => $event->id,
                'email' => $email,
                'field_key' => $fieldKey,
                'file' => UploadedFile::fake()->create($filename, $sizeKb, $mimeType),
            ])
            ->assertOk();

        return (string) $response->json('data.file_id');
    }

    private function registrationPayload(Event $event, Ticket $ticket, int $quantity = 1): array
    {
        return [
            'event_id' => $event->id,
            'email' => 'alice@example.com',
            'name' => 'Alice',
            'phone' => '0900000000',
            'items' => [
                [
                    'ticket_id' => $ticket->id,
                    'quantity' => $quantity,
                ],
            ],
        ];
    }

    private function legacyUpsertPayload(Event $event, Ticket $ticket, ?int $id = null, string $name = 'NGUYỄN VĂN A', ?string $email = null): array
    {
        return [
            'id' => $id,
            'qrcode' => '',
            'event_id' => $event->id,
            'name' => $name,
            'email' => $email ?? 'duonghoangchau345@gmail.com',
            'type' => 'API_TEST',
            'items' => [
                [
                    'ticket_id' => $ticket->id,
                    'quantity' => 1,
                ],
            ],
            'custom_fields' => [
                'address' => '38, Phan Đình Giót, Tân Bình, Hồ Chí Minh',
                'job_title' => 'Ba.Solution',
                'phone_number' => '099900000',
                'company_name' => 'Giltech Solutions',
                'date_of_birth' => '01/01/2026',
                'title' => 'mr',
                'country_code' => 'sg',
                'country' => 'singapore',
                'cme_method' => 'email',
                'reference_id' => '20260423080646-f2H8l',
            ],
        ];
    }

    private function legacyApiServerVariables(): array
    {
        return [
            'PHP_AUTH_USER' => (string) env('REGISTER_AUTH_USER', 'your_username'),
            'PHP_AUTH_PW' => (string) env('REGISTER_AUTH_PASS', 'your_password'),
            'HTTP_USER_AGENT' => 'ApiPortal',
        ];
    }

    private function createPromoCode(Event $event, string $code, int $discountValue): PromoCode
    {
        return PromoCode::query()->create([
            'event_id' => $event->id,
            'code' => $code,
            'discount_type' => 'percentage',
            'discount_value' => $discountValue,
            'status' => 'ACTIVE',
        ]);
    }

    private function enableCashPayment(Event $event, ?string $startAt = null, ?string $endAt = null): void
    {
        $now = now();

        EventSetting::query()->insert([
            [
                'event_id' => $event->id,
                'name' => 'CASH_PAYMENT_ENABLED',
                'description' => 'Bật thanh toán tiền mặt tại quầy',
                'value' => 1,
                'options' => null,
                'group' => 'PAYMENT',
                'status' => 'ACTIVE',
                'input_type' => EventSetting::INPUT_TYPE_SWITCH,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'event_id' => $event->id,
                'name' => 'CASH_PAYMENT_START_AT',
                'description' => 'Thời điểm bắt đầu hiển thị thanh toán tiền mặt',
                'value' => $startAt ?? $now->copy()->subHour()->toDateTimeString(),
                'options' => null,
                'group' => 'PAYMENT',
                'status' => 'ACTIVE',
                'input_type' => EventSetting::INPUT_TYPE_TEXT,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'event_id' => $event->id,
                'name' => 'CASH_PAYMENT_END_AT',
                'description' => 'Thời điểm kết thúc hiển thị thanh toán tiền mặt',
                'value' => $endAt ?? $now->copy()->addHour()->toDateTimeString(),
                'options' => null,
                'group' => 'PAYMENT',
                'status' => 'ACTIVE',
                'input_type' => EventSetting::INPUT_TYPE_TEXT,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'event_id' => $event->id,
                'name' => 'CASH_PAYMENT_LABEL',
                'description' => 'Tên hiển thị cho phương thức tiền mặt',
                'value' => 'Thanh toán tiền mặt tại quầy',
                'options' => null,
                'group' => 'PAYMENT',
                'status' => 'ACTIVE',
                'input_type' => EventSetting::INPUT_TYPE_TEXT,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'event_id' => $event->id,
                'name' => 'CASH_PAYMENT_INSTRUCTION',
                'description' => 'Hướng dẫn thanh toán tiền mặt',
                'value' => 'Vui lòng thanh toán trực tiếp tại quầy trong ngày sự kiện.',
                'options' => null,
                'group' => 'PAYMENT',
                'status' => 'ACTIVE',
                'input_type' => EventSetting::INPUT_TYPE_TEXT,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'event_id' => $event->id,
                'name' => 'CASH_REQUIRES_STAFF_CONFIRMATION',
                'description' => 'Nhân viên phải xác nhận thủ công',
                'value' => 1,
                'options' => null,
                'group' => 'PAYMENT',
                'status' => 'ACTIVE',
                'input_type' => EventSetting::INPUT_TYPE_SWITCH,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    private function createVidecMailCampaigns(Event $event): void
    {
        $now = now()->toDateTimeString();

        Campaign::query()->insert([
            [
                'id' => 307,
                'event_id' => $event->id,
                'template_id' => 307,
                'is_online' => 1,
                'name' => 'VIDEC 2026 registration campaign',
                'type' => 'VIDEC_REGISTER',
                'subject' => 'VIDEC 2026 registration',
                'from_email' => 'event@videc2026.vn',
                'from_name' => 'VIDEC 2026',
                'status' => 'ACTIVE',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 308,
                'event_id' => $event->id,
                'template_id' => 308,
                'is_online' => 1,
                'name' => 'VIDEC 2026 payment success campaign',
                'type' => 'VIDEC_PAYMENT_SUCCESS',
                'subject' => 'VIDEC 2026 payment success',
                'from_email' => 'event@videc2026.vn',
                'from_name' => 'VIDEC 2026',
                'status' => 'ACTIVE',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    private function ticketCrudPayload(array $overrides = []): array
    {
        return array_merge([
            'event_code' => 'videc-2026',
            'code' => 'VC26-CF-VOSA',
            'name' => 'Hội viên VOSA / VOSA Member',
            'type' => 'conference',
            'sort_order' => 30,
            'price' => 1500000,
            'dates_string' => '13/11/2026 - 15/11/2026',
            'dates_valid_from' => '2026-11-13',
            'dates_valid_to' => '2026-11-15',
            'group_code' => 'conference',
            'group_label_vi' => 'Hội nghị',
            'group_label_en' => 'Conference Ticket',
            'group_allow_none' => 1,
            'group_none_label_vi' => 'Không tham gia',
            'group_none_label_en' => 'None',
            'display_name_vi' => 'Hội viên VOSA',
            'display_name_en' => 'VOSA Member',
            'display_price_usd' => 80,
            'max_quantity' => 1,
            'metadata_json' => '',
        ], $overrides);
    }

    private function promoCodeCrudPayload(array $overrides = []): array
    {
        return array_merge([
            'event_id' => 106,
            'code' => 'VIDEC10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'max_discount_amount' => 200000,
            'min_order_amount' => 500000,
            'usage_limit' => 100,
            'starts_at' => '2026-04-24T00:00',
            'ends_at' => '2026-12-31T23:59',
            'status' => 'ACTIVE',
            'metadata_json' => '',
        ], $overrides);
    }

    private function createApiUser(Event $event): void
    {
        \App\Models\User::query()->updateOrCreate(
            ['username' => env('REGISTER_AUTH_USER', 'your_username')],
            [
                'event_id' => $event->id,
                'name' => 'Register API User',
                'email' => 'register@example.com',
                'status' => 'ACTIVE',
                'is_admin' => false,
            ]
        );
    }

    private function createAdminUser(Event $event): User
    {
        $user = User::query()->updateOrCreate(
            ['username' => 'videc-admin'],
            [
                'event_id' => $event->id,
                'name' => 'VIDEC Admin',
                'email' => 'videc-admin@example.com',
                'password' => 'password',
                'is_admin' => true,
                'type' => User::TYPE_WEB,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        $roleId = \App\Models\Role::query()->updateOrCreate(
            ['name' => 'admin'],
            []
        )->id;

        \Illuminate\Support\Facades\DB::table('role_user')->updateOrInsert(
            [
                'user_id' => $user->id,
                'role_id' => $roleId,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return $user->refresh();
    }

    private function seedVidecCatalog(): void
    {
        app(Videc2026Seeder::class)->run();
    }
}
