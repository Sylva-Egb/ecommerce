<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement, PointElement, LineElement } from 'chart.js';
import { Bar, Pie, Line } from 'vue-chartjs';

// Enregistrement des composants de ChartJS
ChartJS.register(
  Title, Tooltip, Legend,
  BarElement, CategoryScale, LinearScale,
  ArcElement, PointElement, LineElement
);


const props = defineProps({
  stats: Object,
  advancedStats: Object,
  recentOrders: Array,
  lowStockProducts: Array,
  currentYear: Number,
});

const statusClasses = {
  pending: 'bg-yellow-100 text-yellow-800',
  paid: 'bg-blue-100 text-blue-800',
  shipped: 'bg-indigo-100 text-indigo-800',
  delivered: 'bg-green-100 text-green-800',
  cancelled: 'bg-red-100 text-red-800',
};

// Préparation des données pour les graphiques
const monthlyRevenueData = {
  labels: props.advancedStats.monthlyRevenue.map(item => new Date(2000, item.month - 1).toLocaleString('default', { month: 'short' })),
  datasets: [{
    label: 'Chiffre d\'affaires (FCFA)',
    backgroundColor: '#4f46e5',
    data: props.advancedStats.monthlyRevenue.map(item => item.revenue),
  }]
};

const ordersTrendData = {
  labels: props.advancedStats.ordersTrend.map(item => item.month),
  datasets: [{
    label: 'Commandes',
    borderColor: '#10b981',
    fill: false,
    data: props.advancedStats.ordersTrend.map(item => item.count),
  }]
};

const ordersByStatusData = {
  labels: props.advancedStats.ordersByStatus.map(item => item.status),
  datasets: [{
    backgroundColor: ['#f59e0b', '#3b82f6', '#6366f1', '#10b981', '#ef4444'],
    data: props.advancedStats.ordersByStatus.map(item => item.count),
  }]
};
</script>

<template>
  <Head title="Tableau de bord Admin" />

  <AuthenticatedLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <!-- Statistiques principales -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-500">Produits</h3>
            <p class="text-3xl font-bold">{{ stats.products }}</p>
          </div>
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-500">Commandes</h3>
            <p class="text-3xl font-bold">{{ stats.orders }}</p>
          </div>
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-500">Clients</h3>
            <p class="text-3xl font-bold">{{ stats.customers }}</p>
          </div>
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-500">Revenu</h3>
            <p class="text-3xl font-bold">{{ stats.revenue.toLocaleString() }} FCFA</p>
          </div>
        </div>

        <!-- Graphiques principaux -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- CA par mois -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium mb-4">Chiffre d'affaires {{ currentYear }} (FCFA)</h3>
            <Line :data="monthlyRevenueData" :options="{ responsive: true }" height="250" />
          </div>

          <!-- Evolution des commandes -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium mb-4">Évolution des commandes (12 derniers mois)</h3>
            <Line :data="ordersTrendData" :options="{ responsive: true }" height="250" />
          </div>
        </div>

        <!-- Deuxième ligne de graphiques -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Répartition par statut -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium mb-4">Statut des commandes</h3>
            <Pie :data="ordersByStatusData" :options="{ responsive: true }" height="250" />
          </div>

          <!-- Produits les plus vendus -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium mb-4">Top 5 des produits</h3>
            <ul class="space-y-3">
              <li v-for="product in advancedStats.topProducts" :key="product.name" class="flex justify-between">
                <span>{{ product.name }}</span>
                <span class="font-bold">{{ product.total_sold }} vendus</span>
              </li>
            </ul>
          </div>

          <!-- Meilleurs clients -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium mb-4">Top 5 clients</h3>
            <ul class="space-y-3">
              <li v-for="customer in advancedStats.topCustomers" :key="customer.name" class="flex justify-between">
                <span>{{ customer.name }}</span>
                <span class="font-bold">{{ customer.total_spent.toLocaleString() }} FCFA</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Alertes stock -->
        <div v-if="lowStockProducts.length" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 hidden">
          <h3 class="font-bold text-yellow-800">Alertes Stock</h3>
          <ul class="mt-2">
            <li v-for="product in lowStockProducts" :key="product.id" class="text-yellow-700">
              {{ product.name }} ({{ product.stock }} restant)
            </li>
          </ul>
        </div>

        <!-- Commandes récentes -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="text-lg font-medium mb-4">Commandes récentes</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Commande</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Adresse</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="order in recentOrders" :key="order.id">
                    <td class="px-6 py-4 whitespace-nowrap">{{ order.order_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ order.user?.name || order.guest?.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ order.address.city }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ order.total_price.toLocaleString() }} FCFA</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="['px-2 py-1 rounded-full text-xs', statusClasses[order.status]]">
                        {{ order.status }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Actions rapides -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Link href="/admin/products" class="bg-white p-4 rounded-lg shadow text-center hover:bg-gray-50 transition">
            <span class="block text-indigo-600 font-medium">Gérer les produits</span>
          </Link>
          <Link href="/admin/categories" class="bg-white p-4 rounded-lg shadow text-center hover:bg-gray-50 transition">
            <span class="block text-indigo-600 font-medium">Gérer les catégories</span>
          </Link>
          <Link href="/orders" class="bg-white p-4 rounded-lg shadow text-center hover:bg-gray-50 transition">
            <span class="block text-indigo-600 font-medium">Voir toutes les commandes</span>
          </Link>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>