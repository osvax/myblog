import { test, expect } from '@playwright/test';

test.describe('Home Page', () => {
  test('should load home page successfully', async ({ page }) => {
    const response = await page.goto('/');
    expect(response?.status()).toBe(200);
  });

  test('should have correct title', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/МойБлог|MyBlog|Блог/);
  });

  test('should have navigation', async ({ page }) => {
    await page.goto('/');
    const nav = page.locator('nav, header nav, .navbar, .navigation').first();
    await expect(nav).toBeVisible();
  });

  test('should have hero section', async ({ page }) => {
    await page.goto('/');
    const hero = page.locator('section.hero, .hero, #hero, main > section:first-child').first();
    await expect(hero).toBeVisible();
  });
});