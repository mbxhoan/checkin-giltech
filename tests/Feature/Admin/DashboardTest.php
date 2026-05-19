<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_sys_admin_dashboard_renders_new_summary_layout(): void
    {
        $admin = $this->admin([
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Tổng quan điều hành hệ thống')
            ->assertSee('Luồng thao tác nhanh')
            ->assertSee('Khách mời được đăng ký/nhập gần đây');
    }
}
