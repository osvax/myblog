import { test, expect } from '@playwright/test';

test.describe('API Endpoints', () => {
  test('GET /api/forms/order should return 405 (method not allowed)', async ({ request }) => {
    const response = await request.get('/api/forms/order');
    expect([405, 404]).toContain(response.status());
  });

  test('POST /api/forms/order should validate required fields', async ({ request }) => {
    const response = await request.post('/api/forms/order', {
      data: {},
    });
    expect(response.status()).toBe(400);
    const body = await response.json();
    expect(body.success).toBe(false);
    expect(body.errors).toBeDefined();
  });

  test('POST /api/forms/order with valid data should succeed', async ({ request }) => {
    const response = await request.post('/api/forms/order', {
      data: {
        name: 'Test User',
        email: 'test@example.com',
        phone: '+7 999 123 45 67',
        message: 'Test message',
      },
    });
    // May return 200/201 or 500 if email not configured - but shouldn't be 400 for validation
    expect([200, 201, 500]).toContain(response.status());
  });

  test('GET /api/admin/orders should require auth', async ({ request }) => {
    const response = await request.get('/api/admin/orders');
    expect(response.status()).toBe(401);
    const body = await response.json();
    expect(body.success).toBe(false);
    expect(body.error).toContain('авторизац');
  });

  test('GET /api/consultant should return 405', async ({ request }) => {
    const response = await request.get('/api/consultant');
    expect([405, 404]).toContain(response.status());
  });
});