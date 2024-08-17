<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'

import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

defineProps({
  game_sessions: {
    type: Array,
    required: true,
  },
})

function gotoSession (id) {
  router.get(`/game-sessions/${id}`)
}
</script>

<template>

  <Head title="Game Sessions" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Game Sessions</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div
            v-if="game_sessions.length"
            class="px-12 py-8 text-gray-900 dark:text-gray-100 w-full"
          >
            <h1
              class="mb-4 text-4xl font-extrabold leading-none tracking-tight text-gray-900 md:text-3xl lg:text-4xl dark:text-white"
            >Game Sessions List</h1>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-2">
              <div
                v-for="game in game_sessions"
                :key="game.id"
                class="bg-white dark:bg-gray-700 overflow-hidden shadow-sm sm:rounded-lg"
              >
                <div
                  class="px-4 py-5 sm:px-6 cursor-pointer"
                  @click="gotoSession(game.id)"
                >
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-200 leading-tight">{{ game.title }}</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-300">{{ game.description || '&nbsp;' }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-300">Max partecipants: {{ game.max_partecipants }}</p>
                </div>
              </div>
            </div>
          </div>

          <h1
            v-else
            class="px-12 py-8 text-4xl font-extrabold leading-none tracking-tight text-gray-900 md:text-3xl lg:text-4xl dark:text-white"
          >There are no active games</h1>

          <div class="px-12 mb-8 mt-4">
            <button
              class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
              type="button"
              @click="router.get('/game-sessions/create')"
            >Create Game</button>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>