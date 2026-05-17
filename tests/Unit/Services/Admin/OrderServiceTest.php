<?php

namespace Tests\Unit\Services\Admin;

use App\Models\Event;
use App\Models\Order;
use App\Models\PortalUser;
use App\Models\PromoCode;
use App\Models\Registration;
use App\Services\Admin\OrderService;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    protected OrderService $service;
    protected Event $event;
    protected PortalUser $portalUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(OrderService::class);

        $this->event = Event::factory()
            ->create(['code' => 'videc-2026']);

        $this->portalUser = PortalUser::factory()
            ->create(['email' => 'test@example.com']);
    }

    /**
     * Test getting orders with filters
     */
    public function test_can_get_orders_with_filters()
    {
        Order::factory(5)->create([
            'event_id' => $this->event->id,
            'portal_user_id' => $this->portalUser->id,
            'status' => 'NEW',
        ]);

        Order::factory(3)->create([
            'event_id' => $this->event->id,
            'status' => 'PAID',
        ]);

        $orders = $this->service->getOrdersWithFilters(
            ['status' => 'NEW'],
            15
        );

        $this->assertCount(5, $orders);
    }

    /**
     * Test filtering by event
     */
    public function test_can_filter_by_event()
    {
        $event2 = Event::factory()->create();

        Order::factory(3)->create([
            'event_id' => $this->event->id,
        ]);

        Order::factory(2)->create([
            'event_id' => $event2->id,
        ]);

        $orders = $this->service->getOrdersWithFilters(
            ['event_id' => $this->event->id],
            15
        );

        $this->assertCount(3, $orders);
    }

    /**
     * Test get order snapshot
     */
    public function test_can_get_order_snapshot()
    {
        $registration = Registration::factory()
            ->create([
                'event_id' => $this->event->id,
                'portal_user_id' => $this->portalUser->id,
            ]);

        $order = Order::factory()
            ->create([
                'event_id' => $this->event->id,
                'portal_user_id' => $this->portalUser->id,
                'registration_id' => $registration->id,
                'total_amount' => 1000000,
                'subtotal_amount' => 1000000,
                'discount_amount' => 0,
                'tax_amount' => 0,
            ]);

        $snapshot = $this->service->getOrderSnapshot($order);

        $this->assertEquals($order->id, $snapshot['id']);
        $this->assertEquals($order->code, $snapshot['code']);
        $this->assertEquals('NEW', $snapshot['status']);
        $this->assertIsArray($snapshot['customer']);
        $this->assertIsArray($snapshot['event']);
        $this->assertIsArray($snapshot['pricing']);
        $this->assertEquals(1000000, $snapshot['pricing']['total_amount']);
    }

    /**
     * Test marking order as paid
     */
    public function test_can_mark_order_as_paid()
    {
        $order = Order::factory()
            ->create(['status' => 'NEW']);

        $result = $this->service->markOrderAsPaid($order, 'Test notes');

        $this->assertTrue($result);

        $order->refresh();
        $this->assertEquals('PAID', $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertTrue($order->metadata['manual_payment_marking'] ?? false);
    }

    /**
     * Test cannot mark already paid order
     */
    public function test_cannot_mark_paid_order_as_paid()
    {
        $order = Order::factory()
            ->create(['status' => 'PAID', 'paid_at' => now()]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot mark order with status PAID as paid');

        $this->service->markOrderAsPaid($order);
    }

    /**
     * Test cancelling order
     */
    public function test_can_cancel_order()
    {
        $order = Order::factory()
            ->create(['status' => 'NEW']);

        $result = $this->service->cancelOrder($order, 'Test reason');

        $this->assertTrue($result);

        $order->refresh();
        $this->assertEquals('CANCELLED', $order->status);
        $this->assertNotNull($order->cancelled_at);
    }

    /**
     * Test cannot cancel paid order
     */
    public function test_cannot_cancel_paid_order()
    {
        $order = Order::factory()
            ->create(['status' => 'PAID', 'paid_at' => now()]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot cancel order with status PAID');

        $this->service->cancelOrder($order);
    }

    /**
     * Test cannot cancel already cancelled order
     */
    public function test_cannot_cancel_already_cancelled_order()
    {
        $order = Order::factory()
            ->create(['status' => 'CANCELLED', 'cancelled_at' => now()]);

        $result = $this->service->cancelOrder($order);

        $this->assertFalse($result);
    }

    /**
     * Test creating refund request
     */
    public function test_can_create_refund_request()
    {
        $order = Order::factory()
            ->create([
                'status' => 'PAID',
                'paid_at' => now(),
                'total_amount' => 1000000,
            ]);

        $refund = $this->service->createRefundRequest(
            $order,
            500000,
            'Customer requested'
        );

        $this->assertNotNull($refund);
        $this->assertEquals(500000, $refund->amount);
        $this->assertEquals('PENDING', $refund->status);
    }

    /**
     * Test cannot refund unpaid order
     */
    public function test_cannot_refund_unpaid_order()
    {
        $order = Order::factory()->create(['status' => 'NEW']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Can only refund PAID orders');

        $this->service->createRefundRequest($order, 500000);
    }

    /**
     * Test cannot refund with invalid amount
     */
    public function test_cannot_refund_with_invalid_amount()
    {
        $order = Order::factory()
            ->create([
                'status' => 'PAID',
                'paid_at' => now(),
                'total_amount' => 1000000,
            ]);

        $this->expectException(\Exception::class);

        // Amount exceeds total
        $this->service->createRefundRequest($order, 5000000);
    }

    /**
     * Test get order statistics
     */
    public function test_can_get_order_statistics()
    {
        Order::factory(5)->create([
            'event_id' => $this->event->id,
            'status' => 'PAID',
            'paid_at' => now(),
            'total_amount' => 1000000,
        ]);

        Order::factory(3)->create([
            'event_id' => $this->event->id,
            'status' => 'NEW',
            'total_amount' => 500000,
        ]);

        $stats = $this->service->getOrderStats(['event_id' => $this->event->id]);

        $this->assertEquals(8, $stats['total_orders']);
        $this->assertEquals(5, $stats['paid_orders']);
        $this->assertEquals(3, $stats['unpaid_orders']);
        $this->assertGreaterThan(0, $stats['total_revenue']);
        $this->assertGreaterThan(0, $stats['payment_success_rate']);
    }

    /**
     * Test filtering by payment status
     */
    public function test_can_filter_by_payment_status()
    {
        Order::factory(5)->create([
            'event_id' => $this->event->id,
            'status' => 'PAID',
            'paid_at' => now(),
        ]);

        Order::factory(3)->create([
            'event_id' => $this->event->id,
            'status' => 'NEW',
        ]);

        // Get paid orders
        $paidOrders = $this->service->getOrdersWithFilters(
            [
                'event_id' => $this->event->id,
                'payment_status' => 'paid',
            ],
            15
        );

        $this->assertCount(5, $paidOrders);
    }

    /**
     * Test get top promo codes
     */
    public function test_can_get_top_promo_codes()
    {
        $promoCode = PromoCode::factory()
            ->create(['discount_percent' => 10]);

        Order::factory(5)->create([
            'event_id' => $this->event->id,
            'promo_code_id' => $promoCode->id,
            'status' => 'PAID',
            'discount_amount' => 100000,
        ]);

        $topCodes = $this->service->getTopPromoCodes(10);

        $this->assertGreaterThan(0, count($topCodes));
    }
}
