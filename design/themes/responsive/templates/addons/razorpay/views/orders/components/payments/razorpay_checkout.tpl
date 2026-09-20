<!DOCTYPE html>
<html lang="{$smarty.const.CART_LANGUAGE|default:'en'}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{__("checkout")} - {__("order")} #{$order_id}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
        }
        .rzp-checkout-wrapper {
            width: 100%;
            max-width: 460px;
        }
        .rzp-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04), 0 0 1px 1px rgba(15, 23, 42, 0.05);
            padding: 36px 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .rzp-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #0c2340 0%, #3399cc 50%, #0284c7 100%);
        }
        .rzp-logo-area {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 24px;
        }
        .rzp-badge-icon {
            width: 44px;
            height: 44px;
            background: #f0f9ff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0284c7;
        }
        .rzp-brand-text {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.02em;
        }
        .rzp-order-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 24px;
            text-align: left;
        }
        .rzp-order-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            font-size: 14px;
        }
        .rzp-order-row:not(:last-child) {
            border-bottom: 1px dashed #e2e8f0;
        }
        .rzp-order-label {
            color: #64748b;
            font-weight: 500;
        }
        .rzp-order-value {
            color: #0f172a;
            font-weight: 600;
        }
        .rzp-order-total {
            font-size: 22px;
            font-weight: 700;
            color: #0c2340;
        }
        .rzp-spinner-container {
            margin: 24px 0 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .rzp-spinner {
            width: 44px;
            height: 44px;
            border: 3.5px solid #e2e8f0;
            border-top-color: {$checkout_options.theme.color|default:'#3399cc'};
            border-radius: 50%;
            animation: rzpSpin 0.9s cubic-bezier(0.55, 0.15, 0.45, 0.85) infinite;
        }
        @keyframes rzpSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .rzp-status-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin-top: 14px;
            margin-bottom: 6px;
            line-height: 1.4;
        }
        .rzp-status-desc {
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
            max-width: 320px;
            margin: 0 auto;
        }
        .rzp-btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 14px 24px;
            margin-top: 18px;
            font-size: 15px;
            font-weight: 600;
            color: #ffffff;
            background-color: {$checkout_options.theme.color|default:'#3399cc'};
            border: none;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(51, 153, 204, 0.25);
            transition: all 0.2s ease;
        }
        .rzp-btn-action:hover {
            opacity: 0.92;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(51, 153, 204, 0.35);
        }
        .rzp-btn-action:active {
            transform: translateY(0);
        }
        .rzp-btn-action:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .rzp-cancel-wrapper {
            margin-top: 16px;
        }
        .rzp-cancel-link {
            font-size: 13px;
            font-weight: 500;
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .rzp-cancel-link:hover {
            color: #475569;
            text-decoration: underline;
        }
        .rzp-security-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid #f1f5f9;
            font-size: 12px;
            color: #94a3b8;
        }
        .rzp-security-icon {
            width: 14px;
            height: 14px;
            fill: currentColor;
        }
    </style>
