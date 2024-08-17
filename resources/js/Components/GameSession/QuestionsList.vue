<script setup lang="ts">
import { computed, reactive, PropType } from 'vue'
import { GameSession } from '../../Models/GameSession'
import { Partecipant } from '../../Models/Partecipant'
import Alert from '../../Components/Alert.vue'

const { game_session, partecipant } = defineProps({
  game_session: {
    type: Object as PropType<GameSession>,
    required: true,
  },
  partecipant: {
    type: Object as PropType<Partecipant>,
    required: false,
  },
})

const isAdmin = computed(() => {
  return !partecipant
})

</script>

<template>
  <Alert v-if="!isAdmin && !game_session.questions?.length">
    Waiting for the game to start...
  </Alert>

  <div class="flex flex-col">
    <div v-for="question in game_session.closedQuestions" class="flex flex-col w-full mb-4">
      <div class="flex items-center justify-between">
        <div class="flex flex-col items center">
          <div class="text-lg font-bold">{{ question.text }}</div>
          <div class="text-sm text-gray-500">{{ question.answers.length }} answers</div>
        </div>

        <div v-if="question.closed_at" class="text-sm text-gray-500">Closed at: {{ question.closed_at.toLocaleString() }}</div>
      </div>

      <div v-if="question.answers?.length > 0" class="flex flex-col pl-4">
        <p class="text-sm -mb-4 mt-4">Answers:</p>
        <div v-for="answer in question.answers" class="flex flex items-center mt-4">
          <div class="mr-4">
            <span v-if="answer.is_correct" class="text-mono px-2 py-1 text-white bg-green-500 block w-8 h-8 text-center rounded-full">v</span>
            <span v-else class="text-mono px-2 py-1 text-white bg-red-500 block w-8 h-8 text-center rounded-full">x</span>
          </div>

          <div class="flex flex-col w-full">
            <div class="text-md">{{ answer.text }}</div>
            <div class="text-sm text-gray-500">by {{ answer.partecipant?.name }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>