import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Filler,
    Tooltip,
} from 'chart.js';

Chart.register(
    LineController,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Filler,
    Tooltip
);

function initMovementAreaChart() {
    const canvas = document.getElementById('movement-area-chart');
    if (!canvas) {
        return;
    }

    const labels = JSON.parse(canvas.dataset.labels || '[]');
    const inData = JSON.parse(canvas.dataset.in || '[]');
    const outData = JSON.parse(canvas.dataset.out || '[]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const ctx = canvas.getContext('2d');

    const createGradient = (r, g, b) => {
        const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height || 260);
        gradient.addColorStop(0, `rgba(${r}, ${g}, ${b}, 0.18)`);
        gradient.addColorStop(1, `rgba(${r}, ${g}, ${b}, 0.0)`);
        return gradient;
    };

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Masuk',
                    data: inData,
                    borderColor: '#16A34A',
                    backgroundColor: (context) => {
                        const { chart } = context;
                        const { ctx: c, chartArea } = chart;
                        if (!chartArea) {
                            return 'rgba(22, 163, 74, 0.18)';
                        }
                        const g = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        g.addColorStop(0, 'rgba(22, 163, 74, 0.18)');
                        g.addColorStop(1, 'rgba(22, 163, 74, 0.0)');
                        return g;
                    },
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#FFFFFF',
                    pointHoverBackgroundColor: '#FFFFFF',
                    pointBorderColor: '#16A34A',
                    pointHoverBorderColor: '#16A34A',
                    pointBorderWidth: 2,
                },
                {
                    label: 'Keluar',
                    data: outData,
                    borderColor: '#2563EB',
                    backgroundColor: (context) => {
                        const { chart } = context;
                        const { ctx: c, chartArea } = chart;
                        if (!chartArea) {
                            return createGradient(37, 99, 235);
                        }
                        const g = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        g.addColorStop(0, 'rgba(37, 99, 235, 0.18)');
                        g.addColorStop(1, 'rgba(37, 99, 235, 0.0)');
                        return g;
                    },
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#FFFFFF',
                    pointHoverBackgroundColor: '#FFFFFF',
                    pointBorderColor: '#2563EB',
                    pointHoverBorderColor: '#2563EB',
                    pointBorderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: reducedMotion ? 0 : 600,
                easing: 'easeOutQuart',
            },
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: '#0F172A',
                    titleColor: '#FFFFFF',
                    bodyColor: '#FFFFFF',
                    cornerRadius: 8,
                    padding: 10,
                    displayColors: true,
                    callbacks: {
                        label(context) {
                            const value = Number(context.parsed.y || 0).toLocaleString('id-ID');
                            return ` ${context.dataset.label}: ${value} unit`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                    },
                    border: {
                        display: false,
                    },
                    ticks: {
                        color: '#64748B',
                        font: {
                            size: 12,
                        },
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 12,
                    },
                },
                y: {
                    beginAtZero: true,
                    border: {
                        display: false,
                        dash: [4, 4],
                    },
                    grid: {
                        color: '#E2E8F0',
                    },
                    ticks: {
                        color: '#64748B',
                        font: {
                            size: 12,
                        },
                        precision: 0,
                        callback(value) {
                            return Number(value).toLocaleString('id-ID');
                        },
                    },
                },
            },
        },
    });
}

document.addEventListener('DOMContentLoaded', initMovementAreaChart);

