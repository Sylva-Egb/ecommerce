<script setup>
import { Head, Link } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { computed, ref } from 'vue'
import { onMounted, onBeforeUnmount } from 'vue'
import { ChevronLeft, ChevronRight, Star } from 'lucide-vue-next'

const props = defineProps({
  auth: {
    type: Object,
    default: () => ({}),
  },
  canLogin: Boolean,
  canRegister: Boolean,
  products: Array,
  laravelVersion: String,
  phpVersion: String,
})

// Dynamic layout based on authentication
const layout = computed(() => props.auth.user ? AuthenticatedLayout : GuestLayout)

// Carousel logic
const featuredProducts = computed(() => {
  const vedettes = props.products.filter(p => p.is_vedette)
  return vedettes.length > 0 ? vedettes : props.products.slice(0, 3)
})

const currentSlide = ref(0)

function nextSlide() {
  currentSlide.value = (currentSlide.value + 1) % featuredProducts.value.length
}

function prevSlide() {
  currentSlide.value = (currentSlide.value - 1 + featuredProducts.value.length) % featuredProducts.value.length
}

// Auto-rotate carousel
const interval = ref(null)

function startCarousel() {
  interval.value = setInterval(nextSlide, 5000)
}

function stopCarousel() {
  if (interval.value) {
    clearInterval(interval.value)
    interval.value = null
  }
}

onMounted(() => {
  startCarousel()
})

onBeforeUnmount(() => {
  stopCarousel()
})
</script>

