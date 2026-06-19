<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    type: { type: String, default: 'bar' },
    data: { type: Object, required: true },
    options: { type: Object, default: () => ({}) },
    height: { type: Number, default: 250 },
});

const canvasRef = ref(null);
let chartInstance = null;

const createChart = async () => {
    if (!canvasRef.value) return;
    if (chartInstance) chartInstance.destroy();

    const { Chart, registerables } = await import('chart.js');
    Chart.register(...registerables);

    const palette = getComputedStyle(document.documentElement);
    const primary = palette.getPropertyValue('--clinic-primary').trim() || '#24543f';
    const primaryDark = palette.getPropertyValue('--clinic-primary-dark').trim() || '#173729';
    const accent = palette.getPropertyValue('--clinic-accent').trim() || '#87a97f';
    const muted = palette.getPropertyValue('--clinic-muted').trim() || '#5d7365';
    const border = palette.getPropertyValue('--clinic-border').trim() || '#dde3d6';

    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: primaryDark,
                titleColor: '#fff',
                bodyColor: '#fff',
                cornerRadius: 10,
                padding: 12,
                titleFont: { weight: '600', size: 12 },
                bodyFont: { size: 12 },
            },
        },
        scales: props.type !== 'doughnut' && props.type !== 'pie' ? {
            x: {
                grid: { color: `${border}60`, drawBorder: false },
                ticks: { color: muted, font: { size: 11 } },
            },
            y: {
                grid: { color: `${border}60`, drawBorder: false },
                ticks: { color: muted, font: { size: 11 } },
                beginAtZero: true,
            },
        } : undefined,
    };

    const mergedOptions = JSON.parse(JSON.stringify({ ...defaultOptions, ...props.options }));

    chartInstance = new Chart(canvasRef.value, {
        type: props.type,
        data: JSON.parse(JSON.stringify(props.data)),
        options: mergedOptions,
    });
};

onMounted(createChart);

watch(() => props.data, createChart, { deep: true });

onUnmounted(() => {
    if (chartInstance) chartInstance.destroy();
});
</script>

<template>
    <div :style="{ height: height + 'px' }">
        <canvas ref="canvasRef" />
    </div>
</template>
