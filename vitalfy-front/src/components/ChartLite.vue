<template>
    <div class="w-full h-full">
        <canvas ref="canvasRef"></canvas>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import {
    ArcElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PieController,
    PointElement,
    Tooltip
} from 'chart.js';

ChartJS.register(
    LineController,
    PieController,
    LineElement,
    PointElement,
    ArcElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend
);

const props = defineProps({
    type: {
        type: String,
        required: true
    },
    data: {
        type: Object,
        default: null
    },
    options: {
        type: Object,
        default: null
    },
    plugins: {
        type: Array,
        default: () => []
    }
});

const canvasRef = ref(null);
let chartInstance = null;
let isUnmounted = false;

const cloneValue = (value) => {
    if (!value) return value;
    if (typeof structuredClone === 'function') return structuredClone(value);
    return JSON.parse(JSON.stringify(value));
};

const renderChart = () => {
    if (isUnmounted || !canvasRef.value || !props.data) return;

    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }

    chartInstance = new ChartJS(canvasRef.value, {
        type: props.type,
        data: props.data,
        options: props.options,
        plugins: props.plugins
    });
};

watch(
    () => [props.type, props.data],
    () => {
        renderChart();
    },
    { deep: true }
);

onMounted(() => {
    renderChart();
});

onBeforeUnmount(() => {
    isUnmounted = true;
    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }
});
</script>
