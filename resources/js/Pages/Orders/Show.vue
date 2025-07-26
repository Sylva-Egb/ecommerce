<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    order: Object,
    products: Array,
    canEdit: Boolean,
});

const statusForm = useForm({
    status: props.order.status,
});

const itemForm = useForm({
    product_id: '',
    quantity: 1,
});


</script>

<template>
    <Head :title="'Commande #' + order.order_number" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Commande #{{ order.order_number }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div class="border rounded-lg p-4">
                                <h3 class="font-medium text-gray-900 mb-2">Informations client</h3>
                                <p>{{ order.user?.name || order.guest?.name }}</p>
                                <p>{{ order.user?.email || order.guest?.email }}</p>
                                <p>{{ order.user?.phone || order.guest?.phone }}</p>
                            </div>

                            <div class="border rounded-lg p-4">
                                <h3 class="font-medium text-gray-900 mb-2">Adresse de livraison</h3>
                                <p>{{ order.address.full_name }}</p>
                                <p>{{ order.address.address_line }}</p>
                                <p>{{ order.address.city }}, {{ order.address.country }}</p>
                            </div>

                            <div class="border rounded-lg p-4">
                                <h3 class="font-medium text-gray-900 mb-2">Statut de la commande</h3>
                                <form @submit.prevent="statusForm.put(route('orders.update-status', order.id))" v-if="canEdit">
                                    <select v-model="statusForm.status" class="border-gray-300 rounded-md shadow-sm">
                                        <option value="pending">En attente</option>
                                        <option value="paid">Payée</option>
                                        <option value="shipped">Expédiée</option>
                                        <option value="delivered">Livrée</option>
                                        <option value="cancelled">Annulée</option>
                                    </select>
                                    <button type="submit" class="ml-2 bg-blue-500 text-white px-3 py-1 rounded-md">
                                        Mettre à jour
                                    </button>
                                </form>
                                <span v-else class="capitalize">{{ order.status }}</span>
                            </div>
                        </div>

                        <h3 class="font-medium text-gray-900 mb-4">Articles commandés</h3>
                        <table class="min-w-full divide-y divide-gray-200 mb-8">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix unitaire</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" v-if="canEdit">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in order.items" :key="item.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img :src="item.product.image_url" :alt="item.product.name" class="h-10 w-10 rounded-full">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ item.product.name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ item.unit_price }} FCFA
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ item.quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ (item.unit_price * item.quantity).toFixed(2) }} FCFA
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" v-if="canEdit">
                                        <button @click="removeItem(item.id)" class="text-red-600 hover:text-red-900">
                                            Supprimer
                                        </button>
                                    </td>

                                </tr>
                            </tbody>
                        </table>

                        <div class="flex justify-end mb-8">
                            <div class="text-right">
                                <p class="text-lg font-semibold">Total: {{ order.total_price }} FCFA</p>
                            </div>
                        </div>

                        <div v-if="canEdit && products.length" class="border-t pt-6">
                            <h3 class="font-medium text-gray-900 mb-4">Ajouter un article</h3>
                            <form @submit.prevent="itemForm.post(route('orders.add-item', order.id))" class="flex gap-4">
                                <select v-model="itemForm.product_id" required class="flex-1 border-gray-300 rounded-md shadow-sm">
                                    <option value="">Sélectionner un produit</option>
                                    <option v-for="product in products" :value="product.id">
                                        {{ product.name }} - {{ product.price }} FCFA
                                    </option>
                                </select>
                                <input v-model="itemForm.quantity" type="number" min="1" required class="w-20 border-gray-300 rounded-md shadow-sm">
                                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-md">
                                    Ajouter
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>