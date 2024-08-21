<template>
  <main>
    <h1>hello</h1>
  </main>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import axios from '../axiosConfig'

interface Task {
  id: number
  name: string
  status: boolean
}

const tasks = ref<Task[]>([])

const fetchTasks = async () => {
  try {
    const response = await axios.get<Task[]>('/api/tasks')
    tasks.value = response.data
  } catch (error) {
    console.error('error fetching list', error)
  }
}

// Call fetchTasks when the component is mounted
fetchTasks()
</script>