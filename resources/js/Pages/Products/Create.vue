<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { InformationCircleIcon } from '@heroicons/vue/24/outline'
import { ref, watch } from 'vue'

const showInfo = ref(false)
const imageRequiredError = ref(false)

const form = useForm({
  name: '',
  description: '',
  price: '',
  slug: '',
  categorie_id: '',
  image: null,
  is_vedette: false
})

// Watch for is_vedette changes to validate image requirement
watch(() => form.is_vedette, (newVal) => {
  if (newVal && !form.image) {
    imageRequiredError.value = true
  } else {
    imageRequiredError.value = false
  }
})

function handleImageChange(e) {
  form.image = e.target.files[0]
  if (form.is_vedette && !form.image) {
    imageRequiredError.value = true
  } else {
    imageRequiredError.value = false
  }
}

function submit() {
  // Validate image requirement for featured products
  if (form.is_vedette && !form.image) {
    imageRequiredError.value = true
    return
  }

  form.is_vedette = form.is_vedette ? 1 : 0


  form.post(route('products.store'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => form.reset('name', 'description', 'price', 'slug', 'categorie_id', 'image'),
    onFinish: () => {
      window.location.href = route('admin.products.index')
    }
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Créer un Produit" />

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nouveau Produit</h2>
    </template>

    <div class="max-w-2xl mx-auto mt-6 p-6 bg-white shadow-lg rounded-lg">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">Créer un nouveau produit</h1>

      <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-6">
        <!-- Nom -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nom du produit</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-pink-500 focus:border-pink-500"
            required
          />
          <span v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</span>
        </div>

        <!-- Slug -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Slug (URL)</label>
          <input
            v-model="form.slug"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-pink-500 focus:border-pink-500"
          />
          <span v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</span>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-pink-500 focus:border-pink-500"
          ></textarea>
        </div>

        <!-- Prix -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Prix (€)</label>
          <input
            v-model="form.price"
            type="number"
            min="0"
            step="0.01"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-pink-500 focus:border-pink-500"
            required
          />
          <span v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</span>
        </div>

        <!-- Catégorie -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
          <select
            v-model="form.categorie_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-pink-500 focus:border-pink-500"
            required
          >
            <option value="" disabled>Sélectionnez une catégorie</option>
            <option v-for="cat in $page.props.categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
          <span v-if="form.errors.categorie_id" class="mt-1 text-sm text-red-600">{{ form.errors.categorie_id }}</span>
        </div>

        <!-- Image -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Image du produit
            <span v-if="form.is_vedette" class="text-red-500">*</span>
          </label>
          <input
            @change="handleImageChange"
            type="file"
            accept="image/*"
            class="block w-full text-sm text-gray-500
                  file:mr-4 file:py-2 file:px-4
                  file:rounded-md file:border-0
                  file:text-sm file:font-semibold
                  file:bg-pink-50 file:text-pink-700
                  hover:file:bg-pink-100"
            :required="form.is_vedette"
          />
          <span v-if="imageRequiredError" class="mt-1 text-sm text-red-600">Une image est requise pour les produits vedettes</span>
          <span v-if="form.errors.image" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</span>
        </div>

        <!-- Produit vedette -->
        <div class="flex items-center justify-between p-4 border rounded-lg bg-gray-50">
          <div class="flex items-center space-x-2">
            <span class="font-medium text-gray-700">Produit vedette</span>

            <div class="relative">
              <button
                type="button"
                @mouseenter="showInfo = true"
                @mouseleave="showInfo = false"
                class="text-gray-400 hover:text-gray-500"
              >
                <InformationCircleIcon class="h-5 w-5" />
              </button>

              <!-- Bulle d'information -->
              <div
                v-show="showInfo"
                class="absolute z-10 w-64 p-3 mt-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg shadow-lg"
              >
                En activant cette option, le produit sera mis en avant sur la page d'accueil comme produit phare.
                Une image est obligatoire pour les produits vedettes.
              </div>
            </div>
          </div>

          <!-- Bouton toggle -->
          <button
            type="button"
            @click="form.is_vedette = !form.is_vedette"
            :class="form.is_vedette ? 'bg-pink-600' : 'bg-gray-200'"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
          >
            <span
              :class="form.is_vedette ? 'translate-x-6' : 'translate-x-1'"
              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
            />
          </button>
        </div>

        <!-- Bouton de soumission -->
        <div class="pt-4">
          <button
            type="submit"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500"
            :disabled="form.processing"
          >
            <span v-if="form.processing">Enregistrement...</span>
            <span v-else>Enregistrer le produit</span>
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>