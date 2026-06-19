const API_BASE = import.meta.env.VITE_API_BASE ?? '/backend'

export async function fetchProducts() {
  const response = await fetch(`${API_BASE}/api/products`)

  if (!response.ok) {
    throw new Error(`Ошибка сервера: ${response.status}`)
  }

  return response.json()
}
