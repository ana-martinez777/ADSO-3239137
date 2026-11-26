import { test } from 'node:test';
import assert from 'node:assert/strict';
import { ApiClient, ApiError, waitForApi } from '../src/apiClient.js';

const json = (status, body) => ({ ok: status < 400, status, json: async () => body });

test('login stores the token and sends it on later requests', async () => {
  const calls = [];
  const client = new ApiClient('http://api/api/v1', async (url, options) => {
    calls.push({ url: String(url), options });
    if (String(url).endsWith('/auth/login')) return json(200, { data: { access_token: 'jwt', user: { role: 'admin' } } });
    return json(200, { data: [] });
  });

  await client.login('admin', 'secret');
  await client.get('/equipment', { status: 'all', search: '' });

  assert.equal(calls[1].url, 'http://api/api/v1/equipment?status=all');
  assert.equal(calls[1].options.headers.Authorization, 'Bearer jwt');
  assert.equal(calls[1].options.headers.Accept, 'application/json');
});

test('API errors keep the contract code', async () => {
  const client = new ApiClient('http://api/api/v1', async () =>
    json(409, { code: 'REFERENCE_ALREADY_EXISTS', message: 'La referencia ya existe.', details: {} }));

  await assert.rejects(client.post('/equipment', {}), (error) => {
    assert.ok(error instanceof ApiError);
    assert.equal(error.status, 409);
    assert.equal(error.code, 'REFERENCE_ALREADY_EXISTS');
    return true;
  });
});

test('waitForApi retries until the API answers', async () => {
  let attempts = 0;
  const client = new ApiClient('http://api/api/v1', async () => {
    attempts += 1;
    if (attempts < 3) throw new TypeError('connection refused');
    return json(200, { status: 'ok' });
  });

  await waitForApi(client, { attempts: 5, delayMs: 0, sleep: async () => {} });
  assert.equal(attempts, 3);
});
