// app/api/klara/articles/route.js
import { NextResponse } from "next/server";

const API = process.env.KLARA_API_BASEURL || "https://api.klara.ch";
const KEY = process.env.KLARA_API_KEY;

function mapArticle(a) {
  const price = Array.isArray(a.pricePeriods) && a.pricePeriods.length
    ? Number(a.pricePeriods[0].price ?? 0)
    : null;

  const catIds = (a.posCategories || [])
    .map((c) => (c && c.id != null ? String(c.id) : null))
    .filter(Boolean);

  return {
    id: String(a.id),
    articleNumber: a.articleNumber || null,
    nameDE: a.nameDE || a.nameEN || "Artikel",
    price,
    image: null,            // Bilder weggelassen
    categories: catIds,     // wichtig fürs Filtern
  };
}

export async function GET(req) {
  try {
    if (!KEY) {
      return NextResponse.json(
        { error: "No API key or access token provided" },
        { status: 502 }
      );
    }

    // categoryId optional aus der Query lesen
    const { searchParams } = new URL(req.url);
    const categoryId = searchParams.get("categoryId")?.trim() || null;

    // ✅ WICHTIG: limit=1000 hinzufügen, sonst werden nur ~48 Artikel zurückgegeben!
    const res = await fetch(`${API}/core/latest/articles?limit=1000`, {
      headers: {
        accept: "application/json",
        "Accept-Language": "de",
        "X-API-KEY": KEY,
      },
      cache: "no-store",
    });

    if (!res.ok) {
      const body = await res.text().catch(() => "");
      return NextResponse.json(
        { error: "KLARA articles error", status: res.status, body },
        { status: 502 }
      );
    }

    const raw = await res.json();
    const all = (Array.isArray(raw) ? raw : []).map(mapArticle);

    // ✅ serverseitig nach Kategorie filtern (genau EINE Liste pro Kategorie)
    const items = categoryId
      ? all.filter((a) => a.categories?.includes(String(categoryId)))
      : all;

    return NextResponse.json(items, { headers: { "Cache-Control": "no-store" } });
  } catch (e) {
    return NextResponse.json({ error: String(e?.message || e) }, { status: 502 });
  }
}
