// WebMCP: yapay zekâ tarayıcı asistanlarına (destekleyen tarayıcılarda) sitede arama aracı tanıtır.
// Tarayıcı desteklemiyorsa hiçbir şey yapmaz. Araç sadece herkese açık arama sonucunu (şehir, yer, aktivite, yazı) döndürür.
const context = navigator.modelContext;

if (context && typeof context.registerTool === 'function') {
    const locale = document.documentElement.lang || 'en';

    try {
        context.registerTool({
            name: 'search_tripspoiler',
            description: 'Search TripSpoiler for cities, attractions, tours, tickets and travel guides. Returns names and page links.',
            inputSchema: {
                type: 'object',
                properties: {
                    query: { type: 'string', description: 'What to look for, e.g. "Colosseum tickets" or "Istanbul"' },
                },
                required: ['query'],
            },
            async execute({ query }) {
                const response = await fetch(`/${locale}/search?q=${encodeURIComponent(query ?? '')}`, {
                    headers: { Accept: 'application/json' },
                });
                const data = response.ok ? await response.json() : { search: [] };
                const results = (data.search ?? []).map(({ type, name, subtitle, url }) => ({ type, name, subtitle, url }));

                return { content: [{ type: 'text', text: JSON.stringify(results) }] };
            },
        });
    } catch {
        // Aynı araç zaten kayıtlıysa ya da tarayıcı reddederse sessizce geç
    }
}
