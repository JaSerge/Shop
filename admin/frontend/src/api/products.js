const API_BASE = import.meta.env.VITE_API_BASE ?? '/backend'

async function parseResponse(response) {
  const data = await response.json().catch(() => ({}))

  if (!response.ok) {
    throw new Error(data.error || `Ошибка сервера: ${response.status}`)
  }

  return data
}

export async function fetchProducts({
  page = 1,
  limit = 10,
  sort = 'id',
  order = 'asc',
  typeId = null,
  stock = 'all',
} = {}) {
  const params = new URLSearchParams({
    page: String(page),
    limit: String(limit),
    sort,
    order,
  })

  if (typeId) {
    params.set('typeId', String(typeId))
  }

  if (stock && stock !== 'all') {
    params.set('stock', stock)
  }

  const response = await fetch(`${API_BASE}/api/products?${params}`)

  return parseResponse(response)
}

export async function fetchProductTypes() {
  const response = await fetch(`${API_BASE}/api/product-types`)

  return parseResponse(response)
}

export async function createProduct(payload) {
  const response = await fetch(`${API_BASE}/api/products`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  })

  return parseResponse(response)
}

export async function updateProduct(id, payload) {
  const response = await fetch(`${API_BASE}/api/products/${id}`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  })

  return parseResponse(response)
}

export async function deleteProduct(id) {
  const response = await fetch(`${API_BASE}/api/products/${id}`, {
    method: 'DELETE',
  })

  return parseResponse(response)
}
