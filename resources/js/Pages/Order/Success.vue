<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CheckCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const Layout = computed(() => page.props.auth.user ? AuthenticatedLayout : GuestLayout);

const props = defineProps({
    order: Object,
    products: Array,
});
</script>

<template>
    <Layout>
        <Head title="Commande confirmée" />

        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden max-w-2xl mx-auto">
                <!-- En-tête avec votre style gradient -->
                <div class="bg-gradient-to-r from-pink-600 to-amber-600 p-8 text-white text-center">
                    <CheckCircle class="w-16 h-16 mx-auto mb-4" />
                    <h1 class="text-3xl font-bold">Commande confirmée !</h1>
                    <p class="mt-2">Numéro de commande: #{{ order.order_number }}</p>
                </div>

                <!-- Contenu amélioré -->
                <div class="p-8">
                    <!-- Liste des produits -->
                    <div v-for="product in products" :key="product.id"
                         class="flex items-start border-b border-gray-200 dark:border-gray-700 py-4 last:border-0">
                        <img :src="product.image_url || '/placeholder-product.jpg'"
                             :alt="product.name"
                             class="w-20 h-20 object-cover rounded-lg mr-4">
                        <div class="flex-1">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ product.name }}</h2>
                            <p class="text-gray-600 dark:text-gray-300">
                                {{ product.quantity }} × {{ product.price }} FCFA
                            </p>
                        </div>
                        <div class="text-pink-600 dark:text-pink-400 font-bold">
                            {{ product.price * product.quantity }} FCFA
                        </div>
                    </div>

                    <!-- Total de la commande -->
                    <div class="flex justify-between items-center mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <span class="text-lg font-semibold">Total</span>
                        <span class="text-2xl font-bold text-pink-600 dark:text-pink-400">
                            {{ order.total_price }} FCFA
                        </span>
                    </div>

                    <!-- Actions - style conservé -->
                    <div class="space-y-4 mt-8">
                        <a :href="order.invoice_url" target="_blank"
                           class="block w-full px-6 py-3 bg-pink-600 hover:bg-pink-700 text-white font-medium rounded-lg text-center transition flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            Télécharger la facture
                        </a>

                        <Link :href="page.props.auth.user ? route('orders.index') : route('home')"
                           class="block w-full px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-lg text-center transition">
                            {{ page.props.auth.user ? 'Voir mes commandes' : 'Retour à l\'accueil' }}
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>