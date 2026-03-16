What would you like your workers.dev subdomain to be? »/taylorhelmantattoo
 * taylorhelmantattoo-availability — Cloudflare Worker
 *
 * Queries Google Calendar free/busy data and returns sanitized availability
 * JSON for taylorhelmantattoo.com.
 *
 * PRIVACY GUARANTEE:
 *   The Google Calendar free/busy API returns ONLY opaque time blocks.
 *   No event titles, client names, descriptions, locations, or attendees
 *   are accessible via this endpoint — this is a hard Google API contract,
 *   not a filter that could be misconfigured.
 *
 * SECRETS:
 *   GOOGLE_SERVICE_ACCOUNT_JSON — stored in Cloudflare encrypted secrets only.
 *   Never committed to Git. Set with: npx wrangler secret put GOOGLE_SERVICE_ACCOUNT_JSON
 */

// ── Schedule & display configuration ─────────────────────────────────────────
// Edit these values and run `npx wrangler deploy` to update the live site.

const CONFIG = {
  timezone:        'America/Toronto',
  workingDays:     [3, 4, 5, 6],          // 0=Sun…6=Sat  →  Wed=3 Thu=4 Fri=5 Sat=6
  workingHours:    { start: 11, end: 19 }, // 11 AM – 7 PM local time
  morningWindow:   { start: 11, end: 14 }, // 11 AM – 2 PM
  afternoonWindow: { start: 14, end: 19 }, // 2 PM  – 7 PM
  slotMinMinutes:  150,  // free minutes required to show status "available" (2.5 h)
  windowMinMinutes: 90,  // free minutes required to show a morning/afternoon window label
  bookingHorizonDays: 60,
  leadTimeHours:   48,
  cacheTtlSeconds: 900,  // 15 minutes

  // ── Special day overrides ──────────────────────────────────────────────────
  // Map "YYYY-MM-DD" → { status, label }.
  // status: "available" | "limited" | "booked" | "closed" | "flash-day" | "travel" | "convention"
  // label:  string shown on the availability page, e.g. "Flash Day", "Convention", "Travel"
  // After editing, run: npx wrangler deploy
  specialDays: {
    // "2026-04-12": { status: "closed",    label: "Convention" },
    // "2026-05-17": { status: "flash-day", label: "Flash Day"  },
    // "2026-06-01": { status: "closed",    label: "Travel"     },
  },
};

const DISCLAIMER =
  'Availability is updated every 15 minutes and is subject to change. ' +
  'Submitting a request does not guarantee a booking \u2014 all appointments are ' +
  'manually confirmed by Taylor. Times shown in Eastern Time (ET).';

const MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

// ── Date / timezone helpers ───────────────────────────────────────────────────

/** Returns "YYYY-MM-DD" for the current day in the given IANA timezone. */
function todayInTZ(tz) {
  const parts = new Intl.DateTimeFormat('en-CA', {
    timeZone: tz, year: 'numeric', month: '2-digit', day: '2-digit',
  }).formatToParts(new Date());
  const p = {};
  parts.forEach(({ type, value }) => { p[type] = value; });
  return `${p.year}-${p.month}-${p.day}`;
}

/** Add `n` calendar days to a "YYYY-MM-DD" string. Handles month/year rollover. */
function addDays(dateStr, n) {
  const d = new Date(`${dateStr}T12:00:00Z`);
  d.setUTCDate(d.getUTCDate() + n);
  return d.toISOString().slice(0, 10);
}

/** 0=Sun…6=Sat for a "YYYY-MM-DD" string (evaluated at noon UTC → safe for all ET dates). */
function dayOfWeek(dateStr) {
  return new Date(`${dateStr}T12:00:00Z`).getUTCDay();
}

/**
 * Converts a whole-hour local time to a UTC Date.
 * Uses a probe-and-correct method: start with a UTC guess that has the same
 * nominal hour value, then read back what local hour that gives in `tz` and
 * shift by the difference. Safe for standard business hours (no DST transitions
 * between 11 AM and 7 PM in America/Toronto).
 */
