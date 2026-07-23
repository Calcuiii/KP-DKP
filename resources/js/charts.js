import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', () => {
    // ── Dashboard page ──────────────────────────────────────────────
    if (window.dashboardData) {
        const { trend, statusData, categoryData } = window.dashboardData;

        const trendCanvas = document.getElementById('trendChart');
        if (trendCanvas) {
            new Chart(trendCanvas, {
                type: 'line',
                data: {
                    labels: trend.map((t) => t.day),
                    datasets: [
                        {
                            label: 'Pertanyaan',
                            data: trend.map((t) => t.pertanyaan),
                            borderColor: '#1A5FA8',
                            backgroundColor: 'rgba(26,95,168,0.12)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 0,
                        },
                        {
                            label: 'Dijawab',
                            data: trend.map((t) => t.dijawab),
                            borderColor: '#0D9E8A',
                            borderDash: [4, 3],
                            fill: false,
                            tension: 0.35,
                            pointRadius: 0,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { labels: { font: { size: 11 } } } },
                    scales: {
                        x: { ticks: { font: { size: 10 } } },
                        y: { ticks: { font: { size: 10 } } },
                    },
                },
            });
        }

        const statusCanvas = document.getElementById('statusChart');
        if (statusCanvas) {
            new Chart(statusCanvas, {
                type: 'doughnut',
                data: {
                    labels: statusData.map((s) => s.name),
                    datasets: [{
                        data: statusData.map((s) => s.value),
                        backgroundColor: statusData.map((s) => s.color),
                    }],
                },
                options: {
                    plugins: { legend: { display: false } },
                    cutout: '65%',
                },
            });
        }

        const categoryCanvas = document.getElementById('categoryChart');
        if (categoryCanvas) {
            new Chart(categoryCanvas, {
                type: 'bar',
                data: {
                    labels: categoryData.map((c) => c.name),
                    datasets: [{
                        data: categoryData.map((c) => c.value),
                        backgroundColor: '#1A5FA8',
                        borderRadius: 4,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { font: { size: 10 } } },
                        y: { ticks: { font: { size: 10 } } },
                    },
                },
            });
        }
    }

    // ── Analytics page ──────────────────────────────────────────────
    if (window.analyticsData) {
        const { questionTrend, categoryData } = window.analyticsData;

        const qtCanvas = document.getElementById('questionTrendChart');
        if (qtCanvas) {
            new Chart(qtCanvas, {
                type: 'line',
                data: {
                    labels: questionTrend.map((t) => t.day.slice(5)),
                    datasets: [{
                        label: 'Pertanyaan',
                        data: questionTrend.map((t) => t.total),
                        borderColor: '#1A5FA8',
                        backgroundColor: 'rgba(26,95,168,0.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 0,
                    }],
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: { x: { ticks: { font: { size: 10 } } }, y: { ticks: { font: { size: 10 } } } },
                },
            });
        }

        const catCanvas = document.getElementById('analyticsCategoryChart');
        if (catCanvas) {
            new Chart(catCanvas, {
                type: 'bar',
                data: {
                    labels: categoryData.map((c) => c.name),
                    datasets: [{ data: categoryData.map((c) => c.value), backgroundColor: '#1A5FA8', borderRadius: 4 }],
                },
                options: {
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: { x: { ticks: { font: { size: 10 } } }, y: { ticks: { font: { size: 10 } } } },
                },
            });
        }
    }
});