# Xendit Payment Testing Flow

1. Create order via `POST /api/package/order` with `package_id` and `payment_method`.
2. Capture `checkout_url` from response.
3. Open `checkout_url` in browser and complete payment.
4. Xendit sends webhook to `POST /api/xendit/webhook`.
5. Verify payment status becomes `paid` and order status becomes `paid`.

Notes:
- Make sure `XENDIT_SECRET_KEY` and `XENDIT_CALLBACK_TOKEN` are set in `.env`.
- Use Xendit sandbox payment flow for testing.