function localHourToUTC(dateStr, hour, tz) {
  const probe = new Date(`${dateStr}T${String(hour).padStart(2, '0')}:00:00Z`);
  const localHour = parseInt(
    new Intl.DateTimeFormat('en-US', { timeZone: tz, hour: '2-digit', hour12: false }).format(probe),
    10,
  );
  return new Date(probe.getTime() + (hour - localHour) * 3_600_000);
}

/** Returns the ISO-week Monday ("YYYY-MM-DD") for any date string. */
function isoWeekMonday(dateStr) {
  const d = new Date(`${dateStr}T12:00:00Z`);
  const dow = d.getUTCDay();
  d.setUTCDate(d.getUTCDate() + (dow === 0 ? -6 : 1 - dow));
  return d.toISOString().slice(0, 10);
}

/** "2026-03-16" → "Week of March 16" */
function buildWeekLabel(mondayStr) {
  return 'Week of ' + new Date(`${mondayStr}T12:00:00Z`).toLocaleDateString('en-US', {
    timeZone: 'UTC', month: 'long', day: 'numeric',
  });
}

/** "2026-03-18" → "Wednesday, March 18" in the given timezone. */
function friendlyDate(dateStr, tz) {
  return new Date(`${dateStr}T12:00:00Z`).toLocaleDateString('en-US', {
    timeZone: tz, weekday: 'long', month: 'long', day: 'numeric',
  });
}

/** "2026-03-18" → "Mar 18" */
function shortDateStr(dateStr, tz) {
  return new Date(`${dateStr}T12:00:00Z`).toLocaleDateString('en-US', {
    timeZone: tz, month: 'short', day: 'numeric',
  });
}

/** Sorted YYYY-MM-DD array → "Apr" or "Apr–Jun" */
function monthRange(dates) {
  if (!dates.length) return '';
  const first = parseInt(dates[0].slice(5, 7), 10) - 1;
  const last  = parseInt(dates[dates.length - 1].slice(5, 7), 10) - 1;
  return first === last ? MONTHS[first] : `${MONTHS[first]}\u2013${MONTHS[last]}`;
}

// ── Free-time computation ─────────────────────────────────────────────────────

/**
 * Returns the number of free minutes inside [winStartMs, winEndMs],
 * given an array of [startMs, endMs] busy pairs that may be unsorted or overlapping.
 */
function freeMinutes(busyPairs, winStartMs, winEndMs) {
  if (winStartMs >= winEndMs) return 0;

  // Clip each busy block to the window; drop zero-length results; sort by start.
  const clipped = busyPairs
    .map(([s, e]) => [Math.max(s, winStartMs), Math.min(e, winEndMs)])
    .filter(([s, e]) => s < e)
    .sort((a, b) => a[0] - b[0]);

  let free = 0;
  let cursor = winStartMs;
  for (const [s, e] of clipped) {
    if (s > cursor) free += s - cursor;  // free gap before this block
    if (e > cursor) cursor = e;          // advance past this block (handles overlaps)
  }
  if (cursor < winEndMs) free += winEndMs - cursor; // free tail after last block
  return free / 60_000; // ms → minutes
}

// ── Google OAuth — Service Account → Bearer token ─────────────────────────────

function b64url(buffer) {
  let s = '';
  for (const byte of new Uint8Array(buffer)) s += String.fromCharCode(byte);
  return btoa(s).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
}

/**
 * Exchanges a Google Service Account JSON key for a short-lived OAuth access token.
 * Uses RS256 JWT signing via the Web Crypto API — no third-party dependencies.
 */
