# Changelog

All notable changes to the **Razorpay Payment Gateway for CS-Cart** add-on will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] - 2026-09-21

### Added
- **Scheme 4.0 Manifest**: Registered add-on manifest (`addon.xml`) with native CS-Cart 4.x compatibility, priority configuration, and PSR-4 namespace autoloading (`Tygh\Addons\Razorpay\` pointing to `src/`).
- **Low-Level Design (LLD) Architecture**:
  - **Strategy Pattern**: Payment modes segregated into independent single-responsibility strategies (`InitiateHandler`, `ReturnHandler`, `CancelHandler`, `WebhookHandler`) implementing `PaymentModeHandlerInterface`.
  - **Factory Pattern**: Dynamic mode resolver `PaymentModeHandlerFactory` with extensible runtime registration via `registerStrategy()`.
  - **Facade Pattern**: High-level dispatcher `PaymentHandler` coordinating execution context and delegating cleanly to resolved mode strategies.
  - **Adapter Pattern**: External REST API client `RazorpayApi` implementing `PaymentApiInterface` with HTTP Basic Authentication, cURL transport, timeout handling, and JSON response parsing.
  - **Repository Pattern**: Persistence abstraction layer with `TransactionRepository` and `WebhookLogRepository` implementing `TransactionRepositoryInterface` and `WebhookLogRepositoryInterface`.
  - **Builder Pattern**: Fluent `QueryBuilder` supporting SQL `JOIN`s, `WHERE IN`, `GROUP BY`, `HAVING`, and aggregate operations without raw string query concatenation.
  - **Active Record Model Layer**: Parameterized database models (`BaseModel`, `TransactionModel`, `WebhookLogModel`, `PaymentProcessorModel`, `PaymentModel`) utilizing native CS-Cart placeholders (`?s`, `?i`, `?d`, `?a`).
  - **Data Transfer Objects (DTO)**: Immutable `PaymentContext` and `RazorpayOrderResult` value objects replacing loose associative arrays.
  - **Observer Pattern**: Event listeners `PaymentsHookHandler` and `OrdersHookHandler` subscribing to `update_payment_post`, `get_payment_processors_post`, and `finish_payment`.
  - **Dependency Injection**: Pimple DI container integration in `ServiceProvider` registering all repositories, services, and handlers.
- **Standalone Database Schemas**:
  - `database/install.sql`: Idempotent SQL deployment executing `DROP TABLE IF EXISTS`, creating `?:razorpay_transactions` and `?:razorpay_webhook_logs` with proper indexes, and seeding `?:payment_processors`.
  - `database/uninstall.sql`: Clean teardown script dropping custom tables and removing processor registrations.
- **Front-end Checkout Experience**:
  - `razorpay_checkout.tpl`: Responsive, glassmorphic presentation card with merchant branding, order summary, animated dual-ring CSS loader, and trust badge.
  - Razorpay standard checkout library integration (`checkout.js`) with `data-no-defer="true"` to prevent CS-Cart script stripping.
  - Automatic `rzp.open()` launch on DOM ready with manual fallback "Pay Now" button in case of popup blockers.
  - Dynamic client-side POST form generation on payment authorization callback.
  - User dismissal handling with card recovery and clean return to checkout via `CancelHandler`.
- **Payment Method Details Enrichment**: Automatic retrieval of payment method details (card brand, last 4 digits, UPI handle, bank name) via Razorpay API stored directly in CS-Cart order payment information.
- **Asynchronous Webhooks**:
  - Automated event handling for `order.paid`, `payment.authorized`, `payment.captured`, and `payment.failed`.
  - Webhook replay protection and deduplication using `event_id`.
  - Complete raw payload logging into `?:razorpay_webhook_logs`.
- **Admin Configuration Interface**:
  - Admin template `razorpay.tpl` for payment processor settings.
  - Configurable parameters: Key ID, Key Secret, Webhook Secret, Payment Action (Capture vs Authorize), Currency conversion, Theme Color (HEX), and custom Order Status mapping.
- **Internationalization & Localization**:
  - English translations catalog (`var/langs/en/addons/razorpay.po`) for all admin and customer-facing UI strings.
  - Added localized strings for `razorpay.cancel_and_return` ("Cancel and return to cart") and `return_to_cart`.

### Security
- **Timing-Safe Verification**: Verification of payment and webhook signatures using PHP's constant-time `hash_equals()` over HMAC-SHA256 digests.
- **PCI-DSS Compliance**: Offloaded payment card capture to Razorpay's PCI-certified iframe modal; zero cardholder data touches the merchant server.
- **SQL Injection Defense**: 100% parameterized database queries utilizing native CS-Cart placeholder escaping throughout all Model and Repository classes.
