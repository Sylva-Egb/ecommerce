import { defineStore } from 'pinia'
import axios from 'axios'
import { router } from '@inertiajs/vue3'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
    loading: false
  }),

  getters: {
    totalItems: (state) => state.items.reduce((total, item) => total + item.quantity, 0),
    totalPrice: (state) => state.items.reduce((total, item) => total + (item.price * item.quantity), 0)
  },

  actions: {
    async fetchCart() {
      this.loading = true
      try {
        const response = await axios.get('/cart')
        this.items = response.data.items
      } catch (error) {
        console.error('Error fetching cart:', error)
        if (error.response?.status === 401) {
          router.visit('/login')
        }
      } finally {
        this.loading = false
      }
    },

    async addItem(product) {
      try {
        await axios.post('/cart/add', {
          product_id: product.id,
          quantity: product.quantity || 1,
          price: product.price,
          image: product.image
        })
        await this.fetchCart()
      } catch (error) {
        console.error('Error adding to cart:', error)
      }
    },

    async updateQuantity(productId, quantity) {
      try {
        await axios.put(`/cart/update/${productId}`, { quantity })
        await this.fetchCart()
      } catch (error) {
        console.error('Error updating quantity:', error)
      }
    },

    async removeItem(productId) {
      try {
        await axios.delete(`/cart/remove/${productId}`)
        await this.fetchCart()
      } catch (error) {
        console.error('Error removing from cart:', error)
      }
    },

    async clearCart() {
      try {
        await axios.delete('/cart/clear')
        this.items = []
      } catch (error) {
        console.error('Error clearing cart:', error)
      }
    }
  }
})