async function serviceAccountToken(serviceAccountJson) {
  const sa  = JSON.parse(serviceAccountJson);
  const now = Math.floor(Date.now() / 1_000);
  const enc = (obj) => b64url(new TextEncoder().encode(JSON.stringify(obj)));

  const headerB64  = enc({ alg: 'RS256', typ: 'JWT' });
  const payloadB64 = enc({
    iss:   sa.client_email,
    scope: 'https://www.googleapis.com/auth/calendar.readonly',
    aud:   'https://oauth2.googleapis.com/token',
    iat:   now,
    exp:   now + 3_600,
  });

  const signingInput = `${headerB64}.${payloadB64}`;

  // Decode PEM private key (always PKCS#8 for Google service accounts)
  const pemBody  = sa.private_key
    .replace(/-----BEGIN PRIVATE KEY-----/, '')
    .replace(/-----END PRIVATE KEY-----/, '')
    .replace(/\s/g, '');
  const keyBytes = Uint8Array.from(atob(pemBody), (c) => c.charCodeAt(0));

  const cryptoKey = await crypto.subtle.importKey(
    'pkcs8',
    keyBytes.buffer,
    { name: 'RSASSA-PKCS1-v1_5', hash: 'SHA-256' },
    false,
    ['sign'],
  );

  const signature = await crypto.subtle.sign(
    'RSASSA-PKCS1-v1_5',
    cryptoKey,
    new TextEncoder().encode(signingInput),
  );

  const jwt = `${signingInput}.${b64url(signature)}`;

  const res = await fetch('https://oauth2.googleapis.com/token', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `grant_type=urn%3Aietf%3Aparams%3Aoauth%3Agrant-type%3Ajwt-bearer&assertion=${jwt}`,
  });

  if (!res.ok) {
    const msg = await res.text().catch(() => '(no body)');
    throw new Error(`Token request failed (${res.status}): ${msg}`);
  }

  const { access_token } = await res.json();
  if (!access_token) throw new Error('Token response missing access_token');
  return access_token;
}

// ── Google Calendar free/busy ─────────────────────────────────────────────────

/**
 * Returns busy periods as [startMs, endMs] pairs.
 *
 * The free/busy API does NOT return event titles, attendees, descriptions,
 * locations, or any other metadata — only time ranges. This is a hard
 * Google API contract enforced server-side by Google.
 */
