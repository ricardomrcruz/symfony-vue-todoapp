<template>
  <main>
    <h1>To Do List</h1>
    <ul>
      <li 
        v-for="task in tasks" 
        :key="task.id"
      >
        <h2 style="h2{color:white;}">{{ task.name }} </h2>
        <p>{{ task.description }}</p>
      </li>
    </ul>
  </main>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import axios from '../axiosConfig'

interface Task {
  id: number
  name: string
  description: string
  status: boolean
}

const tasks = ref<Task[]>([])

const fetchTasks = async () => {
  try {
    const response = await axios.get<Task[]>('/api/tasks')
    console.log(response)
    tasks.value = response.data
    
  } catch (error) {
    console.error('error fetching list', error)
  }
}

// Call fetchTasks when the component is mounted
fetchTasks()
</script>