<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'

import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

const form = reactive({
  id: '',
  title: '',
  description: '',
  max_partecipants: '',
  errors: {},
})

function submit () {
  router.post('/game-sessions', form)
}
</script>

<template>

  <Head title="Game Session" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Game Session</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="px-12 py-8 text-gray-900 dark:text-gray-100 w-full">
            <form
              @submit.prevent="submit"
              class="flex flex-col"
            >
              <h1
                v-if="form.id"
                class="text-h1"
              >Create a new Game Session</h1>
              <h1 v-else>Edit Game Session</h1>

              <div class="mb-4">
                <label
                  for="title"
                  class="block text-gray-700 dark:text-white text-sm font-bold mb-2"
                >Title</label>
                <input
                  id="title"
                  v-model="form.title"
                  class="shadow appearance-none border rounded w-full py-2 px-3 dark:bg-gray-200 text-gray-900 leading-tight focus:outline-none focus:shadow-outline"
                  type="text"
                  placeholder="Title"
                >
                <div v-if="form.errors?.title">{{ form.errors.title }}</div>
              </div>

              <div class="mb-4">
                <label
                  for="description"
                  class="block text-gray-700 dark:text-white text-sm font-bold mb-2"
                >Description</label>
                <textarea
                  id="description"
                  v-model="form.description"
                  class="shadow appearance-none border rounded w-full py-2 px-3 dark:bg-gray-200 text-gray-900 leading-tight focus:outline-none focus:shadow-outline"
                  placeholder="Description"
                ></textarea>
                <div v-if="form.errors?.description">{{ form.errors.description }}</div>
              </div>

              <div class="mb-4">
                <label
                  for="max_partecipants"
                  class="block text-gray-700 dark:text-white text-sm font-bold mb-2"
                >Max Partecipants</label>
                <input
                  id="max_partecipants"
                  v-model="form.max_partecipants"
                  class="shadow appearance-none border rounded w-full py-2 px-3 dark:bg-gray-200 text-gray-900 leading-tight focus:outline-none focus:shadow-outline"
                  type="number"
                  placeholder="Max Partecipants"
                >
                <div v-if="form.errors?.max_partecipants">{{ form.errors.max_partecipants }}</div>
              </div>
              <button
                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                type="submit"
                :disabled="form.processing"
              >Save</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>