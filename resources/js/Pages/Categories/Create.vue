<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  description: '',
  image: null
})

function submit() {
  form.post(route('categories.store'), {
    forceFormData: true
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Créer une Catégorie" />

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nouvelle Catégorie</h2>
    </template>

    <div class="max-w-xl mx-auto mt-10 p-4 bg-white shadow rounded">
      <h1 class="text-2xl font-bold mb-4">Créer une catégorie</h1>

      <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-4">
        <div>
          <label class="block text-sm">Nom</label>
          <input v-model="form.name" type="text" class="w-full border p-2 rounded" />
          <span v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</span>
        </div>

        <div>
          <label class="block text-sm">Description</label>
          <textarea v-model="form.description" class="w-full border p-2 rounded"></textarea>
        </div>

        <div>
          <label class="block text-sm">Image</label>
          <input type="file" @change="e => form.image = e.target.files[0]" class="w-full" />
          <span v-if="form.errors.image" class="text-red-500 text-sm">{{ form.errors.image }}</span>
        </div>

        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
          Enregistrer
        </button>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
