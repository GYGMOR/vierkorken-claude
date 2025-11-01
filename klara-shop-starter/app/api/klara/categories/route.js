import { NextResponse } from "next/server";

const API = process.env.KLARA_API_BASEURL || "https://api.klara.ch";
const KEY = process.env.KLARA_API_KEY;

export async function GET() {
  if (!KEY) {
    return NextResponse.json(
      { error: "No API key provided" },
      { status: 502 }
    );
  }

  // ✅ Sicherheitshalber limit=1000 hinzufügen
  const url = `${API}/core/latest/article-categories?limit=1000`;
  const res = await fetch(url, {
    headers: {
      accept: "application/json",
      "Accept-Language": "de",
      "X-API-KEY": KEY,
    },
    cache: "no-store",
  });

  if (!res.ok) {
    const text = await res.text().catch(() => "");
    return NextResponse.json(
      { error: "KLARA categories error", status: res.status, body: text },
      { status: 502 }
    );
  }

  const raw = await res.json();

  // aufräumen / vereinheitlichen
  const categories = (Array.isArray(raw) ? raw : []).map((c) => ({
    id: String(c.id),
    nameDE: c.nameDE || c.nameEN || "Kategorie",
    order: c.order ?? null,
    active: c.active !== false,
  }));

  // sortieren wie in KLARA
  categories.sort((a, b) => (a.order ?? 9999) - (b.order ?? 9999));

  return NextResponse.json(categories, { headers: { "Cache-Control": "no-store" } });
}
