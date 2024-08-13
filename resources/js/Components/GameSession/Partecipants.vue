<script setup lang="ts">
import { computed, reactive, PropType } from 'vue'
import { GameSession } from '../../Models/GameSession';

const { game_session, winning_answers_count } = defineProps({
  game_session: {
    type: Object as PropType<GameSession>,
    required: true,
  },
  winning_answers_count: {
    type: Number,
    required: true,
  },
});


const sortedPartecipants = computed(() => {
  return [...game_session.partecipants].sort((a, b) => {
    if (isConnected.value(a) && !isConnected.value(b)) {
      return -1;
    }

    if (!isConnected.value(a) && isConnected.value(b)) {
      return 1;
    }

    if (b.answers_available !== a.answers_available) {
      return b.answers_available - a.answers_available;
    }

    return b.answers_correct - a.answers_correct;
  }) || [];
});

const isConnected = computed(() => (partecipant) => {
  return game_session.online_partecipants.includes(partecipant.id);
});

const maxAnswersAvailable = computed(() => {
  return Math.max(...game_session.partecipants.map(p => p.answers_available));
});

</script>

<template>
  <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="px-8 py-8 text-gray-900 dark:text-gray-100">
      <h3 class="relative mb-3 text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white">
        Partecipants
        <span class="absolute -top-2 -end-2 bg-blue-100 text-blue-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">
          {{ game_session.partecipants?.length || 0 }} / {{ game_session.max_partecipants }}
        </span>
      </h3>

      <div class="overflow-y-auto h-full">
        <ul>
          <li v-for="partecipant in sortedPartecipants" class="flex justify-between">
            <div>
              <span v-if="isConnected(partecipant)" class="inline-block bg-green-500 text-white p-1 rounded-md"></span>
              <span v-else class="inline-block bg-red-500 text-white p-1 rounded-md"></span>
              <span class="inline-block mx-2">{{ partecipant.name }}</span>
            </div>

            <div class="block font-mono">
              <span v-for="n in (partecipant.answers_correct || 0)">v</span>
              <span v-for="n in Math.max(0, (winning_answers_count - partecipant.answers_correct) || 0)">o</span>

              | 

              <span v-for="n in (partecipant.answers_available || 0)">o</span>
              <span v-for="n in Math.max(0, (maxAnswersAvailable - partecipant.answers_available) || 0)">x</span>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>