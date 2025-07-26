<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { InformationCircleIcon } from '@heroicons/vue/24/outline'
import { ref } from 'vue'

const isVedette = ref(false)
const showInfo = ref(false)

const form = useForm({
  name: '',
  description: '',
  price: '',
  slug: '',
  categorie_id: '',
  image: null,
  is_vedette: false
})

function submit() {
  form.post(route('products.store'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => form.reset('name', 'description', 'price', 'slug', 'categorie_id', 'image'),
    onFinish: () => {
      window.location.href = route('admin.products.index');
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

    <div class="max-w-xl mx-auto mt-10 p-4 bg-white shadow rounded">
      <h1 class="text-2xl font-bold mb-4">Créer un produit</h1>

      <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-4">
        <div>
          <label class="block text-sm">Nom</label>
          <input v-model="form.name" type="text" class="w-full border p-2 rounded" />
          <span v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</span>
        </div>

        <div>
          <label class="block text-sm">Slug</label>
          <input v-model="form.slug" type="text" class="w-full border p-2 rounded" />
          <span v-if="form.errors.slug" class="text-red-500 text-sm">{{ form.errors.slug }}</span>
        </div>

        <div>
          <label class="block text-sm">Description</label>
          <textarea v-model="form.description" class="w-full border p-2 rounded"></textarea>
        </div>

        <div>
          <label class="block text-sm">Prix</label>
          <input v-model="form.price" type="number" class="w-full border p-2 rounded" />
          <span v-if="form.errors.price" class="text-red-500 text-sm">{{ form.errors.price }}</span>
        </div>

        <div>
          <label class="block text-sm">Catégorie</label>
          <select v-model="form.categorie_id" class="w-full border p-2 rounded">
            <option value="" disabled>Sélectionne une catégorie</option>
            <option v-for="cat in $page.props.categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
          <span v-if="form.errors.categorie_id" class="text-red-500 text-sm">{{ form.errors.categorie_id }}</span>
        </div>

        <div>
          <label class="block text-sm">Image</label>
          <input @change="e => form.image = e.target.files[0]" type="file" class="w-full" />
          <span v-if="form.errors.image" class="text-red-500 text-sm">{{ form.errors.image }}</span>
        </div>

                <!-- Nouveau champ is_vedette -->
        <div class="flex items-center justify-between p-4 border rounded-lg bg-gray-50">
          <div class="flex items-center space-x-2">
            <span class="font-medium">Produit vedette</span>

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
              </div>
            </div>
          </div>

          <!-- Bouton toggle -->
          <button
            type="button"
            @click="isVedette = !isVedette"
            :class="isVedette ? 'bg-pink-600' : 'bg-gray-200'"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
          >
            <span
              :class="isVedette ? 'translate-x-6' : 'translate-x-1'"
              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
            />
          </button>
        </div>

        <button type="submit" class="bg-pink-600 text-white px-4 py-2 rounded hover:bg-pink-700">
          Enregistrer
        </button>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
