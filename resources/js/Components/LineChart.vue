<script setup>
import { ref } from 'vue'
import { Line } from 'vue-chartjs'
import {
    Chart as ChartJS, CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js'

defineProps({
    chartOptions: Object,
    chartData: Array
})

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
)


</script>

<template>
    <Line id="line-chart-id" :options="chartOptions" :data="chartData" />
</template>