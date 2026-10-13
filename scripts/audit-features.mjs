import { chromium } from 'playwright';
import { execSync } from 'node:child_process';
import fs from 'node:fs';

const BASE = (process.env.AUDIT_BASE_URL || 'http://192.168.95.98:8000').replace(/\/$/, '');
const ONLY = (process.env.AUDIT_FEATURE_ONLY || '').split(',').map((s) => s.trim()).filter(Boolean);
const ONLY_ROUTE = (process.env.AUDIT_FEATURE_ROUTE || '').trim();
const SKIP = /^(_|telescope|horizon|livewire|storage|sanctum|up$)|logout|signout|delete|destroy|download|export|print/i;
const GROUP_BY = process.env.AUDIT_FEATURE_GROUP || 'uri';

function loadRoutes() {
  let raw;
  try {
    raw = execSync('php artisan route:list --json --method=GET', {
      encoding: 'utf8',
      maxBuffer: 50 * 1024 * 1024,
    });
  } catch (e) {
    const stdout = String(e.stdout || '');
    if (!stdout.trim().startsWith('[')) {
      throw new Error('route:list gagal: ' + String(e.stderr || e.message).slice(-800));
    }
    raw = stdout;
  }
  return JSON.parse(raw)
    .map((r) => ({
      uri: String(r.uri || '').replace(/^\//, ''),
      name: r.name || '',
      action: r.action || '',
    }))
    .filter((r) => r.uri && !r.uri.includes('{') && !SKIP.test(r.uri));
}

function featureOf(uri, name) {
  if (GROUP_BY === 'route_name' && name) {
    return String(name.split('.')[0] || 'other').toLowerCase();
  }
  const seg = uri.split('/')[0] || 'root';
  return seg.toLowerCase() || 'root';
}

function buildFlows(routes) {
  const byFeature = new Map();
  for (const r of routes) {
    const f = featureOf(r.uri, r.name);
    if (ONLY.length && !ONLY.includes(f)) continue;
    if (ONLY_ROUTE && r.name !== ONLY_ROUTE && r.uri !== ONLY_ROUTE) continue;
    if (!byFeature.has(f)) byFeature.set(f, []);
    byFeature.get(f).push(r);
  }

  const flows = [];
  for (const [feature, list] of byFeature) {
    const index =
      list.find((x) => /index$/i.test(x.name || '')) ||
      list.find((x) => !/(create|edit|show|store|update)$/i.test(x.name || '') && x.uri === feature) ||
      list.find((x) => x.uri.split('/').length <= 2) ||
      list[0];

    const create =
      list.find((x) => /(^|\/)create$/i.test(x.uri) || /create$/i.test(x.name || '')) || null;

    const editCandidates = list.filter(
      (x) => /(^|\/)edit$/i.test(x.uri) || /edit$/i.test(x.name || ''),
    );

    if (index) {
      flows.push({ feature, step: 'buka_daftar', uri: index.uri, name: index.name, kind: 'open' });
    }
    if (create) {
      flows.push({ feature, step: 'buka_form_tambah', uri: create.uri, name: create.name, kind: 'open' });
      flows.push({ feature, step: 'submit_tambah', uri: create.uri, name: create.name, kind: 'submit_create' });
    }
    if (editCandidates.length) {
      flows.push({
        feature,
        step: 'buka_form_edit',
        uri: null,
        name: editCandidates[0].name || '',
        kind: 'open_edit_from_list',
        indexUri: index ? index.uri : null,
      });
    }
  }
  return flows;
}

function classifyError(text, status) {
  const t = (text || '').toLowerCase();
  if (
    status >= 500 ||
    t.includes('sqlstate') ||
    t.includes('queryexception') ||
    t.includes('cannot be null') ||
    t.includes('integrity constraint')
  ) {
    return 'HTTP 500 / Database';
  }
  if (t.includes('view [') && t.includes('not found')) return 'View tidak ditemukan';
  if (t.includes('view not found')) return 'View tidak ditemukan';
  if (t.includes('class') && t.includes('not found')) return 'Class tidak ditemukan';
  if (t.includes('route') && t.includes('not defined')) return 'Route tidak ada';
  if (t.includes('errorexception') || t.includes('exception')) return 'Exception';
  if (t.includes('csrf') || t.includes('419')) return 'CSRF / sesi';
  return 'Server error';
}

function isServerCrash(body, status) {
  if (status >= 500) return true;
  const t = body || '';
  return (
    /SQLSTATE|QueryException|ErrorException|Integrity constraint|cannot be null|NOT NULL/i.test(t) ||
    /View \[/.test(t) ||
    /Illuminate\\Database|Illuminate\\View|Illuminate\\Foundation/i.test(t)
  );
}

const results = [];
const add = (row) => results.push(row);

const routes = loadRoutes();
const flows = buildFlows(routes);

const browser = await chromium.launch();
const context = await browser.newContext();

if (process.env.AUDIT_LOGIN_URL && process.env.AUDIT_EMAIL) {
  const p = await context.newPage();
  await p.goto(BASE + process.env.AUDIT_LOGIN_URL, { waitUntil: 'networkidle', timeout: 30000 });
  await p.fill(process.env.AUDIT_EMAIL_SELECTOR || 'input[name=email]', process.env.AUDIT_EMAIL);
  await p.fill(process.env.AUDIT_PASSWORD_SELECTOR || 'input[name=password]', process.env.AUDIT_PASSWORD || '');
  await Promise.all([p.waitForLoadState('networkidle'), p.keyboard.press('Enter')]);
  await p.close();
}

async function attachPageErrors(page) {
  const bag = [];
  page.on('pageerror', (err) => bag.push({ type: 'JS exception', message: err.message }));
  page.on('console', (msg) => {
    if (msg.type() === 'error') bag.push({ type: 'console.error', message: msg.text() });
  });
  return bag;
}

async function readBodyHint(page) {
  try {
    return await page.locator('body').innerText({ timeout: 2000 });
  } catch {
    return '';
  }
}

async function runOpen(flow) {
  const url = `${BASE}/${flow.uri}`;
  const page = await context.newPage();
  const bag = await attachPageErrors(page);
  page.on('dialog', (d) => d.dismiss());
  let status = 0;
  try {
    const resp = await page.goto(url, { waitUntil: 'networkidle', timeout: 25000 });
    status = resp ? resp.status() : 0;
    const body = await readBodyHint(page);

    if (status === 404 || status === 401 || status === 403) {
      await page.close();
      return;
    }

    if (isServerCrash(body, status)) {
      add({
        level: 'ERROR',
        feature: flow.feature,
        function: flow.step,
        type: classifyError(body, status >= 500 ? status : 500),
        file: null,
        line: 0,
        route: flow.name || null,
        uri: flow.uri,
        url,
        status: 'failed',
        message: (body || `HTTP ${status}`).slice(0, 500),
      });
    } else {
      for (const e of bag) {
        add({
          level: 'ERROR',
          feature: flow.feature,
          function: flow.step,
          type: e.type,
          file: null,
          line: 0,
          route: flow.name || null,
          uri: flow.uri,
          url,
          status: 'failed',
          message: e.message.slice(0, 400),
        });
      }
      if (!bag.length) {
        add({
          level: 'OK',
          feature: flow.feature,
          function: flow.step,
          type: 'Alur OK',
          file: null,
          line: 0,
          route: flow.name || null,
          uri: flow.uri,
          url,
          status: 'ok',
          message: `Berhasil: ${flow.step} (${status || 200})`,
        });
      }
    }
  } catch (e) {
    add({
      level: 'ERROR',
      feature: flow.feature,
      function: flow.step,
      type: 'Navigasi',
      file: null,
      line: 0,
      route: flow.name || null,
      uri: flow.uri,
      url,
      status: 'failed',
      message: String(e.message || e).split('\n')[0].slice(0, 400),
    });
  }
  await page.close();
}

async function runSubmitCreate(flow) {
  const url = `${BASE}/${flow.uri}`;
  const page = await context.newPage();
  page.on('dialog', (d) => d.dismiss());
  try {
    await page.goto(url, { waitUntil: 'networkidle', timeout: 25000 });
    const form = page.locator('form').first();
    if ((await form.count()) === 0) {
      add({
        level: 'WARNING',
        feature: flow.feature,
        function: flow.step,
        type: 'Form',
        file: null,
        line: 0,
        route: flow.name || null,
        uri: flow.uri,
        url,
        status: 'failed',
        message: 'Halaman create tanpa elemen form',
      });
      await page.close();
      return;
    }

    await Promise.all([
      page.waitForLoadState('networkidle').catch(() => {}),
      form.evaluate((f) => f.submit()),
    ]);
    await page.waitForTimeout(600);

    let body = await readBodyHint(page);
    let finalUrl = page.url();

    if (isServerCrash(body, 500)) {
      add({
        level: 'ERROR',
        feature: flow.feature,
        function: flow.step,
        type: classifyError(body, 500),
        file: null,
        line: 0,
        route: flow.name || null,
        uri: flow.uri,
        url: finalUrl,
        status: 'failed',
        message: body.slice(0, 500),
      });
      await page.close();
      return;
    }

    await page.goto(url, { waitUntil: 'networkidle', timeout: 25000 });
    const form2 = page.locator('form').first();
    const inputs = form2.locator(
      'input:not([type=hidden]):not([type=file]):not([type=submit]), select, textarea',
    );
    const n = await inputs.count();
    for (let i = 0; i < Math.min(n, 20); i++) {
      const el = inputs.nth(i);
      const tag = await el.evaluate((e) => e.tagName.toLowerCase());
      const type = (await el.getAttribute('type')) || '';
      const required = await el.getAttribute('required');
      try {
        if (tag === 'select') {
          const opts = el.locator('option:not([value=""])');
          if (await opts.count()) await el.selectOption({ index: 1 });
        } else if (type === 'checkbox' || type === 'radio') {
          await el.check({ force: true }).catch(() => {});
        } else if (required !== null) {
          await el.fill('1');
        }
      } catch {
      }
    }

    await Promise.all([
      page.waitForLoadState('networkidle').catch(() => {}),
      form2.evaluate((f) => f.submit()),
    ]);
    await page.waitForTimeout(600);

    body = await readBodyHint(page);
    finalUrl = page.url();

    if (isServerCrash(body, 500)) {
      add({
        level: 'ERROR',
        feature: flow.feature,
        function: flow.step,
        type: classifyError(body, 500),
        file: null,
        line: 0,
        route: flow.name || null,
        uri: flow.uri,
        url: finalUrl,
        status: 'failed',
        message: body.slice(0, 500),
      });
    } else {
      add({
        level: 'OK',
        feature: flow.feature,
        function: flow.step,
        type: 'Alur OK',
        file: null,
        line: 0,
        route: flow.name || null,
        uri: flow.uri,
        url: finalUrl,
        status: 'ok',
        message: 'Submit form tidak memunculkan error 500/server',
      });
    }
  } catch (e) {
    add({
      level: 'ERROR',
      feature: flow.feature,
      function: flow.step,
      type: 'Submit',
      file: null,
      line: 0,
      route: flow.name || null,
      uri: flow.uri,
      url,
      status: 'failed',
      message: String(e.message || e).split('\n')[0].slice(0, 400),
    });
  }
  await page.close();
}

async function runOpenEditFromList(flow) {
  if (!flow.indexUri) {
    add({
      level: 'WARNING',
      feature: flow.feature,
      function: flow.step,
      type: 'Edit',
      file: null,
      line: 0,
      route: flow.name || null,
      uri: null,
      url: null,
      status: 'failed',
      message: 'Tidak ada halaman daftar untuk mencari link edit',
    });
    return;
  }
  const listUrl = `${BASE}/${flow.indexUri}`;
  const page = await context.newPage();
  page.on('dialog', (d) => d.dismiss());
  try {
    const resp = await page.goto(listUrl, { waitUntil: 'networkidle', timeout: 25000 });
    const status = resp ? resp.status() : 0;
    if (status === 404 || status === 401 || status === 403) {
      await page.close();
      return;
    }
    const bodyList = await readBodyHint(page);
    if (isServerCrash(bodyList, status)) {
      add({
        level: 'ERROR',
        feature: flow.feature,
        function: flow.step,
        type: classifyError(bodyList, status >= 500 ? status : 500),
        file: null,
        line: 0,
        route: flow.name || null,
        uri: flow.indexUri,
        url: listUrl,
        status: 'failed',
        message: bodyList.slice(0, 500),
      });
      await page.close();
      return;
    }

    const editLink = page.locator('a[href*="edit"]').first();
    if ((await editLink.count()) === 0) {
      add({
        level: 'WARNING',
        feature: flow.feature,
        function: flow.step,
        type: 'Edit',
        file: null,
        line: 0,
        route: flow.name || null,
        uri: flow.indexUri,
        url: listUrl,
        status: 'failed',
        message: 'Tidak ditemukan link edit di halaman daftar',
      });
      await page.close();
      return;
    }
    await Promise.all([page.waitForLoadState('networkidle'), editLink.click()]);
    const body = await readBodyHint(page);
    const url = page.url();
    if (isServerCrash(body, 500)) {
      add({
        level: 'ERROR',
        feature: flow.feature,
        function: flow.step,
        type: classifyError(body, 500),
        file: null,
        line: 0,
        route: flow.name || null,
        uri: flow.indexUri,
        url,
        status: 'failed',
        message: body.slice(0, 500),
      });
    } else {
      add({
        level: 'OK',
        feature: flow.feature,
        function: flow.step,
        type: 'Alur OK',
        file: null,
        line: 0,
        route: flow.name || null,
        uri: flow.indexUri,
        url,
        status: 'ok',
        message: 'Berhasil membuka form edit dari daftar',
      });
    }
  } catch (e) {
    add({
      level: 'ERROR',
      feature: flow.feature,
      function: flow.step,
      type: 'Edit',
      file: null,
      line: 0,
      route: flow.name || null,
      uri: flow.indexUri,
      url: listUrl,
      status: 'failed',
      message: String(e.message || e).split('\n')[0].slice(0, 400),
    });
  }
  await page.close();
}

for (const flow of flows) {
  if (flow.kind === 'open') await runOpen(flow);
  else if (flow.kind === 'submit_create') await runSubmitCreate(flow);
  else if (flow.kind === 'open_edit_from_list') await runOpenEditFromList(flow);
}

await browser.close();

const errors = results.filter((r) => r.level === 'ERROR').length;
const warnings = results.filter((r) => r.level === 'WARNING').length;
const oks = results.filter((r) => r.level === 'OK').length;

fs.mkdirSync('storage/logs/audit', { recursive: true });
fs.writeFileSync(
  'storage/logs/audit/features.json',
  JSON.stringify(
    {
      kind: 'features',
      generated_at: new Date().toISOString(),
      summary: { errors, warnings, ok: oks },
      issues: results,
    },
    null,
    2,
  ),
);

console.log(`audit-features: ${oks} ok, ${errors} error, ${warnings} warning, ${flows.length} langkah`);
process.exit(errors > 0 ? 1 : 0);