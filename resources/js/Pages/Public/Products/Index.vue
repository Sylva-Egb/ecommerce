<script setup>
import { Head, Link } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Star } from 'lucide-vue-next'
import Pagination from '@/Components/Pagination.vue'
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  auth: Object,
  canLogin: Boolean,
  canRegister: Boolean,
  products: Object,
})
const page = usePage();

// Computed properties
const isAdmin = computed(() => page.props.auth.user?.role === 'admin');
const layout = computed(() => props.auth.user ? AuthenticatedLayout : GuestLayout)
</script>

<template>
  <component :is="layout">
    <Head title="Tous nos produits" />

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
      <!-- En-tête -->
      <div class="text-center mb-12">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">Notre collection</h1>
        <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
          Découvrez tous nos produits de beauté soigneusement sélectionnés
        </p>
      </div>

      <!-- Liste des produits -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
        <div
          v-for="product in products.data"
          :key="product.id"
          class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 dark:border-gray-700"
        >
          <div class="relative overflow-hidden h-60">
            <img
              :src="product.image_url || 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'"
              :alt="product.name"
              class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
            />
            <div v-if="product.is_vedette" class="absolute top-3 right-3 bg-amber-400 text-white px-2 py-1 rounded-full text-xs font-bold flex items-center">
              <Star class="w-3 h-3 mr-1 fill-white" />
              Vedette
            </div>
          </div>
          <div class="p-5">
            <div class="flex justify-between items-start">
              <h3 class="font-semibold text-lg mb-1">{{ product.name }}</h3>
              <span v-if="product.category" class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-1 rounded">
                {{ product.category.name }}
              </span>
            </div>
            <p class="text-gray-600 dark:text-gray-400 text-sm line-clamp-2 mb-3">
              {{ product.description }}
            </p>
            <div class="flex justify-between items-center">
              <span class="text-pink-600 font-bold text-lg">{{ product.price }} FCFA</span>
              <Link
                :href="route('products.show', product.id)"
                class="text-sm text-pink-600 hover:text-pink-700 font-medium transition"
              >
                Voir plus →
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-12">
        <Pagination :links="products.links" />
      </div>
    </div>
  </component>
</template>