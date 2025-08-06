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
            this.loading = true;
            try {
                const response = await axios.get('/cart');

                // Si le panier est vide mais qu'on a des items en local
                if (response.data.items.length === 0 && this.items.length > 0) {
                    // Essayer de resynchroniser
                    await this.syncLocalCart();
                    return;
                }

                this.items = response.data.items;
                localStorage.setItem('cart_items', JSON.stringify(this.items));
            } catch (error) {
                console.error('Error fetching cart:', error);
                if (error.response?.status === 401) {
                    const localCart = JSON.parse(localStorage.getItem('cart_items') || '[]');
                    this.items = localCart;
                }
            } finally {
                this.loading = false;
            }
        },

        async syncLocalCart() {
            try {
                // Envoyer tous les items locaux au serveur
                await Promise.all(this.items.map(item =>
                    axios.post('/cart/add', {
                        product_id: item.id,
                        quantity: item.quantity,
                        price: item.price,
                        image: item.image
                    })
                ));
                // Recharger le panier
                await this.fetchCart();
            } catch (error) {
                console.error('Sync failed:', error);
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
                // En cas d'erreur, ajouter localement
                const existingItem = this.items.find(item => item.id === product.id)
                if (existingItem) {
                    existingItem.quantity += product.quantity || 1
                } else {
                    this.items.push({
                        id: product.id,
                        name: product.name,
                        price: product.price,
                        image: product.image,
                        quantity: product.quantity || 1
                    })
                }
                localStorage.setItem('cart_items', JSON.stringify(this.items))
            }
        },

        async updateQuantity(productId, quantity) {
            try {
                await axios.put(`/cart/update/${productId}`, { quantity })
                await this.fetchCart()
            } catch (error) {
                console.error('Error updating quantity:', error)
                // Mettre à jour localement en cas d'erreur
                const item = this.items.find(item => item.id === productId)
                if (item) {
                    item.quantity = quantity
                    localStorage.setItem('cart_items', JSON.stringify(this.items))
                }
            }
        },

        async removeItem(productId) {
            try {
                await axios.delete(`/cart/remove/${productId}`)
                await this.fetchCart()
            } catch (error) {
                console.error('Error removing from cart:', error)
                // Supprimer localement en cas d'erreur
                this.items = this.items.filter(item => item.id !== productId)
                localStorage.setItem('cart_items', JSON.stringify(this.items))
            }
        },

        async clearCart() {
            try {
                await axios.delete('/cart/clear')
                this.items = []
                localStorage.removeItem('cart_items')
            } catch (error) {
                console.error('Error clearing cart:', error)
                this.items = []
                localStorage.removeItem('cart_items')
            }
        },

        async migrateGuestCart() {
            try {
                await axios.post('/cart/migrate')
                await this.fetchCart()
            } catch (error) {
                console.error('Error migrating cart:', error)
            }
        },

        initialize() {
            // Charger depuis localStorage en attendant la réponse du serveur
            const localCart = JSON.parse(localStorage.getItem('cart_items') || '[]')
            this.items = localCart

            // Puis synchroniser avec le serveur
            this.fetchCart()

            // Écouter les événements de connexion/déconnexion
            window.addEventListener('user-logged-in', () => {
                this.migrateGuestCart()
            })
        }
    }
})
