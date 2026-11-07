import {
    Chart,
    DoughnutController,
    ArcElement,
    Tooltip,
} from 'chart.js';

Chart.register(DoughnutController, ArcElement, Tooltip);

function initCategoryDonutChart() {
    const canvas = document.getElementById('category-donut-chart');
    if (!canvas) {
        return;
    }

    const labels = JSON.parse(canvas.dataset.labels || '[]');
    const values = JSON.parse(canvas.dataset.values || '[]');
    const colors = JSON.parse(canvas.dataset.colors || '[]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!values.length) {
        return;
    }

    new Chart(canvas.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels,
            datasets: [
                {
                    data: values,
                    backgroundColor: colors,
                    borderColor: '#FFFFFF',
                    borderWidth: 4,
                    borderRadius: 8,
                    hoverOffset: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            animation: {
                duration: reducedMotion ? 0 : 600,
                easing: 'easeOutQuart',
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
                    callbacks: {
                        label(context) {
                            const val = Number(context.parsed || 0).toLocaleString('id-ID');
                            return ` ${context.label}: ${val} unit`;
                        },
                    },
                },
            },
        },
    });
}

document.addEventListener('DOMContentLoaded', initCategoryDonutChart);

