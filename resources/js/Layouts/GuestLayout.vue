<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <!-- HEADER -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <!-- Logo + Mobile menu -->
                <div class="flex items-center gap-4">
                    <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="md:hidden text-gray-600">
                        <MenuIcon class="w-6 h-6" />
                    </button>
                    <Link :href="route('home')" class="text-xl font-bold text-pink-600">🌸</Link>
                </div>

                <!-- Desktop Nav -->
                <nav class="hidden md:flex space-x-6 text-sm text-gray-700">
                    <Link :href="route('products.index')" class="hover:text-pink-600">Nos produits</Link>
                    <Link :href="route('products.index')" class="hover:text-pink-600 hidden">Nouveautés</Link>
                    <Link :href="route('products.index')" class="hover:text-pink-600 hidden">Meilleures ventes</Link>
                    <!-- Vous pouvez ajouter des filtres spécifiques si nécessaire -->
                </nav>

                <!-- Right Icons -->
                <div class="flex items-center gap-4">
                    <div class="hidden"> <!-- ajouter md:block plus tard -->
                        <input type="text" placeholder="Rechercher"
                            class="px-3 py-1 rounded-full border border-gray-300 focus:ring-pink-500 focus:outline-none text-sm" />
                    </div>

                    <button class="text-gray-500 hover:text-pink-600">
                        <HeartIcon class="w-5 h-5" />
                    </button>
                    <div class="relative">
                        <button @click="toggleCart" class="text-gray-500 hover:text-pink-600 relative"
                            aria-label="Panier">
                            <ShoppingBagIcon class="w-5 h-5" />
                            <span v-if="cartCount > 0"
                                class="absolute -top-2 -right-2 bg-pink-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                {{ cartCount }}
                            </span>
                        </button>
                        <!-- Dropdown du panier -->
                        <transition enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
                            <div v-if="isCartOpen"
                                class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl z-50 border border-gray-100">
                                <div class="p-4 border-b border-gray-100">
                                    <h3 class="font-bold text-lg text-pink-600 flex items-center gap-2">
                                        <ShoppingBagIcon class="w-5 h-5" />
                                        Votre panier ({{ cartCount }})
                                    </h3>
                                </div>

                                <!-- Liste des produits -->
                                <div class="max-h-96 overflow-y-auto">
                                    <div v-if="cartItems.length === 0" class="p-6 text-center text-gray-500">
                                        Votre panier est vide
                                    </div>

                                    <div v-for="item in cartItems" :key="item.id"
                                        class="p-4 border-b border-gray-100 hover:bg-pink-50 transition-colors">
                                        <div class="flex gap-3">
                                            <img :src="item.image_url || 'https://via.placeholder.com/80'" :alt="item.name"
                                                class="w-16 h-16 object-cover rounded-lg">
                                            <div class="flex-1">
                                                <h4 class="font-medium text-gray-800">{{ item.name }}</h4>
                                                <p class="text-pink-600 font-bold">{{ item.price }} FCFA</p>
                                                <div class="flex items-center justify-between mt-1">
                                                    <div
                                                        class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                                                        <button @click.stop="updateQuantity(item.id, item.quantity - 1)"
                                                            class="px-2 py-1 bg-gray-100 hover:bg-gray-200 transition"
                                                            :disabled="item.quantity <= 1">
                                                            −
                                                        </button>
                                                        <span class="px-2 w-8 text-center">{{ item.quantity }}</span>
                                                        <button @click.stop="updateQuantity(item.id, item.quantity + 1)"
                                                            class="px-2 py-1 bg-gray-100 hover:bg-gray-200 transition">
                                                            +
                                                        </button>
                                                    </div>
                                                    <button @click.stop="removeFromCart(item.id)"
                                                        class="text-gray-400 hover:text-pink-600 transition-colors">
                                                        <X class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total et actions -->
                                <div class="p-4 border-t border-gray-100">
                                    <div class="flex justify-between items-center mb-4">
                                        <span class="font-medium">Total :</span>
                                        <span class="text-pink-600 font-bold text-lg">{{ cartTotal }} FCFA</span>
                                    </div>

                                    <button
                                        class="block w-full text-center bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-lg transition-colors"
                                        @click="openOrderModal">
                                    Passer la commande
                                    </button>

                                    <button @click="isCartOpen = false"
                                        class="w-full mt-2 text-center text-pink-600 hover:text-pink-700 text-sm underline">
                                        Continuer mes achats
                                    </button>
                                </div>
                            </div>
                        </transition>
                    </div>
                    <img src="https://i.pravatar.cc/30" class="w-8 h-8 rounded-full" alt="Profile" />
                </div>
            </div>

            <!-- Mobile Menu -->
            <div v-if="isMobileMenuOpen" class="md:hidden bg-white px-6 pb-4">
                <nav class="space-y-2 text-gray-700 text-sm">
                    <Link :href="route('products.index')" class="block">Nos produits</Link>
                    <Link :href="route('products.index')" class="block">Nouveautés</Link>
                    <Link :href="route('products.index')" class="block">Meilleures ventes</Link>
                    <!-- Vous pouvez ajouter des filtres spécifiques si nécessaire -->
                </nav>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <main class="flex-grow py-10">
            <div class="w-full h-full mx-auto bg-white rounded-xl shadow-md">
                <slot />
            </div>
        </main>
    </div>
    <!-- Modal de commande -->
