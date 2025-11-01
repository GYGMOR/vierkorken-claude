import Link from "next/link";

async function fetchCategories() {
  const base = process.env.NEXT_PUBLIC_BASE_PATH || "";
  const res = await fetch(`${base}/api/klara/categories`, { cache: "no-store" });
  if (!res.ok) throw new Error("Failed to load categories");
  return res.json();
}

export default async function Home() {
  const categories = await fetchCategories();

  return (
    <main>
      <div style={{ display:"flex", justifyContent:"space-between", alignItems:"baseline" }}>
        <h2 style={{ fontSize: 22, marginBottom: 12 }}>Kategorien</h2>
        <Link href="/all" style={{ color:"#9b5cff" }}>→ Alle Artikel</Link>
      </div>

      <div style={{ display:"grid", gridTemplateColumns:"repeat(auto-fill, minmax(220px, 1fr))", gap:16 }}>
        {categories.map((cat) => (
          <Link key={cat.id} href={`/category/${cat.id}`} style={{ textDecoration:"none" }}>
            <div style={{ background:"#151515", borderRadius:16, padding:16, border:"1px solid #222" }}>
              <div style={{ fontSize:14, opacity:.7 }}>#{cat.id}</div>
              <div style={{ fontSize:18, fontWeight:600, color:"#fff" }}>
                {cat.nameDE}
              </div>
              <div style={{ marginTop:8, fontSize:12, opacity:.7 }}>
                Reihenfolge: {cat.order ?? "—"}
              </div>
              {cat.active === false && (
                <div style={{ marginTop:8, color:"#ff7b72" }}>Inaktiv</div>
              )}
            </div>
          </Link>
        ))}
      </div>
    </main>
  );
}
