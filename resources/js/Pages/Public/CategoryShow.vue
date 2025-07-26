<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { Star, ChevronRight } from 'lucide-vue-next'
import GuestLayout from '@/Layouts/GuestLayout.vue'


defineProps({
    category: Object
})
</script>

<template>
    <GuestLayout>

        <Head :title="category.name" />

        <section class="py-12 px-4 sm:px-6 max-w-7xl mx-auto">
            <!-- Hero Section de la catégorie -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-pink-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900 p-8 md:p-12 mb-12">
                <div class="relative z-10 max-w-2xl">
                    <h1 class="text-4xl md:text-5xl font-bold text-pink-600 dark:text-pink-400 mb-4">{{ category.name }}
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-6">{{ category.description }}</p>
                    <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                        <span>{{ category.products.length }} produits disponibles</span>
                    </div>
                </div>

                <img v-if="category.image_url" :src="category.image_url" alt="Image catégorie"
                    class="absolute right-0 top-0 h-full w-1/2 object-cover opacity-20 md:opacity-100" />

                <!-- Effets décoratifs -->
                <div
                    class="absolute -bottom-20 -right-20 w-64 h-64 bg-pink-200 rounded-full opacity-20 dark:opacity-10 blur-3xl">
                </div>
            </div>

            <!-- Produits -->
            <div v-if="category.products.length">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8">
                    <div v-for="product in category.products" :key="product.id"
                        class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-100 dark:border-gray-700">
                        <div class="relative overflow-hidden h-60">
                            <img :src="product.image_url || 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'"
                                :alt="product.name"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                            <div v-if="product.is_vedette"
                                class="absolute top-3 right-3 bg-amber-400 text-white px-2 py-1 rounded-full text-xs font-bold flex items-center">
                                <Star class="w-3 h-3 mr-1 fill-white" />
                                Vedette
                            </div>
                        </div>

                        <div class="p-5">
                            <h3 class="font-semibold text-lg mb-2">{{ product.name }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm line-clamp-2 mb-4">
                                {{ product.description }}
                            </p>
                            <div class="flex justify-between items-center">
                                <span class="text-pink-600 dark:text-pink-400 font-bold">{{ product.price }} FCFA</span>
                                <Link :href="route('products.show', product.id)"
                                    class="inline-flex items-center text-sm text-pink-600 dark:text-pink-400 hover:text-pink-700 dark:hover:text-pink-300 font-medium transition">
                                Voir détails
                                <ChevronRight class="w-4 h-4 ml-1" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aucun produit -->
            <div v-else class="text-center py-16">
                <div class="max-w-md mx-auto">
                    <div
                        class="w-20 h-20 mx-auto bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">Aucun produit dans cette
                        catégorie</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">Nous ajouterons bientôt de nouveaux produits</p>
                    <Link :href="route('categories.show', category.id)"
                        class="inline-flex items-center px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-full transition">
                    Voir les autres catégories
                    </Link>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>

<style scoped>
/* Animation pour le hover des cartes */
.group:hover {
    transform: translateY(-5px);
}
</style>