<transition name="modal">
    <div v-if="showOrderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click.self="showOrderModal = false" class="fixed inset-0 bg-black/30 backdrop-blur-sm transition-opacity"></div>

        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-gray-200 dark:border-gray-700">
            <!-- En-tête -->
            <div class="sticky top-0 bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center z-10">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Passer commande</h2>
                <button @click="showOrderModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                    <X class="w-6 h-6" />
                </button>
            </div>

            <!-- Contenu -->
            <div class="p-6">
                <!-- Récapitulatif des produits -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                    <h3 class="font-medium text-gray-900 dark:text-white mb-3">Votre commande</h3>
                    <div class="space-y-4">
                        <div v-for="item in cartItems" :key="item.id" class="flex items-center">
                            <img :src="item.image_url || 'https://via.placeholder.com/60'" :alt="item.name" class="w-12 h-12 object-cover rounded-md mr-4">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium">{{ item.name }}</h4>
                                <p class="text-xs text-gray-500">Quantité: {{ item.quantity }}</p>
                            </div>
                            <span class="text-pink-600 font-bold">{{ (item.price * item.quantity).toFixed(2) }} FCFA</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-200 dark:border-gray-600">
                        <span class="font-medium">Total</span>
                        <span class="text-pink-600 font-bold text-lg">{{ cartTotal }} FCFA</span>
                    </div>
                </div>

                <!-- Formulaire client -->
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informations client</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom complet *</label>
                                <input v-model="form.guest.name" type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                <input v-model="form.guest.email" type="email" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Téléphone *</label>
                                <input v-model="form.guest.phone" type="tel" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.is_self_receiving" class="rounded text-pink-600 focus:ring-pink-500">
                            <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Je suis le destinataire de la commande
                            </span>
                        </label>
                    </div>

                    <!-- Adresse de livraison -->
                    <div v-if="!form.is_self_receiving">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Adresse de livraison</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom complet *</label>
                                <input v-model="form.address.full_name" type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Adresse *</label>
                                <input v-model="form.address.address_line" type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ville *</label>
                                <input v-model="form.address.city" type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code postal</label>
                                <input v-model="form.address.zip_code" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pays *</label>
                                <select v-model="form.address.country" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white">
                                    <option value="Côte d'Ivoire">Côte d'Ivoire</option>
                                    <option value="France">France</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Téléphone *</label>
                                <input v-model="form.address.phone" type="tel" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Création de compte -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" v-model="form.create_account" class="rounded text-pink-600 focus:ring-pink-500">
                        <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Créer un compte pour suivre ma commande et bénéficier d'avantages
                        </span>
                    </label>

                    <div v-if="form.create_account" class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Mot de passe *
                            </label>
                            <input v-model="form.password" type="password" :required="form.create_account" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Minimum 8 caractères
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pied de page -->
                <div class="sticky bottom-0 bg-white dark:bg-gray-800 px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                    <button @click="showOrderModal = false" type="button" class="mr-3 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Annuler
                    </button>
                    <button @click="submitOrder" type="button" :disabled="form.processing" class="px-6 py-2 bg-gradient-to-r from-pink-600 to-amber-600 text-white rounded-lg hover:from-pink-700 hover:to-amber-700 transition disabled:opacity-70">
                        Confirmer la commande
                    </button>
                </div>
            </div>
        </div>
    </div>
</transition>

<!-- Modal de succès -->
<transition name="modal">
    <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click.self="showSuccessModal = false" class="fixed inset-0 bg-black/30 backdrop-blur-sm transition-opacity"></div>

        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full overflow-hidden">
            <!-- En-tête -->
            <div class="bg-gradient-to-r from-pink-600 to-amber-600 p-6 text-white text-center">
                <CheckCircle class="w-12 h-12 mx-auto mb-2" />
                <h3 class="text-xl font-bold">Commande confirmée !</h3>
            </div>

            <!-- Corps -->
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div>
                        <p class="text-gray-600 dark:text-gray-300">
                            Votre commande a bien été enregistrée.
                        </p>
                        <p class="text-pink-600 font-bold mt-2">Numéro: {{ orderData?.order_number }}</p>
                    </div>
                </div>

                <div class="space-y-3 mt-6">
                    <a :href="orderData?.invoice_url" target="_blank" class="w-full px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Télécharger la facture
                    </a>
                    <button @click="showSuccessModal = false" class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</transition>
