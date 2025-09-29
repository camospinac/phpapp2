<script setup lang="ts">
import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

// Helper function to load a TradingView script
const createTradingViewWidget = (containerId: string, widgetConfig: object) => {
    const container = document.getElementById(containerId);
    if (container) {
        // Clear previous content
        container.innerHTML = '';

        const script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = widgetConfig.src; // Use the specific source URL
        script.async = true;
        // The configuration is passed as a string in the script's innerHTML
        script.innerHTML = JSON.stringify(widgetConfig.config);

        container.appendChild(script);
    }
};

onMounted(() => {
    // Ticker Tape Widget
    createTradingViewWidget('ticker-tape-container', {
        src: 'https://s3.tradingview.com/external-embedding/embed-widget-ticker-tape.js',
        config: {
            "symbols": [
                { "proName": "FOREXCOM:SPXUSD", "title": "S&P 500" },
                { "proName": "FOREXCOM:NSXUSD", "title": "US 100" },
                { "proName": "FX_IDC:EURUSD", "title": "EUR/USD" },
                { "proName": "BITSTAMP:BTCUSD", "title": "Bitcoin" },
                { "proName": "BITSTAMP:ETHUSD", "title": "Ethereum" }
            ],
            "showSymbolLogo": true, "colorTheme": "light", "isTransparent": false, "displayMode": "adaptive", "locale": "es"
        }
    });

    // Advanced Chart Widget
    createTradingViewWidget('advanced-chart-container', {
        src: 'https://s3.tradingview.com/external-embedding/embed-widget-advanced-chart.js',
        config: {
            "autosize": true, "symbol": "NASDAQ:AAPL", "interval": "D", "timezone": "Etc/UTC", "theme": "light", "style": "1", "locale": "es", "allow_symbol_change": true
        }
    });

    // Market Overview Widget
    createTradingViewWidget('market-overview-container', {
        src: 'https://s3.tradingview.com/external-embedding/embed-widget-market-overview.js',
        config: {
            "colorTheme": "light", "dateRange": "12M", "showChart": true, "locale": "es", "largeChartUrl": "", "isTransparent": false, "showSymbolLogo": true, "showFloatingTooltip": false, "width": "100%", "height": "100%",
            "tabs": [
                { "title": "Índices", "symbols": [{ "s": "FOREXCOM:SPXUSD", "d": "S&P 500" }, { "s": "FOREXCOM:NSXUSD", "d": "Nasdaq 100" }], "originalTitle": "Indices" },
                { "title": "Futuros", "symbols": [{ "s": "CME_MINI:ES1!", "d": "S&P 500" }, { "s": "CME:6E1!", "d": "Euro" }], "originalTitle": "Futures" }
            ]
        }
    });

    // Stock Heatmap Widget
    createTradingViewWidget('stock-heatmap-container', {
        src: 'https://s3.tradingview.com/external-embedding/embed-widget-stock-heatmap.js',
        config: {
            "exchanges": [], "dataSource": "SPX500", "grouping": "sector", "blockSize": "market_cap_basic", "blockColor": "change", "locale": "es", "symbolUrl": "", "colorTheme": "light", "width": "100%", "height": "100%"
        }
    });
});
</script>

<template>

    <Head title="Mercado" />
    <AppLayout>
        <div class="p-4 md:p-6 space-y-6">


            <h1 class="text-3xl font-bold">Resumen del Mercado</h1>

            <div id="ticker-tape-container" class="tradingview-widget-container">
                <div class="tradingview-widget-container__widget"></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[90vh]">

                <div class="lg:col-span-2 rounded-xl border bg-card text-card-foreground p-2 h-[70vh]">
                    <div id="advanced-chart-container" class="tradingview-widget-container h-full"></div>
                </div>

                <div class="rounded-xl border bg-card text-card-foreground">
                    <div id="market-overview-container" class="tradingview-widget-container h-full">
                        <div class="tradingview-widget-container__widget h-full"></div>
                    </div>
                </div>

                <div class="lg:col-span-3 rounded-xl border bg-card text-card-foreground p-2 h-[70vh]">
                    <div id="stock-heatmap-container" class="tradingview-widget-container h-full">
                        <div class="tradingview-widget-container__widget h-full"></div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>