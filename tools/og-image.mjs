// Renders public/og.png, the 1200x630 Open Graph image every page names (app/domains/site/ui/usePageSeo.ts), with the
// factory machine's Playwright and chrome-headless-shell. Run it again after a brand change:
//   node tools/og-image.mjs public/logo.png node_modules/@fontsource-variable/inter/files/inter-latin-wght-normal.woff2 public/og.png
const { chromium } = await import(process.env.HOME + '/.local/opt/playwright/node_modules/playwright/index.mjs');
import { readFileSync } from 'node:fs';
const [logo, font, out] = process.argv.slice(2);
const b64 = (f) => readFileSync(f).toString('base64');
const html = `<style>
@font-face{font-family:Inter;src:url(data:font/woff2;base64,${b64(font)}) format('woff2');font-weight:100 900}
body{margin:0;width:1200px;height:630px;font-family:Inter;color:#e9edf4;background:
radial-gradient(900px 500px at 95% -10%,rgba(85,135,228,.28),transparent 70%),radial-gradient(700px 400px at -10% 110%,rgba(229,220,45,.16),transparent 70%),#000;
display:flex;flex-direction:column;justify-content:center;padding:0 90px;box-sizing:border-box}
.top{display:flex;align-items:center;gap:28px;margin-bottom:44px}
img{height:120px}
.name{font-size:64px;font-weight:750;letter-spacing:-.02em}
h1{margin:0;font-size:66px;line-height:1.1;font-weight:750;letter-spacing:-.02em;max-width:980px}
span{color:#e5dc2d}
p{margin:32px 0 0;font-size:30px;color:#a3adbd}
</style><div class="top"><img src="data:image/png;base64,${b64(logo)}"><div class="name">PCesports</div></div>
<h1>Esports for people who don't have time to <span>follow everything</span></h1>
<p>Which CS2 matches are worth it, with the official VODs. No spoilers.</p>`;
const browser = await chromium.launch({ executablePath: process.env.HOME + '/.local/bin/chrome-headless-shell' });
const page = await browser.newPage({ viewport: { width: 1200, height: 630 } });
await page.setContent(html); await page.evaluate(() => document.fonts.ready);
await page.screenshot({ path: out });
await browser.close();
