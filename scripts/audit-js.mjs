import { chromium } from 'playwright';
import { execSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

const BASE = (process.env.AUDIT_BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');
const SKIP_URI = /^(_|telescope|horizon|livewire|storage|sanctum|up$)|logout|signout|delete|destroy|download|export|print/i;
const DANGEROUS = /delete|hapus|remove|logout|keluar|destroy|reset/i;

const routes = JSON.parse(
  execSync('php artisan route:list --json --method=GET', { encoding: 'utf8', maxBuffer: 50 * 1024 * 1024 }),
)
  .map((r) => r.uri.replace(/^\//, ''))
  .filter((uri) => !uri.includes('{') && !SKIP_URI.test(uri));

const issues = [];
const add = (level, route, type, where, message) => issues.push({ level, route, type, where, message });

const browser = await chromium.launch();
const context = await browser.newContext();

if (process.env.AUDIT_LOGIN_URL && process.env.AUDIT_EMAIL) {
  const p = await context.newPage();
  await p.goto(BASE + process.env.AUDIT_LOGIN_URL, { waitUntil: 'networkidle' });
  await p.fill(process.env.AUDIT_EMAIL_SELECTOR || 'input[name=email]', process.env.AUDIT_EMAIL);
  await p.fill(process.env.AUDIT_PASSWORD_SELECTOR || 'input[name=password]', process.env.AUDIT_PASSWORD || '');
  await Promise.all([p.waitForLoadState('networkidle'), p.keyboard.press('Enter')]);
  await p.close();
}

for (const uri of routes) {
  const url = `${BASE}/${uri}`;
  const route = '/' + uri;
  const page = await context.newPage();
  page.on('dialog', (d) => d.dismiss());

  page.on('pageerror', (err) => {
    const top = (err.stack || '').split('\n').find((l) => l.includes('http')) || '';
    add('ERROR', route, 'JS exception', top.trim().replace(/^at\s+/, ''), err.message);
  });
  page.on('console', (msg) => {
    if (msg.type() !== 'error') return;
    const loc = msg.location();
    add('ERROR', route, 'console.error', `${loc.url || ''}:${loc.lineNumber || 0}`, msg.text());
  });
  page.on('requestfailed', (req) => {
    add('ERROR', route, 'Request gagal', req.url(), req.failure()?.errorText || 'failed');
  });
  page.on('response', (res) => {
    if (res.status() >= 400 && res.url().startsWith(BASE)) {
      add('ERROR', route, `HTTP ${res.status()}`, res.url(), `${res.request().method()} ${res.url().replace(BASE, '')}`);
    }
  });

  try {
    const resp = await page.goto(url, { waitUntil: 'networkidle', timeout: 25000 });
    if (!resp || resp.status() >= 500) add('ERROR', route, 'Halaman', url, `status ${resp ? resp.status() : 'tanpa respons'}`);

    if (process.env.AUDIT_CLICK === '1') {
      const total = await page.locator('[onclick]').count();
      for (let i = 0; i < Math.min(total, 15); i++) {
        const el = page.locator('[onclick]').nth(i);
        const attr = (await el.getAttribute('onclick')) || '';
        const text = (await el.innerText().catch(() => '')) || '';
        if (DANGEROUS.test(attr) || DANGEROUS.test(text)) continue;
        try {
          await el.click({ timeout: 2000 });
          await page.waitForTimeout(300);
          if (page.url() !== url) await page.goto(url, { waitUntil: 'networkidle' });
        } catch {}
      }
    }
  } catch (e) {
    add('ERROR', route, 'Navigasi', url, e.message.split('\n')[0]);
  }
  await page.close();
}
await browser.close();

const uniq = [...new Map(issues.map((i) => [JSON.stringify(i), i])).values()];
const date = new Date().toISOString().slice(0, 10);
fs.mkdirSync('storage/logs/audit', { recursive: true });

function locate(where) {
  const m = where.match(/^(.*?):(\d+)(?::(\d+))?$/);
  const url = (m ? m[1] : where).split('?')[0];
  const line = m ? Number(m[2]) : 0;
  if (url.startsWith(BASE)) {
    const rel = path.posix.join('public', url.slice(BASE.length));
    if (fs.existsSync(rel)) return { file: rel, line };
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
