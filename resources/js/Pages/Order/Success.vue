<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle } from 'lucide-vue-next';

const props = defineProps({
    order: Object,
    products: Array,
});
</script>

<template>
    <GuestLayout>
        <Head title="Commande confirmée" />

        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden max-w-2xl mx-auto">
                <!-- Header -->
                <div class="bg-gradient-to-r from-pink-600 to-amber-600 p-8 text-white text-center">
                    <CheckCircle class="w-16 h-16 mx-auto mb-4" />
                    <h1 class="text-3xl font-bold">Commande confirmée !</h1>
                    <p class="mt-2">Numéro de commande: #{{ order.order_number }}</p>
                </div>

                <!-- Content -->
                <div class="p-8">
                    <div v-for="product in products" :key="product.id" class="flex items-start mb-6">
                        <img :src="product.image_url || '/placeholder-product.jpg'" :alt="product.name"
                             class="w-20 h-20 object-cover rounded-lg mr-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ product.name }}</h2>
                            <p class="text-gray-600 dark:text-gray-300">
                                {{ product.quantity }} × {{ product.price }} FCFA
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4 mt-8">
                        <a :href="order.invoice_url" target="_blank"
                           class="block w-full px-6 py-3 bg-pink-600 hover:bg-pink-700 text-white font-medium rounded-lg text-center transition flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            Télécharger la facture
                        </a>

                        <Link :href="route('home')"
                           class="block w-full px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-lg text-center transition">
                            Retour à l'accueil
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>