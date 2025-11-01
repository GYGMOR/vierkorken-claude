// app/api/klara/image/route.js
// Proxy für KLARA-Bilder, damit der Browser keinen API-Key kennen muss.

export const dynamic = "force-dynamic"; // immer serverseitig frisch
export const runtime = "nodejs";

/**
 * Sicherheits-Whitelist: Erlaubte Pfade relativ zur KLARA Base-URL.
 * (Artikelbilder und – falls gebraucht – Kategoriebilder.)
 */
function isAllowedHref(href, base) {
  const b = base.replace(/\/$/, "");
  const allowedStarts = [
    `${b}/core/latest/articles/`,               // .../articles/{id}/images/{imgId}
    `${b}/core/latest/article-categories/`,     // .../article-categories/{id}/images/{imgId}  (optional)
  ];
  return allowedStarts.some((p) => href.startsWith(p)) && href.includes("/images/");
}

async function proxyImage(href, base, key, tenantId) {
  // Upstream-Request mit benötigten Headers (API-Key + Tenant)
  const upstream = await fetch(href, {
    method: "GET",
    headers: {
      "x-api-key": key,
      "x-tenant-id": tenantId,
      "accept": "*/*", // Bilder kommen als image/* zurück
    },
    // Wir lassen Caching-Header vom Upstream durch (siehe unten),
    // also hier kein explizites cache: "no-store"
  });

  if (!upstream.ok) {
    const text = await upstream.text().catch(() => "");
    return new Response(
      `Upstream error ${upstream.status}\n${text}`,
      { status: 502 }
    );
  }

  // Content-Type & Cache-Control aus Upstream übernehmen (falls gesetzt)
  const headers = new Headers();
  const ct = upstream.headers.get("content-type") || "application/octet-stream";
  headers.set("content-type", ct);

  // sinnvolles Default-Caching setzen, wenn Upstream nichts liefert
  const cc = upstream.headers.get("cache-control") || "public, max-age=3600, stale-while-revalidate=86400";
  headers.set("cache-control", cc);

  const etag = upstream.headers.get("etag");
  if (etag) headers.set("etag", etag);

  // Bilddaten streamen
  const buf = await upstream.arrayBuffer();
  return new Response(buf, { status: 200, headers });
}

export async function GET(req) {
  const url = new URL(req.url);
  const href = url.searchParams.get("href");

  const base = process.env.KLARA_API_BASEURL;
  const key = process.env.KLARA_API_KEY;
  const tenantId = process.env.KLARA_TENANT_ID;

  if (!href) {
    return new Response("Missing `href` query param", { status: 400 });
  }
  if (!base || !key || !tenantId) {
    return new Response("KLARA env vars missing", { status: 500 });
  }

  // Sicherheits-Check: nur freigegebene KLARA-Endpunkte erlauben
  if (!isAllowedHref(href, base)) {
    return new Response("Forbidden href", { status: 403 });
  }

  try {
    return await proxyImage(href, base, key, tenantId);
  } catch (e) {
    return new Response(`Proxy error: ${String(e)}`, { status: 500 });
  }
}
