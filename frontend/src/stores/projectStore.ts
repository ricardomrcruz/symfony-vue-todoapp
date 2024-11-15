import { defineStore } from 'pinia'
import axios from 'axios'

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
