<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import { ShoppingBag as ShoppingBagIcon, X } from 'lucide-vue-next';
import { useCartStore } from '@/Stores/cartStore';
import { usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const isCartOpen = ref(false);
const cartStore = useCartStore();
const page = usePage();

// Computed properties
const isAdmin = computed(() => page.props.auth.user?.role === 'admin');
const cartItems = computed(() => cartStore.items);
const cartCount = computed(() => cartStore.totalItems);
const cartTotal = computed(() => cartStore.totalPrice);

// Methods
const toggleCart = () => {
  isCartOpen.value = !isCartOpen.value;
};

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
                                                    <img :src="item.image || 'https://via.placeholder.com/80'" :alt="item.name"
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

                                            <Link :href="route('orders.store')"
                                                class="block w-full text-center bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-lg transition-colors"
                                                @click="isCartOpen = false">
                                                Passer la commande
                                            </Link>

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