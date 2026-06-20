<template>
  <div class="app">
    <Toast />
    <header class="app__header">
      <h1>TopShop@</h1>
      <p class="app__subtitle">Работаем без выходных</p>
    </header>

    <main class="app__main">
      <ProductsTable />
    </main>
  </div>
</template>

<script setup>
import ProductsTable from './components/ProductsTable.vue'
import Toast from 'primevue/toast'
</script>

<style>
*,
*::before,
*::after {
  box-sizing: border-box;
}

body {
  margin: 0;
  font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
  background: #f4f6f8;
  color: #1f2937;
}

.app {
  min-height: 100vh;
  padding: 2rem 1.5rem;
}

.app__header {
  max-width: 1400px;
  margin: 0 auto 1.5rem;
}

.app__header h1 {
  margin: 0 0 0.25rem;
  font-size: 1.75rem;
  font-weight: 700;
}

.app__subtitle {
  margin: 0;
  color: #6b7280;
}

.app__main {
  max-width: 1400px;
  margin: 0 auto;
}
</style>
