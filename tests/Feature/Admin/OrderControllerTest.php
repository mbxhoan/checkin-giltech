<?php

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\PortalUser;
use App\Models\PromoCode;
use App\Models\Registration;
use App\Models\RefundRequest;
use App\Models\Ticket;
use App\Models\User;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    protected User $adminUser;
    protected Event $event;
    protected PortalUser $portalUser;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->adminUser = User::factory()
            ->create(['is_admin' => true]);

        // Create event
        $this->event = Event::factory()
            ->create(['code' => 'videc-2026']);

        // Create portal user
        $this->portalUser = PortalUser::factory()
            ->create(['email' => 'customer@example.com']);

        // Create registration
        $registration = Registration::factory()
            ->create([
                'event_id' => $this->event->id,
                'portal_user_id' => $this->portalUser->id,
                'email' => $this->portalUser->email,
            ]);

        // Create order
        $this->order = Order::factory()
            ->create([
                'event_id' => $this->event->id,
                'portal_user_id' => $this->portalUser->id,
                'registration_id' => $registration->id,
                'status' => 'NEW',
                'total_amount' => 1000000,
            ]);
    }

    /**
     * Test listing orders
     */
    public function test_can_list_orders()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.orders.index'));

        $response->assertStatus(200);
        $response->assertViewHas('orders');
        $response->assertViewHas('stats');
        $response->assertSee($this->order->code);
    }

    /**
     * Test listing orders with filters
     */
    public function test_can_filter_orders_by_status()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.orders.index', ['status' => 'NEW']));

        $response->assertStatus(200);
        $response->assertSee($this->order->code);
    }

    /**
     * Test listing orders by email
     */
    public function test_can_filter_orders_by_email()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.orders.index', ['email' => $this->portalUser->email]));

        $response->assertStatus(200);
    }

    /**
     * Test view order details
     */
    public function test_can_view_order_details()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.orders.show', $this->order));

        $response->assertStatus(200);
        $response->assertViewHas('order', $this->order);
        $response->assertViewHas('orderSnapshot');
        $response->assertSee($this->order->code);
        $response->assertSee($this->portalUser->email);
    }

    /**
     * Test marking order as paid
     */
    public function test_can_mark_order_as_paid()
    {
        $this->assertEquals('NEW', $this->order->status);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.orders.mark-paid', $this->order), [
                'notes' => 'Manual payment marking for testing',
            ]);

        $response->assertRedirect();

        $this->order->refresh();
        $this->assertEquals('PAID', $this->order->status);
        $this->assertNotNull($this->order->paid_at);
        $this->assertIsArray($this->order->metadata);
        $this->assertTrue($this->order->metadata['manual_payment_marking'] ?? false);
    }

    /**
     * Test cannot mark already paid order
     */
    public function test_cannot_mark_paid_order_as_paid()
    {
        $this->order->update(['status' => 'PAID', 'paid_at' => now()]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.orders.mark-paid', $this->order));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test cancelling order
     */
    public function test_can_cancel_order()
    {
        $this->assertEquals('NEW', $this->order->status);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.orders.cancel', $this->order), [
                'reason' => 'Customer requested cancellation',
            ]);

        $response->assertRedirect();

        $this->order->refresh();
        $this->assertEquals('CANCELLED', $this->order->status);
        $this->assertNotNull($this->order->cancelled_at);
    }

    /**
     * Test cannot cancel already paid order
     */
    public function test_cannot_cancel_paid_order()
    {
        $this->order->update(['status' => 'PAID', 'paid_at' => now()]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.orders.cancel', $this->order));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test creating refund request
     */
    public function test_can_create_refund_request()
    {
        // Mark order as paid first
        $this->order->update(['status' => 'PAID', 'paid_at' => now()]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.orders.refund', $this->order), [
                'amount' => 500000,
                'reason' => 'Customer requested refund',
            ]);

        $response->assertRedirect();

        $refund = $this->order->refundRequests()->first();
        $this->assertNotNull($refund);
        $this->assertEquals(500000, $refund->amount);
        $this->assertEquals('PENDING', $refund->status);
    }

    /**
     * Test cannot refund unpaid order
     */
    public function test_cannot_refund_unpaid_order()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.orders.refund', $this->order), [
                'amount' => 500000,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test cannot refund with invalid amount
     */
    public function test_cannot_create_refund_with_invalid_amount()
    {
        $this->order->update(['status' => 'PAID', 'paid_at' => now()]);

        // Amount exceeds order total
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.orders.refund', $this->order), [
                'amount' => 5000000, // More than order total
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test export orders to CSV
     */
    public function test_can_export_orders_csv()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.orders.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition');
        $response->assertSee($this->order->code);
    }

    /**
     * Test unauthorized access requires authentication
     */
    public function test_cannot_access_without_authentication()
    {
        $response = $this->get(route('admin.orders.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test cannot mark already cancelled order
     */
    public function test_cannot_cancel_already_cancelled_order()
    {
        $this->order->update(['status' => 'CANCELLED', 'cancelled_at' => now()]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.orders.cancel', $this->order));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test order statistics
     */
    public function test_order_statistics_calculation()
    {
        // Create multiple orders with different statuses
        Order::factory()->create([
            'event_id' => $this->event->id,
            'status' => 'PAID',
            'total_amount' => 1000000,
            'paid_at' => now(),
        ]);

        Order::factory()->create([
            'event_id' => $this->event->id,
            'status' => 'NEW',
            'total_amount' => 500000,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.orders.index'));

        $stats = $response->viewData('stats');

        $this->assertGreaterThan(0, $stats['total_orders']);
        $this->assertGreaterThan(0, $stats['total_revenue']);
        $this->assertGreaterThan(0, $stats['payment_success_rate']);
    }
}
