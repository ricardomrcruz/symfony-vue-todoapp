<template>
  <div id="app">
    <BootAnimation
      v-if="showBootAnimation"
      @animation-complete="showBootAnimation = false"
    />
    <Terminal v-else />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import BootAnimation from './components/BootAnimation.vue'
import Terminal from './components/Terminal.vue'

const showBootAnimation = ref(true)
</script>

<style>
body {
  margin: 0;
  overflow: hidden;
}

#app {
  height: 100vh;
}
</style>