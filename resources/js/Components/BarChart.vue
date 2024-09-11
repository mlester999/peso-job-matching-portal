<script setup>
import { ref } from 'vue'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const chartData = ref({
    labels: ['January', 'February', 'March'],
    datasets: [
        {
            label: 'Data One',
            backgroundColor: '#f87979',
            data: [
                40,
                20,
                12
            ]
        }
    ]
})

const chartOptions = ref({
    responsive: true
})
</script>

<template>
    <Bar id="bar-chart-id" :options="chartOptions" :data="chartData" />
</template>