<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Alert from '@/Components/Alert.vue'
import StatusBadge from '@/Components/GameSession/StatusBadge.vue'
import { Head } from '@inertiajs/vue3'

import { computed, reactive } from 'vue'
import { router } from '@inertiajs/vue3'

const { game_session, auth } = defineProps({
  game_session: {
    type: Object,
    required: true,
  },
  auth: {
    type: Object,
  },
})

const partecipants = reactive([...game_session.partecipants || []])

const game_status = computed(() => {
  // TODO: This will be optained from the websocket connection
  return 'waiting-partecipants'
})

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
            class="px-8 py-8 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4"
          >
            <h1 class="relative mb-4 text-4xl font-extrabold leading-none tracking-tight text-gray-900 dark:text-white">
              {{ game_session.title }}

              <StatusBadge :status="game_status"></StatusBadge>
            </h1>

            <p class="mt-4 text-gray-500 dark:text-gray-400">
              {{ game_session.description }}
            </p>

            <p class="mt-4 text-gray-500 dark:text-gray-400">
              Max partecipants: {{ game_session.max_partecipants }}
            </p>
          </div>

          <div
            class="px-8 py-8 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4"
          >
            <h1 class="mb-4 text-4xl font-extrabold leading-none tracking-tight text-gray-900 dark:text-white">
              Questions
            </h1>

            <Alert type="error">
              Before starting the game let's wait for all the partecipants to join
            </Alert>

            <p class="mt-4 text-gray-500 dark:text-gray-400">
              Domande con i relativi controlli
            </p>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="px-8 py-8 text-gray-900 dark:text-gray-100">
            <h3 class="relative mb-3 text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white">
              Partecipants
              <span class="absolute -top-2 -end-2 bg-blue-100 text-blue-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">
                {{ partecipants.length }} / {{ game_session.max_partecipants }}
              </span>
            </h3>

            <div class="overflow-y-auto h-full">
              <ul>
                <li v-for="partecipant in partecipants">
                  {{ partecipant.name }}
                </li>
              </ul>
            </div>

            <p v-if="!partecipants.length">
              Nobody has joined the game yet
            </p>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>