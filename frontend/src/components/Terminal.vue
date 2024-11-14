<template>
    <div 
      class="terminal"
      @click="focusInput"
    >
      <!-- Header -->
      <div class="terminal-header">
        <div class="power-indicator"></div>
        <span class="terminal-title">PRO-JTEC TERMINAL v1.1</span>
      </div>
  
      <!-- Main terminal window -->
      <div 
        ref="terminalOutput"
        class="terminal-output"
      >
        <div v-for="(entry, index) in history" 
             :key="index"
             class="terminal-entry"
             :class="entry.type"
        >
          {{ entry.content }}
        </div>
      </div>
  
      <!-- Command input -->
      <form @submit.prevent="handleCommand" class="terminal-input">
        <span class="prompt">&gt;</span>
        <input
          ref="commandInput"
          v-model="currentCommand"
          type="text"
          spellcheck="false"
        >
      </form>
    </div>
  </template>
  
  <script>
  export default {
    name: 'Terminal',
    data() {
      return {
        history: [
          { type: 'output', content: '=== PRO-JTEC UNIFIED OPERATING SYSTEM ===' },
          { type: 'output', content: '>>> BOOTING UP...' },
          { type: 'output', content: '>>> INITIALIZING SYSTEMS...' },
          { type: 'output', content: '>>> READY.' },
          { type: 'output', content: '\nType "help" for available commands' }
        ],
        currentCommand: '',
        commands: {
          help: () => [
            'Available commands:',
            '----------------',
            'projects - List all projects',
            'project create - Create new project',
            'project view [id] - View project details',
            'tasks - List all tasks',
            'task create - Create new task',
            'task view [id] - View task details',
            'clear - Clear terminal',
            'help - Show this help message'
          ].join('\n'),
          clear: () => {
            this.history = []
            return ''
          }
        }
      }
    },
    mounted() {
      this.focusInput()
    },
    methods: {
      focusInput() {
        this.$refs.commandInput.focus()
      },
      handleCommand(e) {
        if (!this.currentCommand.trim()) return
  
        // Add command to history
        this.history.push({
          type: 'input',
          content: `> ${this.currentCommand}`
        })
  
        // Process command
        const command = this.currentCommand.trim().toLowerCase()
        const output = this.commands[command] 
          ? this.commands[command]() 
          : `Command not found: ${this.currentCommand}\nType "help" for available commands`
  
        // Add response to history
        this.history.push({
          type: 'output',
          content: output
        })
  
        // Clear input and scroll to bottom
        this.currentCommand = ''
        this.$nextTick(() => {
          this.$refs.terminalOutput.scrollTop = this.$refs.terminalOutput.scrollHeight
        })
      }
    }
  }
  </script>
  
  <style scoped>
   * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  margin: 0;
  padding: 0;
  background: #000;
  min-height: 100vh;
  width: 100vw;
}

.terminal {
  height: 100vh;
  width: 100vw;
  background: #000;
  color: #2ec927;
  font-family: monospace;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  font-size: 1.2rem; /* Increased base font size */
}

.terminal-header {
  display: flex;
  align-items: center;
  margin-bottom: 2rem; /* Increased margin */
}

.power-indicator {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #2ec927;
  margin-right: 0.5rem;
  animation: pulse 2s infinite;
}

.terminal-title {
  font-size: 1.4rem; /* Increased title size */
}

.terminal-output {
  flex: 1;
  overflow-y: auto;
  margin-bottom: 2rem; /* Increased margin */
  padding: 1rem; /* Added padding for text */
}

.terminal-entry {
  white-space: pre-wrap;
  line-height: 1.5; /* Increased line height */
  margin-bottom: 0.5rem; /* Added space between entries */
}

.terminal-input {
  display: flex;
  align-items: center;
  padding: 1rem; /* Added padding around input */
}

.prompt {
  margin-right: 1rem; /* Increased spacing */
  font-size: 1.2rem; /* Match base font size */
}

input {
  flex: 1;
  background: transparent;
  border: none;
  color: #2ec927;
  font-family: monospace;
  outline: none;
  font-size: 1.2rem; /* Match base font size */
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

/* Scanline effect */
.terminal::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    to bottom,
    rgba(46, 201, 39, 0) 50%,
    rgba(46, 201, 39, 0.02) 50%
  );
  background-size: 100% 4px;
  pointer-events: none;
}
  </style>
  