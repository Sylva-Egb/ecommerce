<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  orders: Array,
  recommendedProducts: Array,
});

const statusClasses = {
  pending: 'text-yellow-600',
  shipped: 'text-green-600',
  delivered: 'text-blue-600',
  cancelled: 'text-red-600',
};

</script>

<template>
  <Head title="Mon Tableau de bord" />

  <AuthenticatedLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold mb-8">Bonjour ! Voici votre tableau de bord.</h1>

        <!-- Mes commandes -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
          <div class="p-6 bg-white border-b border-gray-200">
            <h2 class="text-lg font-medium mb-4">Mes commandes récentes</h2>
            <div v-if="orders.length">
              <div v-for="order in orders" :key="order.id" class="border-b py-4">
                <div class="flex justify-between">
                  <div>
                    <p class="font-medium">Commande #{{ order.order_number }}</p>
                    <p class="text-sm text-gray-500">{{ order.created_at }}</p>
                  </div>
                  <div>
                    <span :class="statusClasses[order.status]">
                      {{ order.status }}
                    </span>
                    <p class="text-right">{{ order.total_price }} FCFA</p>
                  </div>
                </div>
                <Link :href="route('orders.show', order.id)" class="text-blue-600 hover:underline">
                  Voir détails
                </Link>
              </div>
            </div>
            <p v-else class="text-gray-500">Vous n'avez pas encore passé de commande.</p>
          </div>
        </div>

        <!-- Recommandations -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <h2 class="text-lg font-medium mb-4">Produits recommandés</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div v-for="product in recommendedProducts" :key="product.id" class="border rounded-lg p-4">
                <img :src="product.image_url" :alt="product.name" class="w-full h-32 object-cover mb-2">
                <h3 class="font-medium">{{ product.name }}</h3>
                <p class="text-pink-600 font-bold">{{ product.price }} FCFA</p>
                <Link :href="route('products.show', product.id)" class="text-sm text-blue-600 hover:underline">
                  Voir le produit
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>