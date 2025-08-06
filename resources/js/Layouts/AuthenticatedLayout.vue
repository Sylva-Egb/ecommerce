<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ShoppingBag as ShoppingBagIcon, X, CheckCircle } from 'lucide-vue-next';
import { useCartStore } from '@/Stores/cartStore';
import { onMounted } from 'vue';

const showingNavigationDropdown = ref(false);
const isCartOpen = ref(false);
const showOrderModal = ref(false);
const showSuccessModal = ref(false);
const orderData = ref(null);
const cartStore = useCartStore();
const page = usePage();

onMounted(() => {
    const isAuthenticated = !!page.props.auth.user
    cartStore.initialize(isAuthenticated)
})

// Computed properties
const isAdmin = computed(() => page.props.auth.user?.role === 'admin');
const cartItems = computed(() => cartStore.items);
const cartCount = computed(() => cartStore.totalItems);
const cartTotal = computed(() => cartStore.totalPrice);

// Formulaire de commande prérempli avec les infos utilisateur
const form = useForm({
items: cartStore.items.length > 0
    ? cartStore.items.map(item => ({
        product_id: item.id,
        quantity: item.quantity
    }))
    : [], // Retourne un tableau vide si panier vide
    is_self_receiving: true,
    address: {
        full_name: page.props.auth.user?.name || '',
        address_line: '',
        city: '',
        zip_code: '',
        country: "Côte d'Ivoire",
        phone: page.props.auth.user?.phone || ''
    }
});

function submitOrder() {
    // Créez un nouvel objet formData sans l'adresse si is_self_receiving est true
    const formData = {
        items: form.items,
        is_self_receiving: form.is_self_receiving
    };

    // Ajoutez l'adresse seulement si nécessaire
    if (!form.is_self_receiving) {
        formData.address = {
            full_name: form.address.full_name,
            address_line: form.address.address_line,
            city: form.address.city,
            zip_code: form.address.zip_code,
            country: form.address.country,
            phone: form.address.phone
        };
    }

    // Utilisez form.transform pour envoyer les données correctement
    form.transform(() => formData).post(route('orders.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showOrderModal.value = false;
            showSuccessModal.value = true;
            cartStore.clearCart();
        },
        onError: (errors) => {
            console.log('Validation errors:', errors);
        }
    });
}

// Methods
const toggleCart = () => {
  isCartOpen.value = !isCartOpen.value;
};

// Ouvrir le modal de commande
function openOrderModal() {

    form.items = cartItems.value.map(item => ({
        product_id: item.id,
        quantity: item.quantity
    }));
    showOrderModal.value = true;
    isCartOpen.value = false;
}

// Soumettre la commande
/* function submitOrder() {
    form.post(route('orders.store'), {
        preserveScroll: true,
        onSuccess: (response) => {
            showOrderModal.value = false;
            showSuccessModal.value = true;
            orderData.value = response.props.order;
            cartStore.clearCart();
        },
        onError: (errors) => {
            console.log('Validation errors:', errors);
        }
    });
} */


const updateQuantity = async (productId, quantity) => {
  await cartStore.updateQuantity(productId, quantity);
};

