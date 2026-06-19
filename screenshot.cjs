const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
    const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await context.newPage();

    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'domcontentloaded', timeout: 15000 });

    await page.locator('input[type="email"]').fill('admin@vet.com');
    await page.locator('input[type="password"]').fill('password');
    await page.locator('button[type="submit"]').click();

    await page.waitForTimeout(5000);

    await page.screenshot({ path: 'public/screenshot-dashboard.png', fullPage: true });

    await browser.close();
    console.log('DONE');
})();
