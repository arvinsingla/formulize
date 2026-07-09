// Verify sticky filter row (issue #10): the filter search row should stay
// pinned below the column-header row while the data rows scroll, on both
// desktop and mobile viewports. The bottom footer should also stay visible.
// Run from tests/e2e: node verify-sticky-filters.mjs
import { chromium } from 'playwright';

const base = 'http://localhost:8080';
const browser = await chromium.launch();

async function login(page) {
  await page.goto(base + '/user.php');
  await page.locator('input[name="uname"]').fill('admin');
  await page.locator('input[name="pass"]').fill('password');
  await Promise.all([
    page.waitForURL(/\/modules\/formulize\/.*/),
    page.locator('input[name="pass"]').press('Enter'),
  ]);
}

async function verifySticky(page, label) {
  // Navigate to the Donors list (sid=4)
  await page.goto(base + '/modules/formulize/index.php?sid=4');
  await page.waitForLoadState('networkidle');

  // Enable filters if there's a toggle button
  const toggleBtn = page.locator('#fz-filter-toggle');
  const hasToggle = await toggleBtn.count() > 0;
  if (hasToggle) {
    // Show filter row
    await toggleBtn.click();
    await page.waitForTimeout(300);
  }

  // Check that filter row exists and is visible
  const filterRow = page.locator('.fz-search-row');
  const filterRowCount = await filterRow.count();
  console.log(`[${label}] .fz-search-row count: ${filterRowCount}`);

  if (filterRowCount === 0) {
    console.log(`[${label}] No filter row found — skipping sticky check`);
    return;
  }

  // Check the filter row cells have position:sticky
  const filterCellSticky = await page.evaluate(() => {
    const cell = document.querySelector('.fz-search-row td');
    if (!cell) return null;
    const style = window.getComputedStyle(cell);
    return { position: style.position, top: style.top, zIndex: style.zIndex };
  });
  console.log(`[${label}] .fz-search-row td computed style:`, filterCellSticky);

  // Scroll the list body to the bottom and verify filter row is still visible
  const listBody = page.locator('.fz-list__body');
  const listBodyCount = await listBody.count();
  if (listBodyCount > 0) {
    await listBody.evaluate(el => { el.scrollTop = el.scrollHeight; });
    await page.waitForTimeout(300);
  }

  // Check viewport positions after scrolling
  const positions = await page.evaluate(() => {
    const filterRow = document.querySelector('.fz-search-row');
    const headerRow = document.querySelector('.fz-table thead tr:first-child');
    const footer = document.querySelector('.fz-list__footer');
    const body = document.querySelector('.fz-list__body');

    const filterCell = filterRow ? filterRow.querySelector('td') : null;

    return {
      filterRowHidden: filterRow ? filterRow.hasAttribute('hidden') : true,
      headerRowBottom: headerRow ? headerRow.getBoundingClientRect().bottom : null,
      filterCellTop: filterCell ? filterCell.getBoundingClientRect().top : null,
      filterCellBottom: filterCell ? filterCell.getBoundingClientRect().bottom : null,
      footerTop: footer ? footer.getBoundingClientRect().top : null,
      bodyScrollTop: body ? body.scrollTop : null,
      bodyScrollHeight: body ? body.scrollHeight : null,
    };
  });
  console.log(`[${label}] positions after scroll:`, positions);

  // Verify: filter row top should be at or near header row bottom (sticky)
  if (positions.filterCellTop !== null && positions.headerRowBottom !== null) {
    const gap = Math.abs(positions.filterCellTop - positions.headerRowBottom);
    const isSticky = gap < 5; // within 5px
    console.log(`[${label}] Filter row sticky below header: ${isSticky ? 'YES' : 'NO'} (gap=${gap.toFixed(1)}px)`);
  }

  // Take screenshot
  await page.screenshot({ path: `sticky-filters-${label.toLowerCase().replace(/\s/g, '-')}.png`, fullPage: false });
  console.log(`[${label}] Screenshot saved`);
}

// Desktop
const desktopPage = await browser.newPage({ viewport: { width: 1280, height: 800 } });
await login(desktopPage);
await verifySticky(desktopPage, 'desktop');
await desktopPage.close();

// Mobile (390px)
const mobilePage = await browser.newPage({ viewport: { width: 390, height: 844 } });
await login(mobilePage);
await verifySticky(mobilePage, 'mobile-390');
await mobilePage.close();

await browser.close();
console.log('done');