<template>
  <component :is="layout">
    <Head title="Accueil" />

    <!-- HERO SECTION WITH CAROUSEL -->
    <section class="relative bg-gradient-to-r from-pink-50 to-indigo-50 dark:from-gray-900 dark:to-gray-800 text-gray-800 dark:text-white py-16 md:py-24 px-6 overflow-hidden">
      <div class="max-w-7xl mx-auto flex flex-col lg:flex-row items-center justify-between gap-10 relative z-10">
        <div class="flex-1 space-y-6">
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight">
            Sublimez votre <span class="text-pink-600">beauté</span> naturelle
          </h1>
          <p class="text-lg md:text-xl text-gray-600 dark:text-gray-300 max-w-2xl">
            Découvrez nos produits phares soigneusement sélectionnés pour révéler votre éclat unique ✨
          </p>

          <div class="flex flex-wrap gap-4">
            <Link
              v-if="canRegister && !auth.user"
              :href="route('register')"
              class="border-2 border-pink-600 text-pink-600 hover:bg-pink-600 hover:text-white px-8 py-3 rounded-full font-medium transition-all"
            >
              Rejoindre la communauté
            </Link>
          </div>
        </div>

        <!-- CAROUSEL SECTION -->
        <div class="flex-1 w-full max-w-2xl relative">
          <div class="relative overflow-hidden rounded-2xl shadow-2xl h-96">
            <div
              v-for="(product, index) in featuredProducts"
              :key="product.id"
              class="absolute inset-0 transition-opacity duration-500 ease-in-out"
              :class="{'opacity-100 z-10': currentSlide === index, 'opacity-0 z-0': currentSlide !== index}"
              @mouseenter="stopCarousel"
              @mouseleave="startCarousel"
            >
              <div class="relative h-full w-full">
                <img
                  :src="product.image_url || 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'"
                  :alt="product.name"
                  class="w-full h-full object-cover"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent flex flex-col justify-end p-6">
                  <div class="flex items-center gap-2 mb-2">
                    <Star v-if="product.is_vedette" class="w-5 h-5 text-amber-400 fill-amber-400" />
                    <h3 class="text-xl font-bold text-white">{{ product.name }}</h3>
                  </div>
                  <p class="text-white/90 line-clamp-2 mb-3">{{ product.description }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Carousel controls -->
          <button
            @click="prevSlide"
            class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-pink-600 p-2 rounded-full shadow-md z-20 transition"
            aria-label="Previous slide"
          >
            <ChevronLeft class="w-6 h-6" />
          </button>
          <button
            @click="nextSlide"
            class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-pink-600 p-2 rounded-full shadow-md z-20 transition"
            aria-label="Next slide"
          >
            <ChevronRight class="w-6 h-6" />
          </button>

          <!-- Indicators -->
          <div class="flex justify-center gap-2 mt-4">
            <button
              v-for="(_, index) in featuredProducts"
              :key="index"
              @click="currentSlide = index"
              class="w-3 h-3 rounded-full transition"
              :class="{'bg-pink-600 w-6': currentSlide === index, 'bg-gray-300': currentSlide !== index}"
              aria-label="Go to slide"
            />
          </div>
        </div>
      </div>

      <!-- Decorative elements -->
      <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-pink-200 rounded-full opacity-20 dark:opacity-10 blur-3xl"></div>
      <div class="absolute -top-20 -left-20 w-64 h-64 bg-indigo-200 rounded-full opacity-20 dark:opacity-10 blur-3xl"></div>
    </section>

    <!-- PRODUCTS SECTION -->
    <section class="py-16 px-6 bg-white dark:bg-gray-950">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">Nos Produits Phares</h2>
          <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
            Découvrez une sélection de nos meilleurs produits pour prendre soin de vous
          </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
          <div
            v-for="product in products"
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
              <h3 class="font-semibold text-lg mb-1">{{ product.name }}</h3>
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
        <div class="text-center mt-12">
        <Link
          :href="route('products.index')"
          class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-full shadow-sm text-white bg-pink-600 hover:bg-pink-700 transition"
        >
          Voir toute la collection
        </Link>
      </div>
      </div>
    </section>

    <!-- BENEFITS SECTION -->
    <section class="py-16 px-6 bg-gradient-to-r from-pink-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900">
      <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-sm hover:shadow-md transition">
            <div class="w-14 h-14 bg-pink-100 dark:bg-pink-900 rounded-full flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2">Produits de qualité</h3>
            <p class="text-gray-600 dark:text-gray-400">Des formulations soigneusement sélectionnées pour une efficacité maximale.</p>
          </div>

          <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-sm hover:shadow-md transition">
            <div class="w-14 h-14 bg-pink-100 dark:bg-pink-900 rounded-full flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2">Livraison rapide</h3>
            <p class="text-gray-600 dark:text-gray-400">Recevez vos produits en 24-48h dans toute la région.</p>
          </div>

          <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-sm hover:shadow-md transition">
            <div class="w-14 h-14 bg-pink-100 dark:bg-pink-900 rounded-full flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2">Paiement sécurisé</h3>
            <p class="text-gray-600 dark:text-gray-400">Transactions cryptées pour une protection maximale de vos données.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- NEWSLETTER SECTION -->
    <section class="py-16 px-6 bg-white dark:bg-gray-900">
      <div class="max-w-4xl mx-auto bg-gradient-to-r from-pink-500 to-indigo-500 rounded-2xl p-8 text-center text-white">
        <h3 class="text-2xl md:text-3xl font-bold mb-4">Abonnez-vous à notre newsletter</h3>
        <p class="mb-6 text-pink-100 max-w-2xl mx-auto">Recevez nos offres exclusives et conseils beauté directement dans votre boîte mail.</p>
        <form class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
          <input
            type="email"
            placeholder="Votre email"
            class="flex-grow px-4 py-3 rounded-full text-gray-900 focus:outline-none focus:ring-2 focus:ring-white"
            required
          />
          <button
            type="submit"
            class="bg-white text-pink-600 px-6 py-3 rounded-full font-medium hover:bg-gray-100 transition"
          >
            S'abonner
          </button>
        </form>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 py-12 px-6">
      <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
        <div>
          <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Kyodai Cosmétiques</h4>
          <p class="text-sm">Votre destination beauté pour des produits de qualité et des conseils experts.</p>
        </div>
        <div>
          <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Navigation</h4>
          <ul class="space-y-2">
            <li><Link :href="route('home')" class="hover:text-pink-600 transition">Accueil</Link></li>
            <li><Link href="#" class="hover:text-pink-600 transition">Blog</Link></li>
            <li><Link href="#" class="hover:text-pink-600 transition">Contact</Link></li>
          </ul>
        </div>
        <div>
          <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Légal</h4>
          <ul class="space-y-2">
            <li><Link href="#" class="hover:text-pink-600 transition">CGV</Link></li>
            <li><Link href="#" class="hover:text-pink-600 transition">Mentions légales</Link></li>
            <li><Link href="#" class="hover:text-pink-600 transition">Politique de confidentialité</Link></li>
          </ul>
        </div>
        <div>
          <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Contact</h4>
          <address class="not-italic text-sm">
            <p>123 Rue de la Beauté</p>
            <p>75000 Paris, France</p>
            <p class="mt-2">contact@kyodai.com</p>
            <p>+33 1 23 45 67 89</p>
          </address>
        </div>
      </div>
      <div class="max-w-7xl mx-auto pt-8 mt-8 border-t border-gray-200 dark:border-gray-800 text-center text-sm">
        Laravel v{{ laravelVersion }} / PHP v{{ phpVersion }} • &copy; {{ new Date().getFullYear() }} Kyodai Cosmétiques. Tous droits réservés.
      </div>
    </footer>
  </component>
</template>

<style scoped>
/* Custom transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>