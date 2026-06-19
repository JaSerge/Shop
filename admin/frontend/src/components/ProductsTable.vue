<template>
  <div class="products-table">
    <Message v-if="error" severity="error" :closable="false" class="products-table__message">
      {{ error }}
    </Message>

    <DataTable
      :value="products"
      :loading="loading"
      striped-rows
      paginator
      :rows="10"
      :rows-per-page-options="[5, 10, 20, 50]"
      sort-mode="multiple"
      removable-sort
      data-key="id"
      empty-message="Товары не найдены"
      table-style="min-width: 50rem"
    >
      <template #header>
        <div class="products-table__toolbar">
          <span class="products-table__title">Товары</span>
          <Button
            icon="pi pi-refresh"
            label="Обновить"
            severity="secondary"
            outlined
            :loading="loading"
            @click="loadProducts"
          />
        </div>
      </template>

      <Column field="name" header="Название" sortable />
      <Column field="type" header="Тип" sortable />
      <Column field="description" header="Описание">
        <template #body="{ data }">
          <span class="products-table__description">
            {{ data.description || '—' }}
          </span>
        </template>
      </Column>
      <Column field="quantity" header="Количество" sortable />
      <Column field="price" header="Цена" sortable>
        <template #body="{ data }">
          {{ formatPrice(data.price) }}
        </template>
      </Column>
    </DataTable>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Message from 'primevue/message'
import { fetchProducts } from '../api/products'

const products = ref([])
const loading = ref(false)
const error = ref('')

function formatPrice(value) {
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'RUB',
    minimumFractionDigits: 2,
  }).format(Number(value))
}

async function loadProducts() {
  loading.value = true
  error.value = ''

  try {
    products.value = await fetchProducts()
  } catch (e) {
    error.value = e.message || 'Не удалось загрузить товары'
    products.value = []
  } finally {
    loading.value = false
  }
}

onMounted(loadProducts)
</script>

<style scoped>
.products-table__message {
  margin-bottom: 1rem;
}

.products-table__toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.products-table__title {
  font-size: 1.125rem;
  font-weight: 600;
}

.products-table__description {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  max-width: 28rem;
  color: #4b5563;
}
</style>
