// Versuche API Dokumentation zu finden
const API = "https://api.klara.ch";
const KEY = "01c11c3e-c484-4ce7-bca0-3f52eb3772af";

async function testApiDocs() {
  console.log("=== SUCHE API DOKUMENTATION ===\n");

  const docPaths = [
    "/swagger.json",
    "/openapi.json",
    "/api-docs",
    "/docs.json",
    "/v2/api-docs",
    "/core/swagger.json",
    "/core/openapi.json",
    "/resources/group-swagger-definitions.json",
    "/docs/resources/group-swagger-definitions.json",
  ];

  for (const path of docPaths) {
    try {
      const res = await fetch(`${API}${path}`, {
        headers: {
          "accept": "application/json",
          "X-API-KEY": KEY,
        }
      });

      if (res.ok) {
        const data = await res.json();
        console.log(`\n✓ ${path} gefunden!`);

        // Suche nach Inventory/Stock-bezogenen Pfaden
        const paths = data.paths || {};
        const inventoryPaths = Object.keys(paths).filter(p =>
          p.toLowerCase().includes('inventory') ||
          p.toLowerCase().includes('stock') ||
          p.toLowerCase().includes('warehouse') ||
          p.toLowerCase().includes('quantity')
        );

        if (inventoryPaths.length > 0) {
          console.log("\n✓ INVENTORY-BEZOGENE ENDPUNKTE GEFUNDEN:");
          inventoryPaths.forEach(p => console.log(`   - ${p}`));
        }

        // Zeige alle verfügbaren Pfade
        console.log(`\n   Alle verfügbaren Pfade (${Object.keys(paths).length}):`);
        Object.keys(paths).slice(0, 20).forEach(p => console.log(`   - ${p}`));
        if (Object.keys(paths).length > 20) {
          console.log(`   ... und ${Object.keys(paths).length - 20} weitere`);
        }
      }
    } catch (e) {
      // Ignoriere
    }
  }

  // Teste direkt mit curl-ähnlichem Ansatz - prüfe alle /core/latest/* Endpunkte
  console.log("\n\n=== TESTE SYSTEMATISCH ALLE CORE/LATEST ENDPUNKTE ===\n");

  const possibleResources = [
    "inventories",
    "inventory-items",
    "stocks",
    "stock-items",
    "warehouses",
    "warehouse-locations",
    "storage-locations",
    "article-quantities",
    "product-quantities",
    "quantities",
    "available-stock",
    "inventory-transactions",
    "stock-movements",
  ];

  for (const resource of possibleResources) {
    try {
      const res = await fetch(`${API}/core/latest/${resource}`, {
        headers: {
          "accept": "application/json",
          "Accept-Language": "de",
          "X-API-KEY": KEY,
        }
      });

      if (res.ok) {
        const data = await res.json();
        const count = Array.isArray(data) ? data.length : "?";
        console.log(`✓ /core/latest/${resource}: ${count} Items gefunden!`);

        // Zeige erste paar Felder
        if (Array.isArray(data) && data.length > 0) {
          console.log(`   Felder: ${Object.keys(data[0]).slice(0, 10).join(", ")}`);
          if (data.length > 0) {
            console.log(`   Beispiel:`, JSON.stringify(data[0], null, 2).substring(0, 300));
          }
        }
      }
    } catch (e) {
      // Ignoriere
    }
  }
}

testApiDocs().catch(console.error);
