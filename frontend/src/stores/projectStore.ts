import { defineStore } from 'pinia'
import axios, { AxiosError } from 'axios'
import { handleError } from 'vue'

interface Project {
  id: number
  name: string
  description: string
  status: string
  owner?: string
  deadline?: string
  created_at?: string
  updated_at?: string
}

interface ProjectState {
  projects: Project[]
  currentProject: Project[] | null
  loading: boolean
  error: string | null
}

export const useProjectStore = defineStore('projects', {
  state: (): ProjectState => ({
    projects: [],
    currentProject: null,
    loading: false,
    error: null
  }),

  getters: {
    getProjectById: (state) => (id: number) => {
      return state.projects.find((project) => project.id === id)
    },
    activeProjects: (state) => {
      return state.projects.filter((project) => project.status !== 'completed')
    }
  },

  actions: {
    handleError(error: unknown): string {
      if (axios.isAxiosError(error)) {
        return error.response?.data?.error || error.message
      }
      return 'An expected error occurred'
    },

    async fetchProjects() {
      this.loading = true
      this.error = null
      try {
        const { data } = await axios.get<Project[]>('/api/projects/')
        this.projects = data
      } catch (error) {
        this.error = this.handleError(error)
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})
