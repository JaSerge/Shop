<template>
  <Dialog
    :visible="visible"
    :header="isEdit ? 'Редактирование товара' : 'Добавление товара'"
    modal
    :style="{ width: '32rem' }"
    @update:visible="onVisibleChange"
  >
    <div class="product-form">
      <div class="product-form__field">
        <label for="product-name">Название</label>
        <InputText
          id="product-name"
          v-model="form.name"
          class="product-form__input"
          :invalid="Boolean(errors.name)"
        />
        <small v-if="errors.name" class="product-form__error">{{ errors.name }}</small>
      </div>

      <div class="product-form__field">
        <label for="product-type">Тип</label>
        <Select
          id="product-type"
          v-model="form.typeId"
          :options="typeOptions"
          option-label="label"
          option-value="value"
          placeholder="Выберите тип"
          class="product-form__input"
          :invalid="Boolean(errors.typeId)"
        />
        <small v-if="errors.typeId" class="product-form__error">{{ errors.typeId }}</small>
      </div>

      <div class="product-form__field">
        <label for="product-description">Описание</label>
        <Textarea
          id="product-description"
          v-model="form.description"
          rows="3"
          auto-resize
          class="product-form__input"
        />
      </div>

      <div class="product-form__row">
        <div class="product-form__field">
          <label for="product-quantity">Количество</label>
          <InputNumber
            id="product-quantity"
            v-model="form.quantity"
            :min="0"
            class="product-form__input"
            :invalid="Boolean(errors.quantity)"
          />
          <small v-if="errors.quantity" class="product-form__error">{{ errors.quantity }}</small>
        </div>

        <div class="product-form__field">
          <label for="product-price">Цена</label>
          <InputNumber
            id="product-price"
            v-model="form.price"
            :min="0"
            :min-fraction-digits="2"
            :max-fraction-digits="2"
            mode="decimal"
            class="product-form__input"
            :invalid="Boolean(errors.price)"
          />
          <small v-if="errors.price" class="product-form__error">{{ errors.price }}</small>
        </div>
      </div>
    </div>

    <template #footer>
      <Button label="Отмена" severity="secondary" outlined @click="onVisibleChange(false)" />
      <Button label="Сохранить" :loading="saving" @click="submit" />
    </template>
  </Dialog>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Select from 'primevue/select'
import Button from 'primevue/button'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  product: {
    type: Object,
    default: null,
  },
  typeOptions: {
    type: Array,
    default: () => [],
  },
  saving: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:visible', 'submit'])

const isEdit = ref(false)
const errors = reactive({
  name: '',
  typeId: '',
  quantity: '',
  price: '',
})

const form = reactive({
  name: '',
  typeId: null,
  description: '',
  quantity: 0,
  price: 0,
})

function resetErrors() {
  errors.name = ''
  errors.typeId = ''
  errors.quantity = ''
  errors.price = ''
}

function fillForm(product) {
  form.name = product?.name ?? ''
  form.typeId = product?.typeId ?? null
  form.description = product?.description ?? ''
  form.quantity = product?.quantity ?? 0
  form.price = Number(product?.price ?? 0)
}

watch(
  () => [props.visible, props.product],
  () => {
    if (!props.visible) {
      return
    }

    isEdit.value = Boolean(props.product)
    resetErrors()
    fillForm(props.product)
  },
  { immediate: true },
)

function validate() {
  resetErrors()
  let valid = true

  if (!form.name.trim()) {
    errors.name = 'Укажите название'
    valid = false
  }

  if (!form.typeId) {
    errors.typeId = 'Выберите тип'
    valid = false
  }

  if (form.quantity === null || form.quantity < 0) {
    errors.quantity = 'Количество должно быть неотрицательным'
    valid = false
  }

  if (form.price === null || form.price < 0) {
    errors.price = 'Цена должна быть неотрицательной'
    valid = false
  }

  return valid
}

function submit() {
  if (!validate()) {
    return
  }

  emit('submit', {
    id: props.product?.id ?? null,
    name: form.name.trim(),
    typeId: form.typeId,
    description: form.description.trim() || null,
    quantity: form.quantity,
    price: form.price,
  })
}

function onVisibleChange(value) {
  emit('update:visible', value)
}
</script>

<style scoped>
.product-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.product-form__row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.product-form__field {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.product-form__field label {
  font-size: 0.875rem;
  font-weight: 500;
}

.product-form__input {
  width: 100%;
}

.product-form__error {
  color: #dc2626;
}
</style>
