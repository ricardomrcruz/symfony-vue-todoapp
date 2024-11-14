<template>
    <div class="boot-animation">
      <div class="scanline"></div>
      <canvas ref="canvas"></canvas>
    </div>
  </template>
  
  <script>
  export default {
    name: 'BootAnimation',
    data() {
      return {
        canvas: null,
        ctx: null,
        particles: [],
        targetParticles: [],
        currentWord: 0,
        words: ['WELCOME TO', 'PRO-JTEC SYSTEMS'],
        time: 0,
        animationFrame: null
      }
    },
    mounted() {
      this.initCanvas()
      this.animate()
  
      // After animation completes (adjust timing as needed)
      setTimeout(() => {
        this.$emit('animation-complete')
      }, 3000) // 6 seconds for example
    },
    beforeDestroy() {
      if (this.animationFrame) {
        cancelAnimationFrame(this.animationFrame)
      }
    },
    methods: {
      initCanvas() {
        this.canvas = this.$refs.canvas
        this.ctx = this.canvas.getContext('2d')
        this.canvas.width = window.innerWidth
        this.canvas.height = window.innerHeight
  
        // Initialize particles
        for(let i = 0; i < 500; i++) {
          this.particles.push({
            x: Math.random() * this.canvas.width,
            y: Math.random() * this.canvas.height,
            z: Math.random() * 1000
          })
        }
      },
      createTextParticles(text) {
        this.ctx.font = '120px monospace'
        this.ctx.fillStyle = '#2ec927'
        this.ctx.textAlign = 'center'
        this.ctx.textBaseline = 'middle'
        
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height)
        this.ctx.fillText(text, this.canvas.width/2, this.canvas.height/2)
  
        const imageData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height)
        const pixels = imageData.data
        const positions = []
  
        for(let i = 0; i < pixels.length; i += 4) {
          if(pixels[i] > 0) {
            const x = (i/4) % this.canvas.width
            const y = Math.floor((i/4) / this.canvas.width)
            if(Math.random() < 0.1) {
              positions.push({x, y, z: 0})
            }
          }
        }
  
        return positions
      },
      animate() {
        this.ctx.fillStyle = 'rgba(0, 0, 0, 0.1)'
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height)
  
        if(this.time % 110 === 0) {
          this.targetParticles = this.createTextParticles(this.words[this.currentWord])
          this.currentWord = (this.currentWord + 1) % this.words.length
        }
  
        this.ctx.fillStyle = '#2ec927'
        this.particles.forEach((particle, i) => {
          if(this.targetParticles[i]) {
            particle.x += (this.targetParticles[i].x - particle.x) * 0.1
            particle.y += (this.targetParticles[i].y - particle.y) * 0.1
          } else {
            particle.x += (Math.random() - 0.5) * 2
            particle.y += (Math.random() - 0.5) * 2
          }
          
          this.ctx.fillRect(particle.x, particle.y, 2, 2)
        })
  
        this.time++
        this.animationFrame = requestAnimationFrame(this.animate)
      }
    }
  }
  </script>
  
  <style scoped>
  .boot-animation {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: #000;
  }
  
  canvas {
    width: 100vw;
    height: 100vh;
  }
  
  .scanline {
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