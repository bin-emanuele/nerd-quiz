<template>
  <div class="absolute inline-flex -top-2 -end-2">
    <span
      v-if="status === 'waiting-partecipants'"
      class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300"
    >
      Waiting Partecipants
    </span>
    <span
      v-if="status === 'writing-question'"
      class="bg-gray-100 text-gray-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300"
    >
      Writing Question
    </span>
    <span
      v-if="status === 'waiting-booking'"
      class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300"
    >
      Waiting Booking
    </span>
    <span
      v-if="status === 'answer-booked'"
      class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300"
    >
      Answer Booked
    </span>
    <span
      v-if="status === 'answer-verification'"
      class="bg-yellow-100 text-yellow-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300"
    >
      Answer Verification
    </span>
    <span
      v-if="status === 'game-over'"
      class="bg-indigo-100 text-indigo-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-indigo-900 dark:text-indigo-300"
    >
      Game Over
    </span>
  </div>
</template>

<script setup>
defineProps({
  status: {
    type: String,
    default: 'waiting-partecipants',
  },
});
</script>
