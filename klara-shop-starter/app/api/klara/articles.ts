// Pseudocode/TS – an deine Struktur anpassen
import type { NextApiRequest, NextApiResponse } from 'next';

// Hilfsfilter: beide Welten unterstützen, aber nichts erzwingen
function matchesFilters(a: any, q: {
  posCategoryId?: string;
  onlineShopCategoryId?: string;
  search?: string;
}) {
  const { posCategoryId, onlineShopCategoryId, search } = q;

  const posOk = posCategoryId
    ? a.posCategories?.some((c: any) => String(c.id) === String(posCategoryId))
    : true;

  const onlineOk = onlineShopCategoryId
    ? a.onlineShopCategories?.some((c: any) => String(c.id) === String(onlineShopCategoryId))
    : true;

  const searchOk = search
    ? (a.nameDE?.toLowerCase().includes(search.toLowerCase()) ||
       a.articleNumber?.toLowerCase().includes(search.toLowerCase()))
    : true;

  return posOk && onlineOk && searchOk;
}

export default async function handler(req: NextApiRequest, res: NextApiResponse) {
  try {
    const { posCategoryId, onlineShopCategoryId, search } = req.query;

    // 1) ALLE Artikel holen (pagineiren, falls nötig)
    // Wichtig: KEIN sellInOnlineShop-Filter hier!
    const all: any[] = await fetchAllFromKlara('/core/latest/articles'); // <- deine bestehende Fetch-Funktion

    // 2) Filtern – aber locker
    const items = all.filter(a => matchesFilters(a, {
      posCategoryId: posCategoryId as string,
      onlineShopCategoryId: onlineShopCategoryId as string,
      search: search as string
    }));

    // 3) Auf minimale Form mappen (ohne Bilder)
    const minimal = items.map(a => ({
      id: a.id,
      articleNumber: a.articleNumber,
      name: a.nameDE ?? a.nameEN ?? '',
      price: a.pricePeriods?.[0]?.price ?? 0,
      vatCode: a.vats?.[0]?.vatCode ?? null,
      // optional für UI:
      posCategoryIds: a.posCategories?.map((c: any) => c.id) ?? [],
      onlineShopCategoryIds: a.onlineShopCategories?.map((c: any) => c.id) ?? [],
      sellInOnlineShop: !!a.sellInOnlineShop,
    }));

    // 4) Optional: Lagerbestand dazumergen (wenn dein Proxy das schon kann)
    //    Du hast ja /api/klara/... – bau dort eine Stock-Route, die die KLARA-Inventur abfragt.
    //    Beispiel: /api/klara/stock?ids=10,11,12
    //    Dann hier mergen:
    // const stocks = await fetch(`/api/klara/stock?ids=${minimal.map(x=>x.id).join(',')}`).then(r=>r.json());
    // const byId = new Map(stocks.map((s:any)=>[String(s.id), s]));
    // minimal.forEach(x => x.stock = byId.get(String(x.id))?.stock ?? null);

    res.status(200).json({ count: minimal.length, items: minimal });
  } catch (e:any) {
    res.status(500).json({ error: e?.message ?? 'Unknown error' });
  }
}
