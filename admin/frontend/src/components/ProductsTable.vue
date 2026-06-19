<template>
  <div class="products-table">
    <Message v-if="error" severity="error" :closable="false" class="products-table__message">
      {{ error }}
    </Message>

    <ConfirmDialog />

    <ProductFormDialog
      v-model:visible="formVisible"
      :product="editingProduct"
      :type-options="typeOptions"
      :saving="saving"
      @submit="onFormSubmit"
    />

    <DataTable
      :value="products"
      lazy
      :loading="loading"
      striped-rows
      paginator
      :rows="rows"
      :total-records="totalRecords"
      :rows-per-page-options="[5, 10, 20, 50]"
      :sort-field="sortField"
      :sort-order="sortOrder"
      sort-mode="single"
      removable-sort
      data-key="id"
      empty-message="Товары не найдены"
      table-style="min-width: 50rem"
      @page="onPage"
      @sort="onSort"
    >
      <template #header>
        <div class="products-table__header">
          <div class="products-table__toolbar">
            <span class="products-table__title">Товары</span>
            <div class="products-table__actions">
              <Button
                icon="pi pi-plus"
                label="Добавить"
                @click="openCreateForm"
              />
              <Button
                icon="pi pi-refresh"
                label="Обновить"
                severity="secondary"
                outlined
                :loading="loading"
                @click="loadProducts"
              />
            </div>
          </div>

          <div class="products-table__filters">
            <Select
              v-model="selectedTypeId"
              :options="typeOptions"
              option-label="label"
              option-value="value"
              placeholder="Типы"
              show-clear
              class="products-table__filter"
              @change="onFilterChange"
            />
            <Select
              v-model="selectedStock"
              :options="stockOptions"
              option-label="label"
              option-value="value"
              class="products-table__filter"
              @change="onFilterChange"
            />
          </div>
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
      <Column field="quantity" header="Количество" sortable>
        <template #body="{ data }">
          <Tag
            :value="data.quantity > 0 ? 'В наличии' : 'Нет в наличии'"
            :severity="data.quantity > 0 ? 'success' : 'danger'"
          />
          <span class="products-table__quantity">{{ data.quantity }} шт.</span>
        </template>
      </Column>
      <Column field="price" header="Цена" sortable>
        <template #body="{ data }">
          {{ formatPrice(data.price) }}
        </template>
      </Column>
      <Column header="Действия" style="width: 8rem">
        <template #body="{ data }">
          <div class="products-table__row-actions">
            <Button
              icon="pi pi-pencil"
              severity="secondary"
              text
              rounded
              aria-label="Редактировать"
              @click="openEditForm(data)"
            />
            <Button
              icon="pi pi-trash"
              severity="danger"
              text
              rounded
              aria-label="Удалить"
              @click="confirmDelete(data)"
            />
          </div>
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
import Select from 'primevue/select'
import Tag from 'primevue/tag'
import ConfirmDialog from 'primevue/confirmdialog'
import { useConfirm } from 'primevue/useconfirm'
import ProductFormDialog from './ProductFormDialog.vue'
import {
  createProduct,
  deleteProduct,
  fetchProductTypes,
  fetchProducts,
  updateProduct,
} from '../api/products'

const confirm = useConfirm()

const products = ref([])
const typeOptions = ref([])
const totalRecords = ref(0)
const rows = ref(10)
const sortField = ref('id')
const sortOrder = ref(1)
const loading = ref(false)
const saving = ref(false)
const error = ref('')
const formVisible = ref(false)
const editingProduct = ref(null)
const selectedTypeId = ref(null)
const selectedStock = ref('all')

const stockOptions = [
  { label: 'Наличие', value: 'all' },
  { label: 'В наличии', value: 'in_stock' },
  { label: 'Нет в наличии', value: 'out_of_stock' },
]

const queryParams = ref({
  page: 1,
  limit: 10,
  sort: 'id',
  order: 'asc',
  typeId: null,
  stock: 'all',
})

