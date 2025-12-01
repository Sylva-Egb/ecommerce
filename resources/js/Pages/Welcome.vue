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

// Carousel logic - Only products with images or fallback
const featuredProducts = computed(() => {
  // Filtre les produits vedettes avec images
  let vedettes = props.products.filter(p => p.is_vedette && p.image_url)

  // Si pas assez de vedettes avec images, prend tous les produits avec images
  if (vedettes.length === 0) {
    vedettes = props.products.filter(p => p.image_url).slice(0, 3)
  }

  // Si toujours aucune image, utilise des produits avec images par défaut
  if (vedettes.length === 0) {
    vedettes = props.products.slice(0, 3).map(p => ({
      ...p,
      image_url: p.image_url || 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80'
    }))
  }

  return vedettes
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

    <!-- HERO SECTION WITH CAROUSEL AS BACKGROUND -->
    <section class="relative h-screen min-h-[600px] overflow-hidden">
      <!-- Carousel Background -->
      <div class="absolute inset-0" v-if="featuredProducts.length > 0">
        <div
          v-for="(product, index) in featuredProducts"
          :key="product.id"
          class="absolute inset-0 transition-opacity duration-1000 ease-in-out"
          :class="{'opacity-100': currentSlide === index, 'opacity-0': currentSlide !== index}"
        >
          <img
            :src="product.image_url || 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80'"
            :alt="product.name"
            class="w-full h-full object-cover"
          />
          <!-- Gradient overlay for better text readability -->
          <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/50 to-black/30"></div>
        </div>
      </div>

      <!-- Fallback background when no products -->
      <div v-else class="absolute inset-0">
        <img
          src="https://images.unsplash.com/photo-1596462502278-27bfdc403348?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80"
          alt="Beauty products"
          class="w-full h-full object-cover"
        />
        <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/50 to-black/30"></div>
      </div>

      <!-- Hero Content -->
      <div class="relative z-10 h-full flex items-center">
        <div class="max-w-7xl mx-auto px-6 w-full">
          <div class="max-w-2xl space-y-6">
            <h1 class="text-4xl md:text-5xl lg:text-7xl font-extrabold leading-tight text-white drop-shadow-2xl">
              Sublimez votre <span class="text-pink-400">beauté</span> naturelle
            </h1>
            <p class="text-lg md:text-xl lg:text-2xl text-white/90 drop-shadow-lg">
              Découvrez nos produits phares soigneusement sélectionnés pour révéler votre éclat unique ✨
            </p>

            <div class="flex flex-wrap gap-4 pt-4">
              <Link
                :href="route('products.index')"
                class="bg-pink-600 hover:bg-pink-700 text-white px-8 py-4 rounded-full font-semibold text-lg shadow-2xl hover:shadow-pink-500/50 transition-all transform hover:scale-105"
              >
                Découvrir nos produits
              </Link>
              <Link
                v-if="canRegister && !auth.user"
                :href="route('register')"
                class="border-2 border-white/80 backdrop-blur-sm bg-white/10 text-white hover:bg-white hover:text-pink-600 px-8 py-4 rounded-full font-semibold text-lg transition-all transform hover:scale-105"
              >
                Rejoindre la communauté
              </Link>
            </div>

            <!-- Product Info Badge (current slide) -->
            <div v-if="featuredProducts.length > 0" class="inline-flex items-center gap-3 bg-white/20 backdrop-blur-md rounded-full px-6 py-3 mt-8">
              <Star v-if="featuredProducts[currentSlide]?.is_vedette" class="w-5 h-5 text-amber-400 fill-amber-400" />
              <div>
                <p class="text-white font-semibold text-lg">{{ featuredProducts[currentSlide]?.name }}</p>
                <p class="text-white/80 text-sm">{{ featuredProducts[currentSlide]?.price }} €</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Carousel Controls -->
      <button
        v-if="featuredProducts.length > 1"
        @click="prevSlide"
        @mouseenter="stopCarousel"
        @mouseleave="startCarousel"
        class="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 bg-white/20 backdrop-blur-md hover:bg-white/40 text-white p-3 md:p-4 rounded-full shadow-2xl z-20 transition-all transform hover:scale-110"
        aria-label="Previous slide"
      >
        <ChevronLeft class="w-6 h-6 md:w-8 md:h-8" />
      </button>
      <button
        v-if="featuredProducts.length > 1"
        @click="nextSlide"
        @mouseenter="stopCarousel"
        @mouseleave="startCarousel"
        class="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 bg-white/20 backdrop-blur-md hover:bg-white/40 text-white p-3 md:p-4 rounded-full shadow-2xl z-20 transition-all transform hover:scale-110"
        aria-label="Next slide"
      >
        <ChevronRight class="w-6 h-6 md:w-8 md:h-8" />
      </button>

      <!-- Indicators -->
      <div v-if="featuredProducts.length > 1" class="absolute bottom-8 left-1/2 -translate-x-1/2 flex gap-3 z-20">
        <button
          v-for="(_, index) in featuredProducts"
          :key="index"
          @click="currentSlide = index"
          class="h-2 rounded-full transition-all duration-300 backdrop-blur-sm"
          :class="{
            'bg-white w-12': currentSlide === index,
            'bg-white/50 w-8 hover:bg-white/70': currentSlide !== index
          }"
          :aria-label="`Go to slide ${index + 1}`"
        />
      </div>

      <!-- Scroll Indicator -->
      <div class="absolute bottom-8 left-8 z-20 hidden md:block">
        <div class="flex flex-col items-center gap-2 text-white/60">
          <span class="text-sm font-medium tracking-wider uppercase">Scroll</span>
          <div class="w-px h-12 bg-white/40 animate-pulse"></div>
        </div>
      </div>
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
                <span class="text-pink-600 font-bold text-lg">{{ product.price }} €</span>
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
            class="flex-grow px-4 py-3 rounded-full text-white focus:outline-none focus:ring-2 focus:ring-white"
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
        • &copy; {{ new Date().getFullYear() }} Kyodai Cosmétiques. Tous droits réservés.
      </div>
    </footer>
  </component>
</template>

<style scoped>
/* Smooth transitions for carousel */
.transition-opacity {
  transition-property: opacity;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Animation for scroll indicator */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>