</template>

<style>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .modal-container,
.modal-leave-active .modal-container {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.modal-enter-from .modal-container,
.modal-leave-to .modal-container {
  transform: translateY(-20px);
  opacity: 0;
}
</style>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import {
    Menu as MenuIcon,
    Heart as HeartIcon,
    ShoppingBag as ShoppingBagIcon,
    X,
    CheckCircle
} from 'lucide-vue-next'

import { useCartStore } from '@/Stores/cartStore'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const cartStore = useCartStore()
const isCartOpen = ref(false)
const isMobileMenuOpen = ref(false)
const showOrderModal = ref(false)
const showSuccessModal = ref(false)
const orderData = ref(null)

onMounted(() => {
    const isAuthenticated = !!page.props.auth.user
    cartStore.initialize(isAuthenticated)
})

// Formulaire de commande
const form = useForm({
    items: [],
    create_account: false,
    password: '',
    guest: {
        name: '',
        email: '',
        phone: ''
    },
    address: {
        full_name: '',
        address_line: '',
        city: '',
        zip_code: '',
        country: 'Côte d\'Ivoire',
        phone: ''
    },
    is_self_receiving: true
})

// Computed properties
const cartItems = computed(() => cartStore.items)
const cartCount = computed(() => cartStore.totalItems)
const cartTotal = computed(() => cartStore.totalPrice)

// Ouvrir le modal de commande
function openOrderModal() {
    form.items = cartItems.value.map(item => ({
        product_id: item.id,
        quantity: item.quantity
    }))
    showOrderModal.value = true
    isCartOpen.value = false
}

// Soumettre la commande
function submitOrder() {
    // Validation côté client pour les invités
    if (!page.props.auth.user) {
        if (!form.guest.name || !form.guest.phone) {
            alert('Veuillez remplir votre nom et téléphone');
            return;
        }

        if (form.create_account && (!form.password || form.password.length < 8)) {
            alert('Le mot de passe doit contenir au moins 8 caractères');
            return;
        }
    }

    // Préparer les données à envoyer
    const formData = {
        items: cartStore.items.map(item => ({
            product_id: item.id,
            quantity: item.quantity
        })),
        is_self_receiving: form.is_self_receiving,
        create_account: form.create_account,
        // Ne pas inclure password si create_account est false
        ...(form.create_account && { password: form.password }),
        // Envoyer un tableau vide pour address si is_self_receiving est true
        address: form.is_self_receiving ? [] : {
            full_name: form.address.full_name,
            address_line: form.address.address_line,
            city: form.address.city,
            zip_code: form.address.zip_code,
            country: form.address.country,
            phone: form.address.phone
        }
    };

    // Ajouter les données guest si utilisateur non connecté
    if (!page.props.auth.user) {
        formData.guest = {
            name: form.guest.name,
            email: form.guest.email,
            phone: form.guest.phone
        };
    }

    // Soumettre le formulaire
    form.transform(() => formData).post(route('orders.store'), {
        preserveScroll: true,
        onSuccess: (response) => {
            showOrderModal.value = false;
            orderData.value = response.props.order;
            showSuccessModal.value = true;
            cartStore.clearCart();
        },
        onError: (errors) => {
            console.error('Erreurs de validation:', errors);
            // Afficher les erreurs à l'utilisateur
            if (errors.password) {
                alert(errors.password);
            }
            if (errors.address) {
                alert(errors.address);
            }
        }
    });
}


// Methods
const toggleCart = () => {
  isCartOpen.value = !isCartOpen.value
}

const updateQuantity = async (productId, quantity) => {
  await cartStore.updateQuantity(productId, quantity)
}

const removeFromCart = async (productId) => {
  await cartStore.removeItem(productId)
  if (cartStore.items.length === 0) {
    isCartOpen.value = false
  }
}

// Charger le panier au montage
onMounted(() => {
  cartStore.fetchCart()
})

// Fermer le panier quand on clique ailleurs
const handleClickOutside = (event) => {
  const cartElement = event.target.closest('.relative')
  if (!cartElement && isCartOpen.value) {
    isCartOpen.value = false
  }
}

// Gestion des événements
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

</script>
