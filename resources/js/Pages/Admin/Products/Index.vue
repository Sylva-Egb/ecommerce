<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Star, Edit3, Trash2, X, Check, Upload, Image as ImageIcon } from 'lucide-vue-next'
import Pagination from '@/Components/Pagination.vue'
defineProps({ products: Object })

const isEditing = ref(false)
const form = useForm({
  id: null,
  name: '',
  description: '',
  price: '',
  category_id: '',
  image: null
})

function openEditModal(product) {
  isEditing.value = true
  form.id = product.id
  form.name = product.name
  form.description = product.description
  form.price = product.price
  form.category_id = product.category_id
  form.image = null
}

function closeModal() {
  isEditing.value = false
  form.reset()
}

function update() {
  form.post(route('admin.products.update', form.id), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      closeModal()
      // Force le rechargement pour voir le message flash
      window.location.reload()
    }
  })
}

function destroy(id) {
  if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
    form.delete(route('admin.products.destroy', id), {
      preserveScroll: true,
      onSuccess: () => {
        // Force le rechargement pour voir le message flash
        window.location.reload()
      }
    })
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Produits" />

    <template #header>
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Liste des Produits</h2>
      <Link
            :href="route('admin.products.create')"
            class="bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-lg flex items-center"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Nouveau produit
        </Link>
    </div>
    </template>

    <div class="max-w-7xl mx-auto p-6 relative">
      <!-- Notification flash avec animation -->
      <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div v-if="$page.props.flash?.success" class="bg-emerald-100 text-emerald-700 px-4 py-3 rounded-lg mb-6 flex items-center shadow-md">
          <Check class="w-5 h-5 mr-2" />
          {{ $page.props.flash.success }}
        </div>
      </transition>

<!--       <pre>
        {{ $page.props.products }}
      </pre> -->

      <!-- Contenu principal avec flou conditionnel -->
      <div :class="{'blur-sm': isEditing, 'transition-all duration-300': true}">
        <div v-if="products.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <transition-group name="list">
            <div
              v-for="product in products.data"
              :key="product.id"
              class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden border border-gray-100 hover:border-gray-200 group"
            >
              <!-- Image produit -->
              <div class="overflow-hidden h-48">
                <img
                  v-if="product.image_url"
                  :src="product.image_url"
                  alt="Image produit"
                  class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400">
                  <ImageIcon class="w-10 h-10" />
                </div>
              </div>

              <!-- Contenu produit -->
              <div class="p-5">
                <div class="flex justify-between items-start mb-2">
                  <h3 class="font-semibold text-lg text-gray-800">{{ product.name }}</h3>
                  <Star
                    :class="product.is_vedette ? 'text-amber-400 fill-amber-400' : 'text-gray-300'"
                    class="w-5 h-5 flex-shrink-0"
                  />
                </div>

                <p class="text-gray-600 mb-3 line-clamp-2">{{ product.description }}</p>

                <div class="flex items-center justify-between">
                  <span class="text-pink-600 font-bold text-lg">{{ product.price }} €</span>
                  <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                    {{ product.category?.name ?? 'N/A' }}
                  </span>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 mt-4 pt-3 border-t border-gray-100">
                  <button
                    @click="openEditModal(product)"
                    class="text-blue-500 hover:text-blue-600 transition-colors flex items-center"
                  >
                    <Edit3 class="w-4 h-4 mr-1" />
                    <span class="text-sm">Modifier</span>
                  </button>
                  <button
                    @click="destroy(product.id)"
                    class="text-red-500 hover:text-red-600 transition-colors flex items-center"
                  >
                    <Trash2 class="w-4 h-4 mr-1" />
                    <span class="text-sm">Supprimer</span>
                  </button>
                </div>
              </div>
            </div>
          </transition-group>
        </div>

        <!-- Aucun produit -->
        <div v-else class="text-center py-12">
          <div class="max-w-md mx-auto">
            <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
              <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-700 mb-1">Aucun produit disponible</h3>
            <p class="text-gray-500">Cliquez sur le bouton "+" pour ajouter un produit.</p>
          </div>
        </div>
      </div>

      <!-- Modal de modification -->
      <transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="isEditing" class="fixed inset-0 flex items-center justify-center z-50 p-4">
          <div class="absolute inset-0 bg-black/10 backdrop-blur-sm" @click="closeModal"></div>

          <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
          >
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg relative z-10 overflow-hidden">
              <!-- En-tête modal -->
              <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                  <h2 class="text-lg font-semibold text-gray-800">Modifier le produit</h2>
                  <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                    <X class="w-5 h-5" />
                  </button>
                </div>
              </div>

              <!-- Formulaire -->
              <form @submit.prevent="update" enctype="multipart/form-data" class="p-6 space-y-5">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                  <input
                    v-model="form.name"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                  <textarea
                    v-model="form.description"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                  ></textarea>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Prix</label>
                  <input
                    v-model="form.price"
                    type="number"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                  <select
                    v-model="form.category_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                  >
                    <option value="">Sélectionner une catégorie</option>
                    <option v-for="category in $page.props.categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                  <div class="mt-1 flex items-center">
                    <label class="cursor-pointer">
                      <span class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-l-lg bg-gray-50 text-gray-700 text-sm">
                        <Upload class="w-4 h-4 mr-2" />
                        Choisir
                      </span>
                      <input type="file" @change="e => form.image = e.target.files[0]" class="sr-only" />
                    </label>
                    <span class="ml-3 text-sm text-gray-500 truncate">
                      {{ form.image ? form.image.name : 'Aucun fichier sélectionné' }}
                    </span>
                  </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                  <button
                    @click="closeModal"
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                  >
                    Annuler
                  </button>
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition disabled:opacity-70 flex items-center"
                  >
                    <span>Mettre à jour</span>
                    <svg v-if="form.processing" class="animate-spin ml-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                  </button>
                </div>
              </form>
            </div>
          </transition>
        </div>
      </transition>

      <!-- Après la liste des produits, avant la fermeture de la div principale -->
        <div v-if="products.data.length" class="mt-8">
        <Pagination :links="products.links" />
        </div>
    </div>
  </AuthenticatedLayout>
</template>

<style>
/* Animation pour les éléments de la liste */
.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(30px);
}
</style>