import { defineStore } from 'pinia'
import axios from 'axios'
import { router } from '@inertiajs/vue3'

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: [],
        loading: false,
        isAuthenticated: false
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
                this.items = response.data.items || [];

                // Sauvegarder en local pour les invités
                if (!this.isAuthenticated) {
                    localStorage.setItem('cart_items', JSON.stringify(this.items));
                }
            } catch (error) {
                console.error('Error fetching cart:', error);

                // Si l'utilisateur n'est pas connecté (401), charger depuis localStorage
                if (error.response?.status === 401 || !this.isAuthenticated) {
                    const localCart = JSON.parse(localStorage.getItem('cart_items') || '[]');
                    this.items = localCart;
                }
            } finally {
                this.loading = false;
            }
        },

        async addItem(product) {
            try {
                const response = await axios.post('/cart/add', {
                    product_id: product.id,
                    quantity: product.quantity || 1,
                    price: product.price,
                    image: product.image
                });

                // Mettre à jour les items avec la réponse du serveur
                this.items = response.data.items || [];

                // Sauvegarder en local si c'est un invité
                if (!this.isAuthenticated) {
                    localStorage.setItem('cart_items', JSON.stringify(this.items));
                }

            } catch (error) {
                console.error('Error adding to cart:', error);

                // Fallback : ajouter localement si l'API échoue
                const existingItem = this.items.find(item => item.id === product.id);
                if (existingItem) {
                    existingItem.quantity += product.quantity || 1;
                } else {
                    this.items.push({
                        id: product.id,
                        name: product.name,
                        price: product.price,
                        image_url: product.image,
                        quantity: product.quantity || 1
                    });
                }
                localStorage.setItem('cart_items', JSON.stringify(this.items));
            }
        },

        async updateQuantity(productId, quantity) {
            try {
                const response = await axios.put(`/cart/update/${productId}`, { quantity });
                this.items = response.data.items || [];

                if (!this.isAuthenticated) {
                    localStorage.setItem('cart_items', JSON.stringify(this.items));
                }
            } catch (error) {
                console.error('Error updating quantity:', error);

                // Mettre à jour localement en cas d'erreur
                const item = this.items.find(item => item.id === productId);
                if (item) {
                    item.quantity = Math.max(1, quantity);
                    localStorage.setItem('cart_items', JSON.stringify(this.items));
                }
            }
        },

        async removeItem(productId) {
            try {
                const response = await axios.delete(`/cart/remove/${productId}`);
                this.items = response.data.items || [];

                if (!this.isAuthenticated) {
                    localStorage.setItem('cart_items', JSON.stringify(this.items));
                }
            } catch (error) {
                console.error('Error removing from cart:', error);

                // Supprimer localement en cas d'erreur
                this.items = this.items.filter(item => item.id !== productId);
                localStorage.setItem('cart_items', JSON.stringify(this.items));
            }
        },

        async clearCart() {
            try {
                await axios.delete('/cart/clear');
                this.items = [];
                localStorage.removeItem('cart_items');
            } catch (error) {
                console.error('Error clearing cart:', error);
                this.items = [];
                localStorage.removeItem('cart_items');
            }
        },

        async migrateGuestCart() {
            if (!this.isAuthenticated) return;

            try {
                await axios.post('/cart/migrate');
                await this.fetchCart();
                // Nettoyer le localStorage après la migration
                localStorage.removeItem('cart_items');
            } catch (error) {
                console.error('Error migrating cart:', error);
            }
        },

        setAuthenticationStatus(isAuthenticated) {
            this.isAuthenticated = isAuthenticated;
        },

        initialize(isAuthenticated = false) {
            this.isAuthenticated = isAuthenticated;

            if (!isAuthenticated) {
                // Pour les invités : charger depuis localStorage d'abord
                const localCart = JSON.parse(localStorage.getItem('cart_items') || '[]');
                this.items = localCart;
            }

            // Puis synchroniser avec le serveur
            this.fetchCart();

            // Écouter les événements de connexion
            window.addEventListener('user-logged-in', () => {
                this.setAuthenticationStatus(true);
                this.migrateGuestCart();
            });

            window.addEventListener('user-logged-out', () => {
                this.setAuthenticationStatus(false);
                this.items = [];
                localStorage.removeItem('cart_items');
            });
        }
    }
})