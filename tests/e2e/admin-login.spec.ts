import { test, expect } from '@playwright/test';

test.describe('Admin Login', () => {
  test('should load login page', async ({ page }) => {
    const response = await page.goto('/admin/login.html');
    expect(response?.status()).toBe(200);
  });

  test('should show login form', async ({ page }) => {
    await page.goto('/admin/login.html');
    await expect(page.locator('form')).toBeVisible();
    await expect(page.locator('input[name="email"], input[type="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"], input[type="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"], input[type="submit"]')).toBeVisible();
  });

  test('should redirect to login when accessing admin without auth', async ({ page }) => {
    const response = await page.goto('/admin/');
    // Should redirect to login or show login page
    expect(response?.status()).toBeLessThan(400);
    // Check if we're on login page or redirected
    await expect(page).toHaveURL(/.*login/);
  });
});