const removeFromCart = async (productId) => {
  await cartStore.removeItem(productId);
  if (cartStore.items.length === 0) {
    isCartOpen.value = false;
  }
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="border-b border-gray-100 bg-white">
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="isAdmin ? route('admin.dashboard') : route('dashboard')">
                                    <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink
                                    :href="isAdmin ? route('admin.dashboard') : route('dashboard')"
                                    :active="route().current(isAdmin ? 'admin.dashboard' : 'dashboard')"
                                >
                                    Dashboard
                                </NavLink>

                                <!-- Liens pour admin -->
                                <template v-if="isAdmin">
                                    <NavLink :href="route('admin.products.index')" :active="route().current('admin.products.index')">
                                        Produits
                                    </NavLink>
                                    <NavLink :href="route('admin.categories.index')" :active="route().current('admin.categories.index')">
                                        Catégories
                                    </NavLink>
                                    <NavLink :href="route('orders.index')" :active="route().current('orders.*')">
                                        Commandes
                                    </NavLink>
                                </template>

                                <!-- Liens pour utilisateur normal -->
                                <template v-else>
                                    <NavLink :href="route('products.index')" :active="route().current('products.index')">
                                        Boutique
                                    </NavLink>
                                    <NavLink :href="route('orders.index')" :active="route().current('orders.index')">
                                        Mes Commandes
                                    </NavLink>
                                </template>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Panier (visible pour tous sauf admin) -->
                            <div v-if="!isAdmin" class="relative mr-4">
                                <button @click="toggleCart" class="text-gray-500 hover:text-pink-600 relative" aria-label="Panier">
                                    <ShoppingBagIcon class="w-5 h-5" />
                                    <span v-if="cartCount > 0" class="absolute -top-2 -right-2 bg-pink-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                        {{ cartCount }}
                                    </span>
                                </button>

                                <!-- Dropdown du panier -->
                                <transition enter-active-class="transition ease-out duration-200"
                                    enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0"
                                    leave-active-class="transition ease-in duration-150"
                                    leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
                                    <div v-if="isCartOpen" class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl z-50 border border-gray-100">
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
                                                            <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
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

                                            <button @click="openOrderModal"
                                                class="block w-full text-center bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-lg transition-colors">
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

                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Déconnexion
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="isAdmin ? route('admin.dashboard') : route('dashboard')"
                            :active="route().current(isAdmin ? 'admin.dashboard' : 'dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>

                        <!-- Liens responsives pour admin -->
                        <template v-if="isAdmin">
                            <ResponsiveNavLink :href="route('admin.products.index')" :active="route().current('admin.products.index')">
                                Produits
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.categories.index')" :active="route().current('admin.categories.index')">
                                Catégories
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('orders.index')" :active="route().current('orders.index')">
                                Commandes
                            </ResponsiveNavLink>
                        </template>

                        <!-- Liens responsives pour utilisateur normal -->
                        <template v-else>
                            <ResponsiveNavLink :href="route('products.index')" :active="route().current('products.index')">
                                Boutique
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('orders.index')" :active="route().current('orders.index')">
                                Mes Commandes
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="border-t border-gray-200 pb-1 pt-4">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Déconnexion
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

                        <!-- Modal de commande pour utilisateur authentifié -->
            <transition name="modal">
                <div v-if="showOrderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div @click.self="showOrderModal = false" class="fixed inset-0 bg-black/30 backdrop-blur-sm"></div>

                    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-gray-200">
                        <!-- En-tête -->
                        <div class="sticky top-0 bg-white px-6 py-4 border-b border-gray-200 flex justify-between items-center z-10">
                            <h2 class="text-xl font-bold text-gray-900">Passer commande</h2>
                            <button @click="showOrderModal = false" class="text-gray-400 hover:text-gray-500">
                                <X class="w-6 h-6" />
                            </button>
                        </div>

                        <!-- Contenu -->
                        <div class="p-6">
                            <!-- Récapitulatif des produits -->
                            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                                <h3 class="font-medium text-gray-900 mb-3">Votre commande</h3>
                                <div class="space-y-4">
                                    <div v-for="item in cartItems" :key="item.id" class="flex items-center">
                                        <img :src="item.image_url || 'https://via.placeholder.com/60'" :alt="item.name"
                                             class="w-12 h-12 object-cover rounded-md mr-4">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-medium">{{ item.name }}</h4>
                                            <p class="text-xs text-gray-500">Quantité: {{ item.quantity }}</p>
                                        </div>
                                        <span class="text-pink-600 font-bold">{{ (item.price * item.quantity).toFixed(2) }} FCFA</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-200">
                                    <span class="font-medium">Total</span>
                                    <span class="text-pink-600 font-bold text-lg">{{ cartTotal }} FCFA</span>
                                </div>
                            </div>

                            <!-- Formulaire client -->
                            <div class="space-y-6">
                                <div class="px-6 py-4 border-t border-gray-200">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" v-model="form.is_self_receiving"
                                               class="rounded text-pink-600 focus:ring-pink-500">
                                        <span class="ml-3 text-sm font-medium text-gray-700">
                                            Je suis le destinataire de la commande
                                        </span>
                                    </label>
                                </div>

                                <!-- Adresse de livraison -->
                                <div v-if="!form.is_self_receiving">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Adresse de livraison</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet *</label>
                                            <input v-model="form.address.full_name" type="text" :required="!form.is_self_receiving"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse *</label>
                                            <input v-model="form.address.address_line" type="text" :required="!form.is_self_receiving"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Ville *</label>
                                            <input v-model="form.address.city" type="text" :required="!form.is_self_receiving"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Code postal</label>
                                            <input v-model="form.address.zip_code" type="text"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Pays *</label>
                                            <select v-model="form.address.country" :required="!form.is_self_receiving"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500">
                                                <option value="Côte d'Ivoire">Côte d'Ivoire</option>
                                                <option value="France">France</option>
                                                <option value="Autre">Autre</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone *</label>
                                            <input v-model="form.address.phone" type="tel" :required="!form.is_self_receiving"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pied de page -->
                            <div class="sticky bottom-0 bg-white px-6 py-4 border-t border-gray-200 flex justify-end">
                                <button @click="showOrderModal = false" type="button"
                                        class="mr-3 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                    Annuler
                                </button>
                                <button @click="submitOrder" type="button" :disabled="form.processing"
                                        class="px-6 py-2 bg-gradient-to-r from-pink-600 to-amber-600 text-white rounded-lg hover:from-pink-700 hover:to-amber-700 transition disabled:opacity-70">
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
                    <div @click.self="showSuccessModal = false" class="fixed inset-0 bg-black/30 backdrop-blur-sm"></div>

                    <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full overflow-hidden">
                        <!-- En-tête -->
                        <div class="bg-gradient-to-r from-pink-600 to-amber-600 p-6 text-white text-center">
                            <CheckCircle class="w-12 h-12 mx-auto mb-2" />
                            <h3 class="text-xl font-bold">Commande confirmée !</h3>
                        </div>

                        <!-- Corps -->
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div>
                                    <p class="text-gray-600">
                                        Votre commande a bien été enregistrée.
                                    </p>
                                    <p class="text-pink-600 font-bold mt-2">Numéro: {{ orderData?.order_number }}</p>
                                </div>
                            </div>

                            <div class="space-y-3 mt-6">
                                <a :href="orderData?.invoice_url" target="_blank"
                                   class="w-full px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition flex items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Télécharger la facture
                                </a>
                                <Link :href="route('orders.index')"
                                      class="block w-full px-4 py-2 bg-white border border-pink-600 text-pink-600 rounded-lg hover:bg-pink-50 transition">
                                    Voir mes commandes
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>