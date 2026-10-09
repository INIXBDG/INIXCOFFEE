import { chromium } from 'playwright';
import { execSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

const BASE = (process.env.AUDIT_BASE_URL || 'http://127.0.0.1:8000/').replace(/\/$/, '');
const SKIP_URI = /^(_|telescope|horizon|livewire|storage|sanctum|up$)|logout|signout|delete|destroy|download|export|print/i;
const DANGEROUS = /delete|hapus|remove|logout|keluar|destroy|reset/i;
const NAVIGATION_TIMEOUT_MS = 15000;

const raw = execSync('php artisan route:list --json --method=GET', { encoding: 'utf8', maxBuffer: 50 * 1024 * 1024 });
const routes = JSON.parse(raw.slice(raw.indexOf('[')))
  .map((r) => r.uri.replace(/^\//, ''))
  .filter((uri) => !uri.includes('{') && !SKIP_URI.test(uri));

const issues = [];
const add = (level, route, type, where, message) => issues.push({ level, route, type, where, message });
let authenticated = !(process.env.AUDIT_LOGIN_URL && process.env.AUDIT_EMAIL);

const browser = await chromium.launch();
const context = await browser.newContext();

if (process.env.AUDIT_LOGIN_URL && process.env.AUDIT_EMAIL) {
  const p = await context.newPage();
  p.setDefaultNavigationTimeout(NAVIGATION_TIMEOUT_MS);
  const loginUrl = BASE + process.env.AUDIT_LOGIN_URL;
  try {
    await p.goto(loginUrl, { waitUntil: 'domcontentloaded' });
    await p.fill(process.env.AUDIT_EMAIL_SELECTOR || 'input[name=username]', process.env.AUDIT_EMAIL);
    await p.fill(process.env.AUDIT_PASSWORD_SELECTOR || 'input[name=password]', process.env.AUDIT_PASSWORD || '');
    await Promise.all([p.waitForLoadState('domcontentloaded'), p.keyboard.press('Enter')]);
    authenticated = new URL(p.url()).pathname !== new URL(loginUrl).pathname;
    if (!authenticated) {
      add('ERROR', process.env.AUDIT_LOGIN_URL, 'Login', p.url(), 'Autentikasi tidak berhasil; browser masih berada di halaman login');
    }
  } catch (e) {
    authenticated = false;
    add('ERROR', process.env.AUDIT_LOGIN_URL, 'Login', loginUrl, e.message.split('\n')[0]);
  } finally {
    await p.close();
  }
}

const page = await context.newPage();
page.setDefaultNavigationTimeout(NAVIGATION_TIMEOUT_MS);
page.on('dialog', (d) => d.dismiss());
let activeRoute = '';
page.on('pageerror', (err) => {
  const top = (err.stack || '').split('\n').find((l) => l.includes('http')) || '';
  add('ERROR', activeRoute, 'JS exception', top.trim().replace(/^at\s+/, ''), err.message);
});
page.on('console', (msg) => {
  if (msg.type() !== 'error') return;
  const loc = msg.location();
  add('ERROR', activeRoute, 'console.error', `${loc.url || ''}:${loc.lineNumber || 0}`, msg.text());
});
page.on('requestfailed', (req) => {
  const err = req.failure()?.errorText || 'failed';
  if (/ERR_ABORTED/.test(err)) return;
  add('ERROR', activeRoute, 'Request gagal', req.url(), err);
});
page.on('response', (res) => {
  if (res.status() >= 400 && res.url().startsWith(BASE)) {
    add('ERROR', activeRoute, `HTTP ${res.status()}`, res.url(), `${res.request().method()} ${res.url().replace(BASE, '')}`);
  }
});

for (const uri of routes) {
  const url = `${BASE}/${uri}`;
  activeRoute = '/' + uri;

  try {
    const resp = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: NAVIGATION_TIMEOUT_MS });
    await page.waitForLoadState('networkidle', { timeout: 5000 }).catch(() => {});
    if (!resp || resp.status() >= 500) add('ERROR', activeRoute, 'Halaman', url, `status ${resp ? resp.status() : 'tanpa respons'}`);

    if (authenticated && process.env.AUDIT_CLICK === '1') {
      const total = await page.locator('[onclick]').count();
      for (let i = 0; i < Math.min(total, 15); i++) {
        const el = page.locator('[onclick]').nth(i);
        const attr = (await el.getAttribute('onclick')) || '';
        const text = (await el.innerText().catch(() => '')) || '';
        if (DANGEROUS.test(attr) || DANGEROUS.test(text)) continue;
        try {
          await el.click({ timeout: 2000 });
          await page.waitForTimeout(300);
          if (page.url() !== url) await page.goto(url, { waitUntil: 'domcontentloaded' });
        } catch {}
      }
    }
  } catch (e) {
    add('ERROR', activeRoute, 'Navigasi', url, e.message.split('\n')[0]);
  }
}
await page.close();
await browser.close();

const uniq = [...new Map(issues.map((i) => [JSON.stringify(i), i])).values()];
const date = new Date().toISOString().slice(0, 10);
fs.mkdirSync('storage/logs/audit', { recursive: true });

function locate(where) {
  const m = String(where).match(/(https?:\/\/[^\s()]+?)(?::(\d+)(?::\d+)?)?\)?\s*$/);
  const url = (m ? m[1] : '').split('?')[0];
  const line = m && m[2] ? Number(m[2]) : 0;
  if (url.startsWith(BASE)) {
    const rel = path.posix.join('public', url.slice(BASE.length));
    if (fs.existsSync(rel) && fs.statSync(rel).isFile()) return { file: rel, line };
  }
  return { file: null, line };
}

const errors = uniq.filter((i) => i.level === 'ERROR').length;
fs.writeFileSync(
  'storage/logs/audit/js.json',
  JSON.stringify(
    {
      kind: 'js',
      generated_at: new Date().toISOString(),
      summary: { errors, warnings: uniq.length - errors },
      issues: uniq.map((i) => ({ level: i.level, feature: i.route, type: i.type, ...locate(i.where), url: i.where, message: i.message })),
    },
    null,
    2,
  ),
);

if (!uniq.length) {
  const ok = `[${new Date().toISOString()}] Semua ${routes.length} halaman bersih dari error JavaScript.\n`;
  fs.writeFileSync(`storage/logs/audit-js-${date}.log`, ok);
  console.log(ok.trim());
  process.exit(0);
}
console.table(uniq.map(({ level, route, type, where, message }) => ({ level, route, type, where, message: message.slice(0, 160) })));

const log = uniq.map((i) => `[${i.level}] ${i.route} | ${i.type} | ${i.where} | ${i.message}`).join('\n');
fs.writeFileSync(`storage/logs/audit-js-${date}.log`, log + '\n');
console.error(`\nTotal: ${uniq.length} masalah di ${new Set(uniq.map((i) => i.route)).size} halaman`);
process.exit(1);