<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { Star, ChevronRight, ShoppingCart, Zap, Heart, Share2, X, CheckCircle } from 'lucide-vue-next'
import { ref, computed } from 'vue'
import { useCartStore } from '@/Stores/cartStore'
/* importer router */
import { router } from '@inertiajs/vue3'

const cartStore = useCartStore()
const page = usePage()

// Déterminer le layout à utiliser
const Layout = computed(() => page.props.auth.user ? AuthenticatedLayout : GuestLayout)

const props = defineProps({
    product: Object,
    relatedProducts: Array,
})

const quantity = ref(1)
const isWishlisted = ref(false)
const showOrderModal = ref(false)
const showSuccessModal = ref(false)
const orderData = ref(null)
const createAccount = ref(false)
const password = ref('')
const isSelfReceiving = ref(true)

// Préremplir les infos si l'utilisateur est connecté
const form = useForm({
    product_id: null,
    quantity: 1,
    create_account: false,
    password: '',
    guest: {
        name: page.props.auth.user?.name || '',
        email: page.props.auth.user?.email || '',
        phone: page.props.auth.user?.phone || ''
    },
    address: {
        full_name: page.props.auth.user?.name || '',
        address_line: page.props.auth.user?.address?.address_line || '',
        city: page.props.auth.user?.address?.city || '',
        zip_code: page.props.auth.user?.address?.zip_code || '',
        country: page.props.auth.user?.address?.country || 'Côte d\'Ivoire',
        phone: page.props.auth.user?.phone || ''
    }
})

const showCopiedNotification = ref(false)
const notificationTimeout = ref(null)

const addToCart = () => {
    cartStore.addItem({
        id: props.product.id,
        name: props.product.name,
        price: props.product.price,
        image: props.product.image_url,
        quantity: quantity.value
    })
}

function openOrderModal() {
    form.product_id = props.product.id
    form.quantity = quantity.value

    // Si l'utilisateur est connecté, préremplir avec ses infos
    if (page.props.auth.user) {
        form.guest.name = page.props.auth.user.name
        form.guest.email = page.props.auth.user.email
        form.guest.phone = page.props.auth.user.phone

        if (page.props.auth.user.address) {
            form.address = {
                full_name: page.props.auth.user.name,
                address_line: page.props.auth.user.address.address_line,
                city: page.props.auth.user.address.city,
                zip_code: page.props.auth.user.address.zip_code,
                country: page.props.auth.user.address.country,
                phone: page.props.auth.user.phone
            }
        }
    }

    showOrderModal.value = true
}

function submitOrder() {
    const items = cartStore.items.length > 0
        ? cartStore.items.map(item => ({
            product_id: item.id,
            quantity: item.quantity
        }))
        : [{
            product_id: props.product.id,
            quantity: quantity.value
        }];

    const formData = {
        items,
        create_account: createAccount.value,
        password: createAccount.value ? password.value : undefined,
        is_self_receiving: isSelfReceiving.value,
        address: isSelfReceiving.value ? null : {
            full_name: form.address.full_name,
            address_line: form.address.address_line,
            city: form.address.city,
            zip_code: form.address.zip_code,
            country: form.address.country,
            phone: form.address.phone
        }
    };

    form.transform(() => formData).post(route('orders.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showOrderModal.value = false
        },
        onError: (errors) => {
            console.log('Erreurs de validation:', errors);
        }
    });
}

function shareProduct() {
    const productUrl = window.location.href

    navigator.clipboard.writeText(productUrl)
        .then(() => {
            showCopiedNotification.value = true
            if (notificationTimeout.value) {
                clearTimeout(notificationTimeout.value)
            }
            notificationTimeout.value = setTimeout(() => {
                showCopiedNotification.value = false
            }, 3000)
        })
        .catch(err => {
            console.error('Erreur lors de la copie:', err)
            const textArea = document.createElement('textarea')
            textArea.value = productUrl
            document.body.appendChild(textArea)
            textArea.select()
            document.execCommand('copy')
            document.body.removeChild(textArea)

            showCopiedNotification.value = true
            notificationTimeout.value = setTimeout(() => {
                showCopiedNotification.value = false
            }, 3000)
        })
}
</script>

