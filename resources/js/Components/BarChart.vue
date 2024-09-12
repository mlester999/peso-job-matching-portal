<script setup>
import { ref } from 'vue'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

defineProps({
    chartOptions: Object,
    chartData: Array
})

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

</script>

<template>
    <Bar id="bar-chart-id" :options="chartOptions" :data="chartData" />
</template>