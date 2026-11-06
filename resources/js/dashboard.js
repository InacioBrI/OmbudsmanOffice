import ApexCharts from 'apexcharts';

const dataElement = document.getElementById('dashboard-chart-data');
const dashboard = document.getElementById('dashboard');

if (dataElement && dashboard) {
    const data = JSON.parse(dataElement.textContent);
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const number = new Intl.NumberFormat('pt-BR');
    const base = {
        chart: {
            fontFamily: 'Instrument Sans, sans-serif',
            foreColor: '#64748b',
            toolbar: { show: false },
            animations: { enabled: !reducedMotion },
        },
        dataLabels: { enabled: false },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 3 },
        tooltip: { theme: 'light', y: { formatter: (value) => number.format(value) } },
    };

    const trend = new ApexCharts(dashboard.querySelector('#grafico-tendencia'), {
        ...base,
        chart: { ...base.chart, type: 'area', height: 288, zoom: { enabled: false } },
        series: [{ name: 'Total geral', data: data.mensal.total }],
        colors: ['#0b1f3a'],
        stroke: { width: 3, curve: 'smooth' },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.22, opacityTo: 0.02 } },
        markers: { size: 4, hover: { size: 6 } },
        xaxis: { categories: data.mensal.labels, labels: { style: { fontSize: '11px' } } },
        yaxis: { min: 0, forceNiceScale: true, labels: { formatter: (value) => number.format(Math.round(value)) } },
    });
    trend.render();

    const series = {
        total: { name: 'Total geral', color: '#0b1f3a' },
        emAndamento: { name: 'Em andamento', color: '#d97706' },
        encerradas: { name: 'Encerradas', color: '#059669' },
    };

    dashboard.querySelectorAll('[data-series]').forEach((button) => {
        button.addEventListener('click', () => {
            const selected = button.dataset.series;
            dashboard.querySelectorAll('[data-series]').forEach((tab) => {
                const active = tab === button;
                tab.setAttribute('aria-pressed', String(active));
                tab.classList.toggle('border-[#0b1f3a]', active);
                tab.classList.toggle('text-[#0b1f3a]', active);
                tab.classList.toggle('font-semibold', active);
                tab.classList.toggle('border-transparent', !active);
                tab.classList.toggle('text-slate-500', !active);
                tab.classList.toggle('font-medium', !active);
            });
            trend.updateOptions({
                colors: [series[selected].color],
                series: [{ name: series[selected].name, data: data.mensal[selected] }],
            });
        });
    });

    if (dashboard.querySelector('#grafico-categorias')) {
        new ApexCharts(dashboard.querySelector('#grafico-categorias'), {
            ...base,
            chart: { ...base.chart, type: 'bar', height: 280 },
            series: [{ name: 'Manifestações', data: data.categorias.values }],
            colors: ['#34d399'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '48%' } },
            xaxis: { categories: data.categorias.labels, labels: { rotate: -35, trim: false, style: { fontSize: '10px' } } },
            yaxis: { min: 0, forceNiceScale: true, labels: { formatter: (value) => number.format(Math.round(value)) } },
            responsive: [{
                breakpoint: 640,
                options: {
                    chart: { height: 300 },
                    plotOptions: { bar: { horizontal: true, barHeight: '55%' } },
                    xaxis: { labels: { rotate: 0 } },
                },
            }],
        }).render();
    }

    if (dashboard.querySelector('#grafico-status')) {
        new ApexCharts(dashboard.querySelector('#grafico-status'), {
            ...base,
            chart: { ...base.chart, type: 'donut', height: 264 },
            series: data.status.values,
            labels: data.status.labels,
            colors: data.status.colors,
            legend: { show: false },
            stroke: { width: 2, colors: ['#fff'] },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            name: { show: true, formatter: () => 'Total' },
                            value: { show: true, formatter: (value) => number.format(Number(value)) },
                            total: { show: true, label: 'Total', formatter: () => number.format(data.status.values.reduce((sum, value) => sum + value, 0)) },
                        },
                    },
                },
            },
        }).render();
    }
}
