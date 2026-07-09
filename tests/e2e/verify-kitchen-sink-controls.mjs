// Verify second-pass kitchen-sink control styling on the Lyris form screen:
//   • Autocomplete List  (.formulize_autocomplete input + .auto_multi chips + ui-menu)
//   • Multi-select Listbox (<select multiple>)
//   • Native <select> dropdown full-width (<nobr> wrapper defeated)
//
// Run from tests/e2e:  node verify-kitchen-sink-controls.mjs
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

const results = [];
function check(name, cond, detail) {
  results.push({ name, pass: !!cond, detail });
  console.log(`${cond ? 'PASS' : 'FAIL'}  ${name}${detail ? '  — ' + detail : ''}`);
}

async function run(width, tag) {
  const ctx = await browser.newContext({ viewport: { width, height: 1000 } });
  const page = await ctx.newPage();
  await login(page);

  // Exhibit form (sid=7): autocompleteLinked + native <select> dropdown + proxyuser multi-select
  await page.goto(base + '/modules/formulize/index.php?sid=7');
  await page.waitForLoadState('networkidle');

  // --- Autocomplete input styled as a bordered combo field ---
  const acInput = page.locator('input.formulize_autocomplete').first();
  if (await acInput.count()) {
    const s = await acInput.evaluate(el => {
      const c = getComputedStyle(el);
      return { br: c.borderRadius, bw: c.borderTopWidth, h: c.height, prPad: c.paddingRight, w: el.getBoundingClientRect().width };
    });
    check(`[${tag}] autocomplete input has rounded border`, parseFloat(s.br) >= 4 && parseFloat(s.bw) >= 1, `radius=${s.br} border=${s.bw}`);
    check(`[${tag}] autocomplete input reserves room for icon`, parseFloat(s.prPad) >= 30, `padding-right=${s.prPad}`);
    check(`[${tag}] autocomplete input is wide (full field)`, s.w > 240, `width=${Math.round(s.w)}px`);
  } else {
    check(`[${tag}] autocomplete input present`, false, 'not found');
  }

  // magnifier icon muted + pulled over the input
  const icon = page.locator('img.autocomplete-icon').first();
  if (await icon.count()) {
    const s = await icon.evaluate(el => { const c = getComputedStyle(el); return { op: c.opacity, ml: c.marginLeft }; });
    check(`[${tag}] magnifier icon muted + overlapped`, parseFloat(s.op) < 1 && parseFloat(s.ml) < 0, `opacity=${s.op} ml=${s.ml}`);
  }

  // Type to trigger the jQuery UI suggestion menu, then verify its skin.
  try {
    await acInput.click();
    await acInput.type('c', { delay: 60 });
    await page.waitForSelector('ul.ui-autocomplete.ui-menu:visible', { timeout: 4000 });
    const menu = page.locator('ul.ui-autocomplete.ui-menu:visible').first();
    const ms = await menu.evaluate(el => { const c = getComputedStyle(el); return { br: c.borderRadius, bw: c.borderTopWidth, bg: c.backgroundColor }; });
    check(`[${tag}] autocomplete menu is a bordered card`, parseFloat(ms.br) >= 4 && parseFloat(ms.bw) >= 1, `radius=${ms.br} border=${ms.bw}`);
    await page.screenshot({ path: `ks-controls-${tag}-autocomplete-menu.png` });
    await page.keyboard.press('Escape');
  } catch (e) {
    check(`[${tag}] autocomplete menu appears + styled`, false, 'menu did not open (source may be empty)');
  }

  // --- Native single <select> stretches full width (nobr defeated) ---
  const sel = page.locator('nobr > select:not([multiple])').first();
  if (await sel.count()) {
    const wrap = await sel.evaluate(el => {
      const selW = el.getBoundingClientRect().width;
      const body = el.closest('.fz-field__body, .col2');
      const bodyW = body ? body.getBoundingClientRect().width : 0;
      return { selW, bodyW };
    });
    check(`[${tag}] native <select> fills the field width`, wrap.selW > wrap.bodyW * 0.9, `select=${Math.round(wrap.selW)} field=${Math.round(wrap.bodyW)}`);
  }

  // --- Multi-select listbox: auto height (shows multiple rows), not clipped ---
  const multi = page.locator('select[multiple]').first();
  if (await multi.count()) {
    const ms = await multi.evaluate(el => { const c = getComputedStyle(el); return { h: el.getBoundingClientRect().height, br: c.borderRadius, oflow: c.overflowY }; });
    check(`[${tag}] multi-select shows multiple rows (auto height)`, ms.h > 60, `height=${Math.round(ms.h)}px`);
    check(`[${tag}] multi-select is a rounded scrollable surface`, parseFloat(ms.br) >= 4, `radius=${ms.br}`);
    await multi.scrollIntoViewIfNeeded();
    await multi.screenshot({ path: `ks-controls-${tag}-multiselect.png` });
  } else {
    check(`[${tag}] multi-select present`, false, 'not found on this screen');
  }

  await page.screenshot({ path: `ks-controls-${tag}-full.png`, fullPage: true });
  await ctx.close();
}

await run(1280, 'desktop');
await run(390, 'mobile');

await browser.close();
const failed = results.filter(r => !r.pass);
console.log(`\n${results.length - failed.length}/${results.length} checks passed.`);
process.exit(failed.length ? 1 : 0);