const maxLength = 100
const truncatedMessage = (name) => {
  if (!name) return 'этот товар'
  const str = String(name)
  return str.length > maxLength ? str.slice(0, maxLength) + '…' : str
}

function formatPrice(value) {
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'RUB',
    minimumFractionDigits: 2,
  }).format(Number(value))
}

async function loadProductTypes() {
  const types = await fetchProductTypes()
  typeOptions.value = types.map((type) => ({
    label: type.name,
    value: type.id,
  }))
}

async function loadProducts() {
  loading.value = true
  error.value = ''

  try {
    const result = await fetchProducts(queryParams.value)
    products.value = result.data
    totalRecords.value = result.meta.total
  } catch (e) {
    error.value = e.message || 'Не удалось загрузить товары'
    products.value = []
    totalRecords.value = 0
  } finally {
    loading.value = false
  }
}

function openCreateForm() {
  editingProduct.value = null
  formVisible.value = true
}

function openEditForm(product) {
  editingProduct.value = product
  formVisible.value = true
}

async function onFormSubmit(payload) {
  saving.value = true
  error.value = ''

  try {
    if (payload.id) {
      await updateProduct(payload.id, payload)
    } else {
      await createProduct(payload)
    }

    formVisible.value = false
    await loadProducts()
  } catch (e) {
    error.value = e.message || 'Не удалось сохранить товар'
  } finally {
    saving.value = false
  }
}

function confirmDelete(product) {
  confirm.require({
    styleClass: 'custom-confirm-dialog',
    header: 'Удаление товара',
    message: `Удалить товар «${truncatedMessage(product.name)}»?`,    
    icon: 'pi pi-exclamation-triangle',
    rejectLabel: 'Отмена',
    acceptLabel: 'Удалить',
    rejectProps: {
      label: 'Отмена',
      severity: 'secondary',
      outlined: true,
    },
    acceptProps: {
      label: 'Удалить',
      severity: 'danger',
    },
    accept: async () => {
      error.value = ''

      try {
        await deleteProduct(product.id)
        await loadProducts()
      } catch (e) {
        error.value = e.message || 'Не удалось удалить товар'
      }
    },
  })
}

function onFilterChange() {
  queryParams.value = {
    ...queryParams.value,
    page: 1,
    typeId: selectedTypeId.value,
    stock: selectedStock.value,
  }
  loadProducts()
}

function onPage(event) {
  queryParams.value = {
    ...queryParams.value,
    page: event.page + 1,
    limit: event.rows,
  }
  rows.value = event.rows
  loadProducts()
}

function onSort(event) {
  sortField.value = event.sortField ?? 'id'
  sortOrder.value = event.sortOrder ?? 1

  queryParams.value = {
    ...queryParams.value,
    page: 1,
    sort: sortField.value,
    order: sortOrder.value === -1 ? 'desc' : 'asc',
  }
  loadProducts()
}

onMounted(async () => {
  try {
    await loadProductTypes()
    await loadProducts()
  } catch (e) {
    error.value = e.message || 'Не удалось загрузить данные'
  }
})
</script>

<style scoped>
.products-table__message {
  margin-bottom: 1rem;
}

.products-table__header {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.products-table__toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.products-table__actions {
  display: flex;
  gap: 0.75rem;
}

.products-table__filters {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.products-table__filter {
  min-width: 12rem;
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

.products-table__quantity {
  margin-left: 0.5rem;
  color: #4b5563;
}

.products-table__row-actions {
  display: flex;
  gap: 0.25rem;
}

:deep(.p-datatable) {
  font-size: 0.875rem; /* 14px вместо 16px */
  --p-datatable-cell-padding: 0.25rem 0.5rem;
}

:deep(.p-datatable tbody td) {
  padding: 0.25rem 0.5rem;
  line-height: 1.2;
}

:deep(.p-datatable thead th) {
  padding: 0.4rem 0.5rem;
  font-size: 0.95rem;
}

:deep(.custom-confirm-dialog) {
  width: 90% !important;
  max-width: 480px !important;
  overflow: hidden;
}

</style>
