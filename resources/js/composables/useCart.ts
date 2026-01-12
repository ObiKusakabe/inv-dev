const cart = ref<CartItem[]>([])

function add(product) {}
function remove(productId) {}
function totalPrice() {}

export function useCart() {
  return { cart, add, remove, totalPrice }
}