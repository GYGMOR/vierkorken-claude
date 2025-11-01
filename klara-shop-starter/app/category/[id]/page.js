// app/category/[id]/page.js
import Link from "next/link";

async function fetchArticlesByCategory(id) {
  const base = process.env.NEXT_PUBLIC_BASE_PATH || "";
  const res = await fetch(`${base}/api/klara/articles?categoryId=${encodeURIComponent(id)}`, {
    cache: "no-store",
  });
  if (!res.ok) throw new Error("Fehler beim Laden der Artikel");
  return res.json();
}

export default async function CategoryPage({ params }) {
  const id = params.id; // Kategoried-ID aus URL
  const items = await fetchArticlesByCategory(id);

  return (
    <main>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "baseline" }}>
        <h2 style={{ fontSize: 24 }}>Kategorie {id}</h2>
        <Link href="/" style={{ color: "#a78bfa" }}>← Zur Übersicht</Link>
      </div>

      {(!items || items.length === 0) && (
        <p style={{ opacity: 0.7 }}>Keine Artikel gefunden.</p>
      )}

      <div
        style={{
          display: "grid",
          gridTemplateColumns: "repeat(auto-fill, minmax(320px, 1fr))",
          gap: 16,
        }}
      >
        {items?.map((a) => (
          <div key={a.id} style={{ background: "#151515", borderRadius: 16, padding: 16, border: "1px solid #222" }}>
            <div style={{ fontSize: 12, opacity: 0.7 }}>#{a.id} · {a.articleNumber || "—"}</div>
            <div style={{ fontSize: 18, fontWeight: 700, marginTop: 6 }}>{a.nameDE}</div>
            <div style={{ marginTop: 12, fontSize: 14 }}>
              Preis: {a.price != null ? `${a.price.toFixed(2)} CHF` : "—"}
            </div>
          </div>
        ))}
      </div>

      <p style={{ marginTop: 24, opacity: 0.5 }}>Nur Test-UI.</p>
    </main>
  );
}
