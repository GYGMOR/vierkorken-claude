// Erweiterte Suche nach Lagerbestand
const API = "https://api.klara.ch";
const KEY = "01c11c3e-c484-4ce7-bca0-3f52eb3772af";

async function testInventoryDetailed() {
  console.log("=== ERWEITERTE LAGERBESTAND-SUCHE ===\n");

  // 1. Hole einen einzelnen Artikel mit allen Details
  console.log("1. Lade einzelnen Artikel mit allen Details...");
  const articleRes = await fetch(`${API}/core/latest/articles/12`, {
    headers: {
      "accept": "application/json",
      "Accept-Language": "de",
      "X-API-KEY": KEY,
    }
  });

  const article = await articleRes.json();
  console.log("\nArtikel Links:");
  if (article._links) {
    Object.keys(article._links).forEach(key => {
      console.log(`  - ${key}:`, article._links[key]);
    });
  }

  // 2. Teste verschiedene Warehouse-bezogene Endpunkte
  console.log("\n\n2. Teste Warehouse/Lager Endpunkte...");
  const warehouseEndpoints = [
    "/warehouse/latest/locations",
    "/warehouse/latest/location",
    "/core/latest/locations",
    "/core/latest/storage-locations",
    "/core/latest/article-quantities",
    "/core/latest/article/12/quantity",
    "/core/latest/article/12/stock",
    "/core/latest/articles/12/stock",
    "/core/latest/articles/12/inventory",
    "/pos/latest/inventory",
    "/pos/latest/stock",
  ];

  for (const endpoint of warehouseEndpoints) {
    try {
      const res = await fetch(`${API}${endpoint}`, {
        headers: {
          "accept": "application/json",
          "Accept-Language": "de",
          "X-API-KEY": KEY,
        }
      });

      if (res.ok) {
        const data = await res.json();
        console.log(`\n✓ ${endpoint}:`);
        console.log("   Daten:", JSON.stringify(data, null, 2).substring(0, 500));
      }
    } catch (e) {
      // Ignoriere Fehler
    }
  }

  // 3. Prüfe ob es eine API-Dokumentation gibt
  console.log("\n\n3. Teste API Root/Dokumentation...");
  try {
    const rootRes = await fetch(`${API}/`, {
      headers: {
        "accept": "application/json",
        "X-API-KEY": KEY,
      }
    });

    if (rootRes.ok) {
      const root = await rootRes.json();
      console.log("✓ API Root Response:");
      console.log(JSON.stringify(root, null, 2));
    }
  } catch (e) {
    console.log("✗ Keine Root-Dokumentation verfügbar");
  }

  // 4. Teste core/latest root
  console.log("\n\n4. Teste core/latest Root...");
  try {
    const coreRes = await fetch(`${API}/core/latest`, {
      headers: {
        "accept": "application/json",
        "X-API-KEY": KEY,
      }
    });

    if (coreRes.ok) {
      const core = await coreRes.json();
      console.log("✓ Core Latest Response:");
      console.log(JSON.stringify(core, null, 2));
    }
  } catch (e) {
    console.log("✗ Keine Core-Dokumentation verfügbar");
  }

  // 5. Teste mit verschiedenen Modulen
  console.log("\n\n5. Teste verschiedene Module...");
  const modules = [
    "core",
    "warehouse",
    "inventory",
    "stock",
    "pos",
    "shop",
    "accounting",
    "management"
  ];

  for (const module of modules) {
    try {
      const res = await fetch(`${API}/${module}/latest`, {
        headers: {
          "accept": "application/json",
          "X-API-KEY": KEY,
        }
      });

      if (res.ok) {
        console.log(`✓ ${module}/latest ist verfügbar`);
        const data = await res.json();
        if (data._links || data.links) {
          console.log("   Links:", JSON.stringify(data._links || data.links, null, 2));
        }
      }
    } catch (e) {
      // Ignoriere
    }
  }
}

testInventoryDetailed().catch(console.error);
