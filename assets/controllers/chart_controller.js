import { Controller } from '@hotwired/stimulus';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

export default class extends Controller {
    static values = {
        type: { type: String, default: 'bar' },
        data: Array,
        labelKey: { type: String, default: 'label' },
        dataKey: { type: String, default: 'value' },
        label: { type: String, default: 'Data' },
        color: { type: String, default: '59, 130, 246' },
        horizontal: { type: Boolean, default: false },
    };

    connect() {
        this.createChart();
    }

    disconnect() {
        if (this.chart) {
            this.chart.destroy();
        }
    }

    createChart() {
        const labels = this.dataValue.map(item => item[this.labelKeyValue]);
        const data = this.dataValue.map(item => item[this.dataKeyValue]);

        const config = this.getChartConfig(labels, data);
        this.chart = new Chart(this.element, config);
    }

    getChartConfig(labels, data) {
        const type = this.typeValue;
        const isDoughnut = type === 'doughnut' || type === 'pie';

        if (isDoughnut) {
            return this.getDoughnutConfig(labels, data);
        }

        return this.getBarLineConfig(labels, data, type);
    }

    getDoughnutConfig(labels, data) {
        const colors = this.generateColors(data.length);

        return {
            type: this.typeValue,
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors.map(c => `rgba(${c}, 0.8)`),
                    borderColor: colors.map(c => `rgba(${c}, 1)`),
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#9ca3af',
                            padding: 12,
                            font: { size: 11 },
                        },
                    },
                },
            },
        };
    }

    getBarLineConfig(labels, data, type) {
        const color = this.colorValue;
        const isHorizontal = this.horizontalValue;

        return {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    label: this.labelValue,
                    data: data,
                    backgroundColor: `rgba(${color}, 0.7)`,
                    borderColor: `rgba(${color}, 1)`,
                    borderWidth: type === 'line' ? 2 : 1,
                    fill: type === 'line' ? 'origin' : undefined,
                    tension: type === 'line' ? 0.3 : undefined,
                }],
            },
            options: {
                indexAxis: isHorizontal ? 'y' : 'x',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(75, 85, 99, 0.3)',
                        },
                        ticks: {
                            color: '#9ca3af',
                        },
                    },
                    y: {
                        grid: {
                            color: 'rgba(75, 85, 99, 0.3)',
                        },
                        ticks: {
                            color: '#9ca3af',
                        },
                        beginAtZero: true,
                    },
                },
            },
        };
    }

    generateColors(count) {
        const palette = [
            '239, 68, 68',    // red
            '59, 130, 246',   // blue
            '16, 185, 129',   // green
            '251, 191, 36',   // amber
            '139, 92, 246',   // purple
            '236, 72, 153',   // pink
            '20, 184, 166',   // teal
            '249, 115, 22',   // orange
            '99, 102, 241',   // indigo
            '132, 204, 22',   // lime
        ];

        const colors = [];
        for (let i = 0; i < count; i++) {
            colors.push(palette[i % palette.length]);
        }
        return colors;
    }
}
