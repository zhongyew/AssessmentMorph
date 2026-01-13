<script setup>
import HelloWorld from './components/HelloWorld.vue'
</script>

<template>
  <main>
    <HelloWorld />
  </main>
</template>

<style>
/* 这里的样式是全局生效的 */
body {
  margin: 0;
  padding: 0;
  background-color: #0a0a0a; /* 给整个网页一个深色背景 */
  display: flex;
  place-items: center;
  min-width: 320px;
  min-height: 100vh;
}

#app {
  width: 100%;
  margin: 0 auto;
  text-align: center;
}
</style>