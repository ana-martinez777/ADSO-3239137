// Minimal HTTP client for the Sports Gear API (docs/api-contract.md).

export class ApiError extends Error {
  constructor(status, code, message, details) {
    super(message);
    this.status = status;
    this.code = code;
    this.details = details;
  }
}

export class ApiClient {
  constructor(baseUrl, fetchImpl = globalThis.fetch) {
    this.baseUrl = baseUrl;
    this.fetch = fetchImpl;
    this.token = null;
  }

  async request(method, path, { body, query } = {}) {
    const url = new URL(this.baseUrl + path);
    Object.entries(query ?? {}).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') url.searchParams.set(key, value);
    });

    const headers = { Accept: 'application/json' };
    if (body !== undefined) headers['Content-Type'] = 'application/json';
    if (this.token) headers.Authorization = `Bearer ${this.token}`;

    const response = await this.fetch(url, {
      method,
      headers,
      body: body === undefined ? undefined : JSON.stringify(body),
    });
    const payload = response.status === 204 ? null : await response.json().catch(() => null);

    if (!response.ok) {
      throw new ApiError(
        response.status,
        payload?.code ?? 'UNEXPECTED_ERROR',
        payload?.message ?? `La API respondió HTTP ${response.status}.`,
        payload?.details ?? {},
      );
    }
    return payload?.data ?? payload;
  }

  async login(username, password) {
    const data = await this.request('POST', '/auth/login', { body: { username, password } });
    this.token = data.access_token;
    return data.user;
  }

  get(path, query) {
    return this.request('GET', path, { query });
  }

  post(path, body) {
    return this.request('POST', path, { body });
  }
}

/** Waits until GET /health answers, so the seeder can start with the containers. */
export async function waitForApi(client, { attempts = 60, delayMs = 2000, sleep = defaultSleep } = {}) {
  for (let attempt = 1; attempt <= attempts; attempt += 1) {
    try {
      await client.get('/health');
      return;
    } catch {
      if (attempt === attempts) break;
      await sleep(delayMs);
    }
  }
  throw new Error(`La API ${client.baseUrl} no respondió.`);
}

function defaultSleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}
