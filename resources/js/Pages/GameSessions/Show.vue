<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'

import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

defineProps({
  game_session: {
    type: Object,
    required: true,
  },
})

const partecipants = reactive([
  { name: 'John Doe' },
  { name: 'Jane Doe' },
])

function gotoSessions () {
  router.get(`/game-sessions`)
}
</script>

<template>

  <Head :title="`Game Session - ${game_session.title}`" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Game Sessions</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
          <div
            class="px-12 py-8 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4"
          >
            <h1 class="mb-4 text-4xl font-extrabold leading-none tracking-tight text-gray-900 dark:text-white">
              {{ game_session.title }}
            </h1>

            <p class="mt-4 text-gray-500 dark:text-gray-400">
              {{ game_session.description }}
            </p>

            <p class="mt-4 text-gray-500 dark:text-gray-400">
              Max partecipants: {{ game_session.max_partecipants }}
            </p>
          </div>

          <div
            class="px-12 py-8 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4"
          >
            <h1 class="mb-4 text-4xl font-extrabold leading-none tracking-tight text-gray-900 dark:text-white">
              Domande
            </h1>

            <p class="mt-4 text-gray-500 dark:text-gray-400">
              Domande con i relativi controlli
            </p>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="px-8 py-8 text-gray-900 dark:text-gray-100">
            <h3 class="mb-3 text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white">
              Partecipants
            </h3>

            <div class="overflow-y-auto h-full">
              <ul>
                <li v-for="partecipant in partecipants">
                  {{ partecipant.name }}
                </li>
              </ul>
            </div>
          </div>
        </div>


      </div>
    </div>
  </AuthenticatedLayout>
</template>