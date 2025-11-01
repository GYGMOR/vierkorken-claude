// Test um Lagerbestand/Inventory Endpunkte zu finden
const API = "https://api.klara.ch";
const KEY = "01c11c3e-c484-4ce7-bca0-3f52eb3772af";

async function testInventory() {
  console.log("=== SUCHE LAGERBESTAND/INVENTORY ENDPUNKTE ===\n");

  // Teste verschiedene mögliche Endpunkte
  const endpoints = [
    "/core/latest/inventory",
    "/core/latest/stock",
    "/core/latest/stocks",
    "/core/latest/warehouse",
    "/core/latest/warehouses",
    "/core/latest/inventory-items",
    "/core/latest/article-stock",
    "/core/latest/article-stocks",
    "/inventory/latest/articles",
    "/inventory/latest/stocks",
    "/stock/latest/articles",
    "/warehouse/latest/inventory",
    "/warehouse/latest/articles",
  ];

  const workingEndpoints = [];

  for (const endpoint of endpoints) {
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
        const count = Array.isArray(data) ? data.length : (data.items?.length || "?");
        console.log(`✓ ${endpoint}: ${count} Items`);
        workingEndpoints.push(endpoint);

        // Zeige Struktur des ersten Items
        if (Array.isArray(data) && data.length > 0) {
          console.log("  Beispiel:", JSON.stringify(data[0], null, 2).substring(0, 500) + "...");
        }
      } else {
        console.log(`✗ ${endpoint}: ${res.status}`);
      }
    } catch (e) {
      console.log(`✗ ${endpoint}: Error - ${e.message}`);
    }
  }

  // Prüfe auch, ob Artikel bereits Lagerbestand-Informationen enthalten
  console.log("\n\n=== PRÜFE ARTIKEL AUF LAGERBESTAND-FELDER ===\n");

  const artRes = await fetch(`${API}/core/latest/articles?limit=10`, {
    headers: {
      "accept": "application/json",
      "Accept-Language": "de",
      "X-API-KEY": KEY,
    }
  });

  const articles = await artRes.json();

  if (articles.length > 0) {
    const firstArticle = articles[0];
    console.log("Erster Artikel (ID: " + firstArticle.id + "):");
    console.log("Alle Felder:", Object.keys(firstArticle).join(", "));

    // Suche nach Lagerbestand-relevanten Feldern
    const stockFields = Object.keys(firstArticle).filter(key =>
      key.toLowerCase().includes('stock') ||
      key.toLowerCase().includes('inventory') ||
      key.toLowerCase().includes('quantity') ||
      key.toLowerCase().includes('amount') ||
      key.toLowerCase().includes('warehouse')
    );

    if (stockFields.length > 0) {
      console.log("\n✓ Gefundene Lagerbestand-Felder:");
      stockFields.forEach(field => {
        console.log(`  - ${field}:`, firstArticle[field]);
      });
    } else {
      console.log("\n✗ Keine Lagerbestand-Felder direkt im Artikel gefunden");
    }

    // Zeige komplette Artikel-Struktur
    console.log("\n\nKompletter erster Artikel:");
    console.log(JSON.stringify(firstArticle, null, 2));
  }

  console.log("\n\n=== ZUSAMMENFASSUNG ===");
  console.log(`Funktionierende Endpunkte: ${workingEndpoints.length}`);
  if (workingEndpoints.length > 0) {
    console.log("Liste:");
    workingEndpoints.forEach(ep => console.log(`  - ${ep}`));
  }
}

testInventory().catch(console.error);
