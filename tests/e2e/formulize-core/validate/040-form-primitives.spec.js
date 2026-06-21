const { test, expect } = require('@playwright/test');
import { login } from '../../utils';

// Verification for the Lyris form-screen .fz-* primitive adoption.
// Confirms the design-system vocabulary is emitted and the compact density
// resolves to the expected control sizing on a real data-entry screen.
test.describe('Lyris form-screen primitives', () => {
	test('data-entry screen emits .fz-field / .fz-control primitives', async ({ page }) => {
		await login(page, 'admin', 'password');
		await page.goto('/modules/formulize/index.php?sid=1');
		await page.waitForLoadState('networkidle');

		// Container modifiers (label-top + compact) present.
		await expect(page.locator('.fz-form.fz-form--label-top.fz-form--compact')).toHaveCount(1);

		// Field wrapper + label + body primitives emitted.
		expect(await page.locator('.fz-field').count()).toBeGreaterThan(0);
		expect(await page.locator('.fz-field__label').count()).toBeGreaterThan(0);
		expect(await page.locator('.fz-field__body').count()).toBeGreaterThan(0);

		// Required field renders the asterisk primitive (not an inline red span).
		expect(await page.locator('.fz-field__req').count()).toBeGreaterThan(0);
		expect(await page.locator(".fz-field__label span[style*='color: red']").count()).toBe(0);

		// Controls inside the form get the compact density: ~32px height.
		const firstInput = page.locator(".fz-form input[type='text']:visible").first();
		await expect(firstInput).toBeVisible();
		const h = await firstInput.evaluate((el) => Math.round(el.getBoundingClientRect().height));
		expect(h).toBeGreaterThanOrEqual(30);
		expect(h).toBeLessThanOrEqual(34);
	});
});
