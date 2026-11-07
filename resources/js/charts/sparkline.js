import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Filler,
} from 'chart.js';

Chart.register(
    LineController,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Filler
);

function initSparklines() {
    const canvases = document.querySelectorAll('canvas[data-sparkline]');
    if (!canvases.length) {
        return;
    }

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    canvases.forEach((canvas) => {
        const points = JSON.parse(canvas.dataset.points || '[0,0,0,0]');
        const ctx = canvas.getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['M1', 'M2', 'M3', 'M4'],
                datasets: [
                    {
                        data: points,
                        borderColor: '#2563EB',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 0,
                        backgroundColor: (context) => {
                            const { chart } = context;
                            const { ctx: c, chartArea } = chart;
                            if (!chartArea) {
                                return 'rgba(37, 99, 235, 0.14)';
                            }
                            const g = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            g.addColorStop(0, 'rgba(37, 99, 235, 0.18)');
                            g.addColorStop(1, 'rgba(37, 99, 235, 0.0)');
                            return g;
                        },
                    },
                ],
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                animation: {
                    duration: reducedMotion ? 0 : 600,
                    easing: 'easeOutQuart',
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false },
                },
                scales: {
                    x: { display: false },
                    y: {
                        display: false,
                        beginAtZero: true,
                    },
                },
            },
        });
    });
}

document.addEventListener('DOMContentLoaded', initSparklines);