</head>
<body>
    <div class="rzp-checkout-wrapper">
        <div class="rzp-card">
            <!-- Header Brand -->
            <div class="rzp-logo-area">
                <div class="rzp-badge-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <div class="rzp-brand-text">{$checkout_options.name|default:$settings.Company.company_name|escape}</div>
            </div>

            <!-- Order Summary Details -->
            <div class="rzp-order-box">
                <div class="rzp-order-row">
                    <span class="rzp-order-label">{__("order")}</span>
                    <span class="rzp-order-value">#{$order_id}</span>
                </div>
                {if !empty($checkout_options.prefill.name)}
                <div class="rzp-order-row">
                    <span class="rzp-order-label">{__("customer")}</span>
                    <span class="rzp-order-value">{$checkout_options.prefill.name|escape}</span>
                </div>
                {/if}
                <div class="rzp-order-row">
                    <span class="rzp-order-label">{__("total")}</span>
                    <span class="rzp-order-value rzp-order-total">
                        {$checkout_options.currency} {($checkout_options.amount / 100)|number_format:2}
                    </span>
                </div>
            </div>

            <!-- Spinner & Live Status Notification -->
            <div class="rzp-spinner-container">
                <div class="rzp-spinner" id="rzp-spinner"></div>
                <div class="rzp-status-title" id="rzp-status-title">{__("razorpay.opening_modal_notice")}</div>
                <div class="rzp-status-desc" id="rzp-status-desc">
                    {__("razorpay.connecting_notice")}. {__("please_be_patient")}
                </div>
            </div>

            <!-- Manual Open Fallback Button -->
            <button type="button" class="rzp-btn-action" id="rzp-pay-button">
                {__("razorpay.pay_now")}
            </button>

            <!-- Cancel & Return Option -->
            <div class="rzp-cancel-wrapper">
                <a href="{$cancel_url}" class="rzp-cancel-link" id="rzp-cancel-link">
                    &larr; {__("razorpay.cancel_and_return")}
                </a>
            </div>

            <!-- PCI-DSS / 256-Bit SSL Footer -->
            <div class="rzp-security-footer">
                <svg class="rzp-security-icon" viewBox="0 0 24 24">
                    <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                </svg>
                <span>{__("razorpay.secure_256_encryption")}</span>
            </div>
        </div>
    </div>

    <!-- Razorpay Checkout Official Library -->
    <script src="https://checkout.razorpay.com/v1/checkout.js" data-no-defer="true"></script>

    <!-- Context Variables from CS-Cart Smarty -->
    <script data-no-defer="true">
        var rzpCheckoutOptions = {$json_options nofilter};
        var rzpReturnUrl = "{$return_url|escape:javascript nofilter}";
        var rzpCancelUrl = "{$cancel_url|escape:javascript nofilter}";
    </script>

    <!-- Razorpay Lifecycle Execution -->
    {literal}
    <script data-no-defer="true">
        (function() {
            var isSubmitted = false;
            var rzp = null;

            var titleEl = document.getElementById('rzp-status-title');
            var descEl = document.getElementById('rzp-status-desc');
            var btnEl = document.getElementById('rzp-pay-button');
            var spinnerEl = document.getElementById('rzp-spinner');

            // Success Callback: Invoked by Razorpay modal upon completed authorization
            rzpCheckoutOptions.handler = function(response) {
                if (isSubmitted) {
                    return;
                }
                isSubmitted = true;

                if (titleEl) {
                    titleEl.textContent = 'Payment Authorized!';
                }
                if (descEl) {
                    descEl.textContent = 'Verifying signature and completing your order. Please do not refresh...';
                }
                if (btnEl) {
                    btnEl.disabled = true;
                    btnEl.textContent = 'Verifying...';
                }
                if (spinnerEl) {
                    spinnerEl.style.display = 'block';
                }

                // Construct hidden POST form to submit parameters to CS-Cart ReturnHandler
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = rzpReturnUrl;
                form.style.display = 'none';

                var payload = {
                    'razorpay_payment_id': response.razorpay_payment_id || '',
                    'razorpay_order_id': response.razorpay_order_id || rzpCheckoutOptions.order_id || '',
                    'razorpay_signature': response.razorpay_signature || ''
                };

                for (var param in payload) {
                    if (payload.hasOwnProperty(param)) {
                        var field = document.createElement('input');
                        field.type = 'hidden';
                        field.name = param;
                        field.value = payload[param];
                        form.appendChild(field);
                    }
                }

                document.body.appendChild(form);
                form.submit();
            };

            // Dismiss Callback: Triggered if the customer closes the payment popup
            rzpCheckoutOptions.modal = rzpCheckoutOptions.modal || {};
            rzpCheckoutOptions.modal.ondismiss = function() {
                if (isSubmitted) {
                    return;
                }
                if (titleEl) {
                    titleEl.textContent = 'Payment window was closed';
                }
                if (descEl) {
                    descEl.textContent = 'You closed the payment dialog without completing the transaction. Click below to try again or return to your cart.';
                }
                if (btnEl) {
                    btnEl.textContent = 'Try Again';
                    btnEl.disabled = false;
                }
                if (spinnerEl) {
                    spinnerEl.style.display = 'none';
                }
            };

            // Initialize Razorpay SDK instance
            function initAndOpen() {
                if (typeof Razorpay === 'undefined') {
                    if (titleEl) {
                        titleEl.textContent = 'Gateway Loading Error';
                        titleEl.style.color = '#dc2626';
                    }
                    if (descEl) {
                        descEl.textContent = 'Unable to load Razorpay checkout. Please disable content blockers or check your internet connection.';
                    }
                    if (spinnerEl) {
                        spinnerEl.style.display = 'none';
                    }
                    return;
                }

                if (!rzp) {
                    try {
                        rzp = new Razorpay(rzpCheckoutOptions);
                        rzp.on('payment.failed', function(response) {
                            if (titleEl) {
                                titleEl.textContent = 'Payment Failed';
                                titleEl.style.color = '#dc2626';
                            }
                            if (descEl) {
                                descEl.textContent = (response.error && response.error.description) 
                                    ? response.error.description 
                                    : 'The transaction could not be completed. Please try another payment method.';
                            }
                            if (btnEl) {
                                btnEl.textContent = 'Retry Payment';
                                btnEl.disabled = false;
                            }
                            if (spinnerEl) {
                                spinnerEl.style.display = 'none';
                            }
                        });
                    } catch (e) {
                        console.error('Razorpay initialization error:', e);
                        return;
                    }
                }

                try {
                    rzp.open();
                } catch (e) {
                    console.error('Failed to open Razorpay modal:', e);
                }
            }

            // Auto-launch checkout modal (unless explicitly disabled)
            if (rzpCheckoutOptions.auto_open !== false) {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initAndOpen);
                } else {
                    initAndOpen();
                }
            }

            // Click listener for manual fallback button
            if (btnEl) {
                btnEl.addEventListener('click', function(e) {
                    e.preventDefault();
                    initAndOpen();
                });
            }
        })();
    </script>
    {/literal}
</body>
</html>
