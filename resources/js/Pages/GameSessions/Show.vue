<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Alert from '@/Components/Alert.vue'
import StatusBadge from '@/Components/GameSession/StatusBadge.vue'
import GameSessionPartecipants from '@/Components/GameSession/Partecipants.vue'
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
  winning_answers_count: {
    type: Number,
    required: true,
  },
})

const partecipants = reactive([...game_session.partecipants || []])
const online_partecipants = reactive([])

const game_status = computed(() => {
  // TODO: This will be optained from the websocket connection
  return 'waiting-partecipants'
})

connect()

function connect () {
  Echo.join(`game-session.${game_session.slug}`)
    .here((partecipants) => {
      console.log('Partecipants here', partecipants);
      partecipants
        .filter(x => x.type === 'partecipant')
        .forEach((partecipant) => {
          if (!game_session.partecipants.some((p) => p.id === partecipant.id)) {
            game_session.partecipants.push(partecipant);
          }
        });
      online_partecipants.splice(0, online_partecipants.length, ...partecipants.map((p) => p.id));
    })
    .joining((partecipant) => {
      console.log('Partecipant joined', partecipant);
      if (partecipant.type !== 'partecipant') {
        return;
      }

      online_partecipants.push(partecipant.id);

      if (game_session.partecipants.map(x => x.id).includes(partecipant.id)) {
        return;
      }

      game_session.partecipants.push(partecipant);
    })
    .leaving((partecipant) => {
      if (partecipant.type !== 'partecipant') {
        return;
      }

      console.log('Partecipant left', partecipant);
      online_partecipants.splice(online_partecipants.indexOf(partecipant.id), 1);
    })
    .listen('*', (e) => {
      console.log(e);
    });
}

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

        <GameSessionPartecipants 
          :game_session="game_session"
          :online_partecipants="online_partecipants"
          :winning_answers_count="winning_answers_count"
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>