<template>
    <Layout>
        <Head :title="product.name" />

        <!-- Notification de copie -->
        <transition name="fade">
            <div v-if="showCopiedNotification"
                class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center">
                <CheckCircle class="w-5 h-5 mr-2" />
                Lien copié avec succès !
            </div>
        </transition>

        <section class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="flex mb-8" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                    <li class="inline-flex items-center">
                        <Link :href="route('home')"
                            class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-pink-600 dark:text-gray-400 dark:hover:text-white">
                        Accueil
                        </Link>
                    </li>
                    <li v-if="product.category">
                        <div class="flex items-center">
                            <ChevronRight class="w-4 h-4 text-gray-400 mx-1" />
                            <Link :href="route('categories.show', product.category.id)"
                                class="ml-1 text-sm font-medium text-gray-700 hover:text-pink-600 md:ml-2 dark:text-gray-400 dark:hover:text-white">
                            {{ product.category.name }}
                            </Link>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <ChevronRight class="w-4 h-4 text-gray-400 mx-1" />
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">{{
                                product.name }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Product Section -->
            <div class="grid md:grid-cols-2 gap-8 lg:gap-12">
                <!-- Image Gallery -->
                <div class="space-y-4">
                    <div class="relative overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800 aspect-square">
                        <img :src="product.image_url || 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'"
                            :alt="product.name"
                            class="w-full h-full object-cover transition-transform duration-500 hover:scale-105" />
                        <div v-if="product.is_vedette"
                            class="absolute top-4 left-4 bg-amber-400 text-white px-3 py-1 rounded-full text-xs font-bold flex items-center z-10">
                            <Star class="w-4 h-4 mr-1 fill-white" />
                            Produit vedette
                        </div>
                    </div>
                </div>

                <!-- Product Details -->
                <div>
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-2">{{
                                product.name }}</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                Catégorie:
                                <Link v-if="product.category" :href="route('categories.show', product.category.id)"
                                    class="font-medium text-pink-600 hover:text-pink-700 dark:text-pink-400 dark:hover:text-pink-300">
                                {{ product.category.name }}
                                </Link>
                                <span v-else class="font-medium">Non catégorisé</span>
                            </p>
                        </div>

                        <button @click="isWishlisted = !isWishlisted"
                            class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                            aria-label="Ajouter aux favoris">
                            <Heart class="w-6 h-6"
                                :class="isWishlisted ? 'text-pink-600 fill-pink-600' : 'text-gray-400'" />
                        </button>
                    </div>

                    <div class="mb-6">
                        <p class="text-3xl font-bold text-pink-600 dark:text-pink-400">{{ product.price }} FCFA</p>
                        <p v-if="product.old_price" class="text-sm text-gray-500 line-through">{{ product.old_price }}
                            FCFA</p>
                    </div>

                    <div class="prose max-w-none text-gray-700 dark:text-gray-300 mb-8 whitespace-pre-line">
                        {{ product.description }}
                    </div>

                    <!-- Quantity Selector -->
                    <div class="flex items-center mb-8">
                        <span class="mr-4 text-gray-700 dark:text-gray-300">Quantité:</span>
                        <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                            <button @click="quantity > 1 ? quantity-- : null"
                                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 transition">
                                −
                            </button>
                            <span class="px-4 py-2 w-12 text-center">{{ quantity }}</span>
                            <button @click="quantity++"
                                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 transition">
                                +
                            </button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-4 mb-8">
                        <button @click="addToCart"
                            class="flex-1 flex items-center justify-center gap-2 bg-pink-600 hover:bg-pink-700 text-white px-6 py-3 rounded-lg shadow-md hover:shadow-lg transition-all">
                            <ShoppingCart class="w-5 h-5" />
                            Ajouter au panier
                        </button>
                        <button @click="openOrderModal"
                            class="flex-1 flex items-center justify-center gap-2 bg-gradient-to-r from-pink-500 to-amber-500 hover:from-pink-600 hover:to-amber-600 text-white px-6 py-3 rounded-lg shadow-md hover:shadow-lg transition-all">
                            <Zap class="w-5 h-5" />
                            Commander
                        </button>
                    </div>

                    <!-- Share & Info -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <div class="flex items-center justify-between">
                            <button @click="shareProduct"
                                class="flex items-center text-gray-500 hover:text-pink-600 dark:text-gray-400 dark:hover:text-pink-400 transition">
                                <Share2 class="w-4 h-4 mr-2" />
                                Partager
                            </button>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                Référence: {{ product.id }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products Section -->
            <div v-if="product.related_products && product.related_products.length" class="mt-16">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Produits similaires</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <div v-for="related in product.related_products" :key="related.id"
                        class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-100 dark:border-gray-700">
                        <Link :href="route('products.show', related.id)" class="block">
                        <div class="relative overflow-hidden h-48">
                            <img :src="related.image_url || 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'"
                                :alt="related.name"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-lg mb-1 line-clamp-1">{{ related.name }}</h3>
                            <p class="text-pink-600 dark:text-pink-400 font-bold">{{ related.price }} FCFA</p>
                        </div>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modal de commande -->
        <transition name="modal">
            <div v-if="showOrderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div @click.self="showOrderModal = false"
                    class="fixed inset-0 bg-black/30 backdrop-blur-sm transition-opacity">
                </div>

                <div
                    class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-gray-200 dark:border-gray-700">
                    <!-- En-tête -->
                    <div
                        class="sticky top-0 bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center z-10">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Passer commande</h2>
                        <button @click="showOrderModal = false"
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            <X class="w-6 h-6" />
                        </button>
                    </div>

                    <!-- Contenu -->
                    <div class="p-6">
                        <!-- Récapitulatif produit -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <img :src="product.image_url || 'https://via.placeholder.com/300'" :alt="product.name"
                                    class="w-16 h-16 object-cover rounded-md mr-4" />
                                <div>
                                    <h3 class="font-medium text-gray-900 dark:text-white">{{ product.name }}</h3>
                                    <p class="text-pink-600 dark:text-pink-400 font-bold">
                                        {{ product.price }} FCFA × {{ quantity }} = {{ (product.price *
                                            quantity).toFixed(2) }} FCFA
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Formulaire client -->
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                    {{ page.props.auth.user ? 'Vos informations' : 'Informations client' }}
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom
                                            complet *</label>
                                        <input v-model="form.guest.name" type="text" required
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white"
                                            :disabled="!!page.props.auth.user" />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                        <input v-model="form.guest.email" type="email"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white"
                                            :disabled="!!page.props.auth.user" />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Téléphone
                                            *</label>
                                        <input v-model="form.guest.phone" type="tel" required
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white"
                                            :disabled="!!page.props.auth.user" />
                                    </div>
                                </div>
                            </div>

                            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="isSelfReceiving"
                                        class="rounded text-pink-600 focus:ring-pink-500">
                                    <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Je suis le destinataire de la commande
                                    </span>
                                </label>
                            </div>

                            <!-- Adresse de livraison -->
                            <div v-if="!isSelfReceiving">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Adresse de livraison
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom
                                            complet *</label>
                                        <input v-model="form.address.full_name" type="text" required
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white" />
                                    </div>
                                    <div class="md:col-span-2">
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Adresse
                                            *</label>
                                        <input v-model="form.address.address_line" type="text" required
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white" />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ville
                                            *</label>
                                        <input v-model="form.address.city" type="text" required
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white" />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code
                                            postal</label>
                                        <input v-model="form.address.zip_code" type="text"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white" />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pays
                                            *</label>
                                        <select v-model="form.address.country" required
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white">
                                            <option value="Côte d'Ivoire">Côte d'Ivoire</option>
                                            <option value="France">France</option>
                                            <option value="Autre">Autre</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Téléphone
                                            *</label>
                                        <input v-model="form.address.phone" type="tel" required
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Création de compte (seulement pour les invités) -->
                        <div v-if="!page.props.auth.user" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" v-model="createAccount"
                                    class="rounded text-pink-600 focus:ring-pink-500">
                                <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Créer un compte pour suivre ma commande et bénéficier d'avantages
                                </span>
                            </label>

                            <div v-if="createAccount" class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Mot de passe *
                                    </label>
                                    <input v-model="password" type="password" :required="createAccount"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:text-white" />
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Minimum 8 caractères
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Pied de page -->
                        <div
                            class="sticky bottom-0 bg-white dark:bg-gray-800 px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                            <button @click="showOrderModal = false" type="button"
                                class="mr-3 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Annuler
                            </button>
                            <button @click="submitOrder" type="button"
                                class="px-6 py-2 bg-gradient-to-r from-pink-600 to-amber-600 text-white rounded-lg hover:from-pink-700 hover:to-amber-700 transition disabled:opacity-70"
                                :disabled="form.processing">
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
                <div @click.self="showSuccessModal = false"
                    class="fixed inset-0 bg-black/30 backdrop-blur-sm transition-opacity">
                </div>

                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full overflow-hidden">
                    <!-- En-tête -->
                    <div class="bg-gradient-to-r from-pink-600 to-amber-600 p-6 text-white text-center">
                        <CheckCircle class="w-12 h-12 mx-auto mb-2" />
                        <h3 class="text-xl font-bold">Commande confirmée !</h3>
                    </div>

                    <!-- Corps -->
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <img :src="orderData.product_image || 'https://via.placeholder.com/300'"
                                :alt="orderData.product_name" class="w-16 h-16 object-cover rounded-md mr-4">
                            <div>
                                <p class="text-gray-600 dark:text-gray-300">
                                    Votre commande <strong>#{{ orderData.order_number }}</strong> a bien été
                                    enregistrée.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-3 mt-6">
                            <a :href="orderData.invoice_url" target="_blank"
                                class="w-full px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                                Télécharger la facture
                            </a>
                            <Link :href="page.props.auth.user ? route('orders.index') : route('home')"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                {{ page.props.auth.user ? 'Voir mes commandes' : 'Retour à l\'accueil' }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </Layout>
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

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(20px);
}
</style>