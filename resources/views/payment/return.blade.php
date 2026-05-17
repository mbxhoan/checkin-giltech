@extends('web.layouts.app', [
    'pageTitle' => $title . ' | ' . config('app.name', 'Laravel'),
])

@push('web_css')
    <style>
        :root {
            --payment-bg: #07111f;
            --payment-surface: rgba(255, 255, 255, 0.92);
            --payment-surface-strong: #ffffff;
            --payment-text: #0f172a;
            --payment-muted: #64748b;
            --payment-border: rgba(15, 23, 42, 0.08);
            --payment-success: #16a34a;
            --payment-success-soft: rgba(22, 163, 74, 0.12);
            --payment-accent: #0f766e;
            --payment-shadow: 0 32px 80px rgba(2, 8, 23, 0.22);
        }

        body.web-body {
            background-color: #f6f9fb !important;
            background-image:
                radial-gradient(circle at top, rgba(45, 212, 191, 0.22), transparent 34%),
                radial-gradient(circle at bottom right, rgba(59, 130, 246, 0.18), transparent 30%),
                linear-gradient(180deg, #edf7f6 0%, #f6f9fb 100%) !important;
        }

        .payment-return-screen {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 24px 0;
        }

        .payment-return-shell {
            width: min(1180px, calc(100vw - 32px));
            margin: 0 auto;
            position: relative;
        }

        .payment-return-shell::before,
        .payment-return-shell::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            filter: blur(12px);
            pointer-events: none;
        }

        .payment-return-shell::before {
            width: 220px;
            height: 220px;
            background: rgba(22, 163, 74, 0.12);
            top: -52px;
            left: -24px;
        }

        .payment-return-shell::after {
            width: 180px;
            height: 180px;
            background: rgba(14, 165, 233, 0.12);
            right: -20px;
            bottom: -42px;
        }

        .payment-return-card {
            position: relative;
            z-index: 1;
            background: var(--payment-surface);
            backdrop-filter: blur(18px);
            border: 1px solid var(--payment-border);
            border-radius: 28px;
            box-shadow: var(--payment-shadow);
            overflow: hidden;
        }

        .payment-return-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr);
            gap: 0;
        }

        .payment-hero {
            padding: 42px;
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.96) 0%, rgba(247, 255, 250, 0.94) 100%);
            border-right: 1px solid rgba(15, 23, 42, 0.06);
        }

        .payment-hero-copy {
            max-width: 560px;
        }

        .payment-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 18px;
        }

        .payment-badge.is-success {
            color: var(--payment-success);
            background: var(--payment-success-soft);
        }

        .payment-badge.is-pending {
            color: #b45309;
            background: rgba(245, 158, 11, 0.14);
        }

        .payment-mark {
            width: 88px;
            height: 88px;
            border-radius: 28px;
            display: grid;
            place-items: center;
            margin-bottom: 22px;
            background: linear-gradient(145deg, rgba(22, 163, 74, 0.18), rgba(15, 118, 110, 0.12));
            color: var(--payment-success);
            box-shadow: inset 0 0 0 1px rgba(22, 163, 74, 0.12);
        }

        .payment-mark svg {
            width: 44px;
            height: 44px;
        }

        .payment-hero h1 {
            font-size: clamp(30px, 3.4vw, 48px);
            line-height: 1.08;
            font-weight: 800;
            letter-spacing: -0.04em;
            color: var(--payment-text);
            margin: 0 0 16px;
        }

        .payment-hero p {
            color: var(--payment-muted);
            font-size: 16px;
            line-height: 1.8;
            margin: 0 0 28px;
        }

        .payment-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
        }

        .payment-close-btn {
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, #0f766e 0%, #16a34a 100%);
            color: #fff;
            font-weight: 700;
            padding: 14px 20px;
            box-shadow: 0 14px 24px rgba(15, 118, 110, 0.24);
        }

        .payment-close-btn:hover {
            filter: brightness(1.03);
        }

        .payment-countdown {
            color: var(--payment-muted);
            font-size: 14px;
        }

        .payment-countdown strong {
            color: var(--payment-text);
            font-size: 18px;
        }

        .payment-manual-note {
            margin-top: 14px;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(15, 23, 42, 0.04);
            color: var(--payment-muted);
            font-size: 14px;
            line-height: 1.7;
        }

        .payment-summary {
            padding: 42px;
            background: rgba(255, 255, 255, 0.7);
        }

        .summary-card {
            height: 100%;
            border-radius: 24px;
            background: var(--payment-surface-strong);
            border: 1px solid rgba(15, 23, 42, 0.06);
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
            padding: 24px;
        }

        .summary-heading {
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--payment-muted);
            margin: 0 0 16px;
        }

        .summary-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 14px;
        }

        .summary-item {
            padding: 14px 0;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        }

        .summary-item:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .summary-label {
            display: block;
            color: var(--payment-muted);
            font-size: 13px;
            margin-bottom: 5px;
        }

        .summary-value {
            display: block;
            color: var(--payment-text);
            font-size: 16px;
            font-weight: 700;
            word-break: break-word;
        }

        .summary-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
        }

        .summary-status.is-success {
            color: var(--payment-success);
            background: var(--payment-success-soft);
        }

        .summary-status.is-pending {
            color: #b45309;
            background: rgba(245, 158, 11, 0.14);
        }

        .summary-footnote {
            margin-top: 18px;
            color: var(--payment-muted);
            font-size: 13px;
            line-height: 1.7;
        }

        @media (max-width: 991.98px) {
            .payment-return-screen {
                padding: 16px 0;
            }

            .payment-return-grid {
                grid-template-columns: 1fr;
            }

            .payment-hero {
                border-right: 0;
                border-bottom: 1px solid rgba(15, 23, 42, 0.06);
            }
        }

        @media (max-width: 575.98px) {
            .payment-return-shell {
                width: calc(100vw - 20px);
            }

            .payment-hero,
            .payment-summary {
                padding: 24px 18px;
            }

            .payment-return-card {
                border-radius: 22px;
            }

            .payment-close-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $isSuccess = $state === 'success';
        $statusClass = $isSuccess ? 'is-success' : 'is-pending';
        $statusText = $isSuccess ? 'Thanh toán thành công' : 'Đang xác nhận giao dịch';
        $summaryText = $isSuccess ? 'Giao dịch đã được ghi nhận' : 'Hệ thống đang đồng bộ';
    @endphp

    <div class="payment-return-screen">
        <div class="payment-return-shell">
            <div class="payment-return-card">
                <div class="payment-return-grid">
                    <section class="payment-hero">
                        <div class="payment-hero-copy">
                            <div class="payment-badge {{ $statusClass }}">
                                <span>{{ $statusText }}</span>
                            </div>

                            <div class="payment-mark" aria-hidden="true">
                                <svg viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="26" cy="26" r="24" stroke="currentColor" stroke-width="3" opacity="0.18" />
                                    <path d="M16 26.5L23.2 33.7L36 20.5" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <h1>{{ $title }}</h1>
                            <p>{{ $message }}</p>

                            <div class="payment-actions">
                                <button type="button" class="payment-close-btn" data-close-window>
                                    Đóng cửa sổ ngay
                                </button>
                                <div class="payment-countdown">
                                    Tự đóng sau <strong data-countdown data-seconds="{{ $countdownSeconds }}">{{ $countdownSeconds }}</strong> giây
                                </div>
                            </div>

                            <div class="payment-manual-note" data-manual-close-note>
                                Nếu tab này không tự tắt, vui lòng đóng thủ công. Một số trình duyệt chỉ cho phép `window.close()` khi tab được mở từ cửa sổ khác.
                            </div>
                        </div>
                    </section>

                    <aside class="payment-summary">
                        <div class="summary-card">
                            <div class="summary-heading">{{ $summaryText }}</div>

                            <ul class="summary-list">
                                <li class="summary-item">
                                    <span class="summary-label">Trạng thái</span>
                                    <span class="summary-value">
                                        <span class="summary-status {{ $statusClass }}">{{ $statusText }}</span>
                                    </span>
                                </li>

                                <li class="summary-item">
                                    <span class="summary-label">Mã giao dịch</span>
                                    <span class="summary-value">{{ $txnRef ?? 'N/A' }}</span>
                                </li>

                                <li class="summary-item">
                                    <span class="summary-label">Mã đơn hàng</span>
                                    <span class="summary-value">{{ $order?->no ?? 'Chưa có thông tin' }}</span>
                                </li>

                                <li class="summary-item">
                                    <span class="summary-label">Mã đăng ký</span>
                                    <span class="summary-value">{{ $registration?->code ?? 'Chưa có thông tin' }}</span>
                                </li>

                                <li class="summary-item">
                                    <span class="summary-label">Số tiền</span>
                                    <span class="summary-value">{{ $amountDisplay }}</span>
                                </li>
                            </ul>

                            <div class="summary-footnote">
                                Chúng tôi sẽ tiếp tục đồng bộ hoá đơn, QR và trạng thái đơn hàng trong nền.
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var countdownEl = document.querySelector('[data-countdown]');
            var manualNote = document.querySelector('[data-manual-close-note]');
            var closeButton = document.querySelector('[data-close-window]');
            var remaining = parseInt(countdownEl?.dataset.seconds || '8', 10);

            function attemptClose() {
                if (manualNote) {
                    manualNote.classList.remove('d-none');
                }

                try {
                    window.close();
                } catch (error) {
                    // Ignore browser restrictions.
                }
            }

            function tick() {
                if (countdownEl) {
                    countdownEl.textContent = String(Math.max(remaining, 0));
                }

                if (remaining <= 0) {
                    attemptClose();
                    return;
                }

                remaining -= 1;
                window.setTimeout(tick, 1000);
            }

            if (closeButton) {
                closeButton.addEventListener('click', attemptClose);
            }

            if (manualNote) {
                manualNote.classList.add('d-none');
            }

            tick();
        });
    </script>
@endpush