async function fetchBusy(token, calendarId, rangeStart, rangeEnd) {
  const res = await fetch('https://www.googleapis.com/calendar/v3/freeBusy', {
    method: 'POST',
    headers: {
      Authorization:  `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      timeMin:  rangeStart.toISOString(),
      timeMax:  rangeEnd.toISOString(),
      timeZone: CONFIG.timezone,
      items:    [{ id: calendarId }],
    }),
  });

  if (!res.ok) {
    const msg = await res.text().catch(() => '(no body)');
    throw new Error(`freeBusy request failed (${res.status}): ${msg}`);
  }

  const data = await res.json();
  return (data.calendars?.[calendarId]?.busy ?? []).map(({ start, end }) => [
    new Date(start).getTime(),
    new Date(end).getTime(),
  ]);
}

// ── Day / week transformation ─────────────────────────────────────────────────

/** Compute which half-day windows have enough free time to advertise. */
function computeWindows(busyPairs, dateStr, tz) {
  const windows = [];
  const mS = localHourToUTC(dateStr, CONFIG.morningWindow.start,   tz).getTime();
  const mE = localHourToUTC(dateStr, CONFIG.morningWindow.end,     tz).getTime();
  const aS = localHourToUTC(dateStr, CONFIG.afternoonWindow.start, tz).getTime();
  const aE = localHourToUTC(dateStr, CONFIG.afternoonWindow.end,   tz).getTime();
  if (freeMinutes(busyPairs, mS, mE) >= CONFIG.windowMinMinutes) windows.push('Morning');
  if (freeMinutes(busyPairs, aS, aE) >= CONFIG.windowMinMinutes) windows.push('Afternoon');
  return windows;
}

/**
 * Builds a single day entry.
 * If the date is in CONFIG.specialDays its status/label comes from config only.
 * Otherwise status is derived purely from free/busy time math.
 */
function buildDay(dateStr, busyPairs, tz) {
  const base = {
    date:      dateStr,
    dayName:   new Date(`${dateStr}T12:00:00Z`).toLocaleDateString('en-US', { timeZone: tz, weekday: 'long' }),
    shortDate: shortDateStr(dateStr, tz),
  };

  const special = CONFIG.specialDays[dateStr];
  if (special) {
    const showWindows = special.status === 'available' || special.status === 'flash-day';
    return {
      ...base,
      status:  special.status,
      label:   special.label ?? null,
      windows: showWindows ? computeWindows(busyPairs, dateStr, tz) : [],
    };
  }

  const winStartMs = localHourToUTC(dateStr, CONFIG.workingHours.start, tz).getTime();
  const winEndMs   = localHourToUTC(dateStr, CONFIG.workingHours.end,   tz).getTime();
  const free       = freeMinutes(busyPairs, winStartMs, winEndMs);

  const status = free >= CONFIG.slotMinMinutes ? 'available'
               : free > 0                      ? 'limited'
               :                                 'booked';

  return {
    ...base,
    status,
    label:   null,
    windows: status !== 'booked' ? computeWindows(busyPairs, dateStr, tz) : [],
  };
}

/** Groups a flat array of day objects into ISO-week buckets. */
function groupIntoWeeks(days) {
  const map = new Map();
  for (const day of days) {
    const monday = isoWeekMonday(day.date);
    if (!map.has(monday)) {
      map.set(monday, { weekLabel: buildWeekLabel(monday), isoWeekStart: monday, days: [] });
    }
    map.get(monday).days.push(day);
  }
  return [...map.values()];
}

/** Derives the top-level "open" | "limited" | "closed" status from all days. */
function overallStatus(days) {
  if (days.some((d) => d.status === 'available' || d.status === 'flash-day')) return 'open';
  if (days.some((d) => d.status === 'limited'))                               return 'limited';
  return 'closed';
}

/** Human-readable status label shown at the top of the availability page. */
function buildStatusLabel(status, days) {
  if (status === 'closed') return 'No upcoming openings \u2014 please inquire';
  const open    = days.filter((d) => d.status === 'available' || d.status === 'flash-day');
  const limited = days.filter((d) => d.status === 'limited');
  if (status === 'limited') return `Limited availability \u2014 ${monthRange(limited.map((d) => d.date))}`;
  return `Accepting requests for ${monthRange(open.map((d) => d.date))}`;
}

// ── CORS helper ───────────────────────────────────────────────────────────────

function buildCORSHeaders(origin, allowedOrigin) {
  const allowed =
    origin === allowedOrigin ||
    origin === 'http://localhost' ||
    /^http:\/\/localhost:\d+$/.test(origin) ||
    /^http:\/\/127\.0\.0\.1(:\d+)?$/.test(origin);

  if (!allowed) return null;
  return {
    'Access-Control-Allow-Origin':  origin,
    'Access-Control-Allow-Methods': 'GET, OPTIONS',
    'Access-Control-Allow-Headers': 'Content-Type',
    'Access-Control-Max-Age':       '86400',
  };
}

// ── Fallback response (always valid JSON, never leaks error details) ───────────

function makeFallback(corsHeaders) {
  return new Response(
    JSON.stringify({
      status:      'unknown',
      statusLabel: 'Availability temporarily unavailable',
      fallback:    true,
      stale:       false,
      disclaimer:  DISCLAIMER,
    }),
    {
      status:  200,
      headers: { 'Content-Type': 'application/json', ...corsHeaders },
    },
  );
}

// ── Worker entry point ────────────────────────────────────────────────────────

export default {
  async fetch(request, env, ctx) {
    const url    = new URL(request.url);
    const origin = request.headers.get('Origin') ?? '';
    const allowedOrigin = env.ALLOWED_ORIGIN ?? 'https://taylorhelmantattoo.com';

    const cors = buildCORSHeaders(origin, allowedOrigin);

    // Reject untrusted cross-origin browser requests
    if (origin && !cors) {
      return new Response('Forbidden', { status: 403 });
    }

    // Handle CORS preflight
    if (request.method === 'OPTIONS') {
      return new Response(null, { status: 204, headers: cors ?? {} });
    }

    // Only accept GET /api/availability
    if (request.method !== 'GET' || url.pathname !== '/api/availability') {
      return new Response('Not Found', { status: 404 });
    }

    // Guard: required secrets/vars must be present
    if (!env.GOOGLE_SERVICE_ACCOUNT_JSON || !env.CALENDAR_ID) {
      console.error('[availability] Missing required environment variables');
      return makeFallback(cors ?? {});
    }

    // ── Cache lookup ──────────────────────────────────────────────────────────
    const cache    = caches.default;
    const cacheKey = new Request(`https://${url.host}/api/availability`);
    const cached   = await cache.match(cacheKey);

    if (cached) {
      // Clone and re-attach CORS headers (they are origin-specific)
      const r = new Response(cached.body, cached);
      if (cors) Object.entries(cors).forEach(([k, v]) => r.headers.set(k, v));
      return r;
    }

    // ── Compute working-day date range ────────────────────────────────────────
    const tz          = env.TIMEZONE        ?? CONFIG.timezone;
    const calendarId  = env.CALENDAR_ID;
    const horizonDays = parseInt(env.BOOKING_HORIZON  ?? String(CONFIG.bookingHorizonDays), 10);
    const leadHrs     = parseInt(env.LEAD_TIME_HOURS  ?? String(CONFIG.leadTimeHours), 10);

    const now        = new Date();
    const leadCutoff = new Date(now.getTime() + leadHrs * 3_600_000);
    const today      = todayInTZ(tz);

    const workingDates = [];
    for (let i = 0; i <= horizonDays + 1; i++) {
      const d   = addDays(today, i);
      const dow = dayOfWeek(d);
      if (!CONFIG.workingDays.includes(dow)) continue;

      // Skip days whose entire working window ends before the lead-time cutoff
      const dayEndUTC = localHourToUTC(d, CONFIG.workingHours.end, tz);
      if (dayEndUTC <= leadCutoff) continue;

      workingDates.push(d);
    }

    // Edge case: no working days qualify (e.g. very long lead time, or a gap in schedule)
    if (!workingDates.length) {
      return new Response(
        JSON.stringify({
          status: 'closed', statusLabel: 'No upcoming openings \u2014 please inquire',
          fallback: false, stale: false, weeks: [],
          generatedAt: now.toISOString(), disclaimer: DISCLAIMER,
        }),
        { status: 200, headers: { 'Content-Type': 'application/json', ...(cors ?? {}) } },
      );
    }

    const rangeStart = localHourToUTC(workingDates[0],                     CONFIG.workingHours.start, tz);
    const rangeEnd   = localHourToUTC(workingDates[workingDates.length - 1], CONFIG.workingHours.end,   tz);

    // ── Query Google Calendar free/busy ───────────────────────────────────────
    let busyPairs;
    try {
      const token = await serviceAccountToken(env.GOOGLE_SERVICE_ACCOUNT_JSON);
      busyPairs   = await fetchBusy(token, calendarId, rangeStart, rangeEnd);
    } catch (err) {
      console.error('[availability] Google API error:', err.message);
      // Return fallback — visitor path is never broken
      return makeFallback(cors ?? {});
    }

    // ── Transform busy blocks → availability ──────────────────────────────────
    const days   = workingDates.map((d) => buildDay(d, busyPairs, tz));
    const weeks  = groupIntoWeeks(days);
    const status = overallStatus(days);

    // Find the next day that has any openings (for the summary card on index.html)
    const nextOpen = days.find(
      (d) => d.status === 'available' || d.status === 'flash-day' || d.status === 'limited',
    );

    const responsePayload = {
      status,
      statusLabel:        buildStatusLabel(status, days),
      timezone:           tz,
      generatedAt:        now.toISOString(),
      cacheTtlSeconds:    CONFIG.cacheTtlSeconds,
      stale:              false,
      fallback:           false,
      nextAvailableDate:  nextOpen?.date  ?? null,
      nextAvailableLabel: nextOpen ? friendlyDate(nextOpen.date, tz) : null,
      weeks,
      disclaimer:         DISCLAIMER,
    };

    const response = new Response(JSON.stringify(responsePayload), {
      status:  200,
      headers: {
        'Content-Type':  'application/json',
        'Cache-Control': `public, max-age=${CONFIG.cacheTtlSeconds}`,
        ...(cors ?? {}),
      },
    });

    // Store in Cloudflare edge cache — fire-and-forget, does not block response
    ctx.waitUntil(cache.put(cacheKey, response.clone()));

    return response;
  },
};
