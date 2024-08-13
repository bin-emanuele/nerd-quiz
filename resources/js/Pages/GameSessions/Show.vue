<script setup>

import { computed, reactive, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Alert from '@/Components/Alert.vue'
import StatusBadge from '@/Components/GameSession/StatusBadge.vue'
import GameSessionPartecipants from '@/Components/GameSession/Partecipants.vue'
import GameSessionQuestionList from '@/Components/GameSession/QuestionsList.vue'
import Countdown from '@/Components/GameSession/Countdown.vue'
import InputError from '@/Components/InputError.vue'
import { Head, useForm } from '@inertiajs/vue3'

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

const currentQuestion = computed(() => {
  const unansweredQuestions = game_session.questions.filter((q) => !q.answered_at).sort((a, b) => a.created_at - b.created_at);
  if (!unansweredQuestions.length) {
    return null;
  }

  return unansweredQuestions[0];
});
const latestAnswer = computed(() => {
  const answers = currentQuestion.value?.answers?.sort((a, b) => a.answered_at - b.answered_at);
  return answers?.length ? answers[0] : null;
});

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
    .listen('GameSession\\WritingQuestion', (data) => {
      console.log('Writing question', data.game_session.status);
      game_session.status = data.game_session.status;
      questionForm.show = true;
    })
    .listen('GameSession\\NextQuestion', (data) => {
      console.log('New question', data.question);
      if (!game_session.questions) {
        game_session.questions = [];
      }
      game_session.questions.push(data.question);
      game_session.status = data.game_session.status;
    })
    .listen('GameSession\\BookedQuestion', (data) => {
      console.log('Booked question', data.question);
      game_session.questions.splice(game_session.questions.findIndex((q) => q.id === data.question.id), 1, data.question);
      game_session.status = data.game_session.status;
    })
    .listen('GameSession\\CheckingAnswer', (data) => {
      console.log('Checking answer', data.answer);
      console.log(currentQuestion.value);
      if (!currentQuestion.value.answers) {
        currentQuestion.value.answers = [];
      }
      currentQuestion.value.answers.push(data.answer);
      game_session.status = data.answer.question.game_session.status;
    })
    .listenToAll((e, data) => {
      console.log(e, data);
    });
}


function leaveGame () {
  Echo.leave(`game-session.${game_session.slug}`);
  router.post(`/game/${game_session.slug}/leave`);
}

function gotoSessions () {
  router.get(`/game-sessions`)
}


const questionForm = useForm({
  show: false,
  text: '',
})
function nextQuestion () {
  axios.post(route('game-sessions.writing-question', game_session.id), {})
    .then(({ data }) => {
      console.log('nextQuestion', data.status)
      game_session.status = data.status
      questionForm.show = true
      questionForm.text = ''
    })
    .catch((error) => {
      console.error('Error', error)
      alert(error.response.message)
    })
}
const canWriteNextQuestion = computed(() => {
  return game_session.status === 'waiting-partecipants' && !questionForm.show
})
function questionSubmit () {
  questionForm.show = false
  axios.post(route('game-sessions.next-question', game_session.id), {
    text: questionForm.text
  })
    .then(({ data }) => {
      console.log('questionSubmit', data)
    })
    .catch((error) => {
      console.error('Error', error)
      alert(error.response)
    })
}

function confirmAnswer (isCorrect) {
  axios.post(route('game-sessions.confirm-answer', [game_session.id, latestAnswer.value.id]), {
    is_correct: isCorrect,
    answer_id: latestAnswer.value.id,
  })
    .then(({ data }) => {
      console.log('confirmAnswer', data)
    })
    .catch((error) => {
      console.error('Error', error)
      alert(error.response)
    })
}

function resetGame () {
  axios.post(route('game-sessions.reset', game_session.id), {})
    .then(({ data }) => {
      console.log('resetGame', data)
      router.get(`/game-sessions/${game_session.id}`)
    })
    .catch((error) => {
      console.error('Error', error.message)
      alert(error.response)
    })
}

onMounted(() => {
  questionForm.show = (game_session.status === 'writing-question')
  connect()
});

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

              <StatusBadge :status="game_session.status"></StatusBadge>
            </h1>

            <p class="mt-4 text-gray-500 dark:text-gray-400">
              {{ game_session.description }}
            </p>

            <p class="mt-4 text-gray-500 dark:text-gray-400">
              Max partecipants: {{ game_session.max_partecipants }}
            </p>

            <button
              class="mt-4 px-4 py-2 bg-gray-700 text-white rounded-md"
              type="button"
              @click="resetGame"
            >
              Reset
            </button>
          </div>

          <div
            class="px-8 py-8 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-4">
            <h1 class="mb-4 text-4xl font-extrabold leading-none tracking-tight text-gray-900 dark:text-white">
              Current Question
            </h1>

            <div
              v-if="currentQuestion"
              class="flex items-center justify-between"
            >
              <div class="flex flex-col items center">
                <div class="text-lg font-bold">{{ currentQuestion.text }}</div>
                <div class="text-sm text-gray-500">{{ currentQuestion.answers?.length || 0 }} answers</div>
              </div>

              <div
                v-if="!currentQuestion.answered_at"
                class="flex items-center"
              >
                <Countdown
                  v-if="game_session.status == 'waiting-booking'"
                  :ends_at="currentQuestion.expires_at"
                />
                <span
                  v-if="game_session.status == 'answer-booked'"
                  class="text"
                >Booked!</span>
              </div>
              <div
                v-else
                class="text-sm text-gray-500"
              >Correct answer: {{ currentQuestion.answered_at }}</div>
            </div>

            <div
              v-if="game_session.status == 'answer-booked'"
              class="my-4"
            >
              <Alert type="info">
                {{ currentQuestion.booked_by?.name }} has booked the question! Waiting for the partecipant to submit the
                answer.
              </Alert>
            </div>

            <div
              v-if="game_session.status == 'answer-check'"
              class="my-4 p-4 dark:text-gray-800 dark:bg-blue-300 shadow-sm sm:rounded-lg"
            >
              <Alert type="dark">Answer to check</Alert>
              <div class="mt-4">
                <p class="text-lg italic">
                  > {{ latestAnswer?.text }}
                </p>
                <p class="text-gray-700">
                  <span>Answer by:</span><span class="font-bold ml-2">{{ latestAnswer?.partecipant?.name }}</span>
                </p>

                <div class="flex justify-center mt-4">
                  <button
                    class="px-8 py-2 bg-green-700 text-white rounded-md mr-12"
                    @click="confirmAnswer(true)"
                  >Correct!</button>
                  <button
                    class="px-8 py-2 bg-red-700 text-white rounded-md"
                    @click="confirmAnswer(false)"
                  >Wrong!</button>
                </div>
              </div>
            </div>

            <button
              class="px-4 py-2 bg-blue-700 text-white rounded-md"
              v-if="canWriteNextQuestion"
              @click="nextQuestion"
            >
              Next Question
            </button>

            <div
              v-if="questionForm.show"
              class="px-4 py-4 dark:bg-blue-700 shadow-sm sm:rounded-lg mb-6"
            >
              <form @submit.prevent="questionSubmit">
                <label
                  for="question"
                  class="block text-gray-700 dark:text-white text-sm font-bold mb-2"
                >Question</label>
                <textarea
                  v-model="questionForm.text"
                  id="question"
                  name="question"
                  class="shadow appearance-none border rounded w-full py-2 px-3 dark:bg-gray-200 text-gray-900 leading-tight focus:outline-none focus:shadow-outline"
                  placeholder="Question"
                  rows="3"
                ></textarea>
                <InputError
                  class="mt-2"
                  :message="questionForm.errors?.question"
                />

                <button
                  class="mt-4 px-4 py-2 bg-green-700 text-white rounded-md"
                  type="submit"
                >
                  Submit
                </button>
              </form>
            </div>
          </div>

          <div
            v-if="game_session.questions.length > 1"
            class="px-8 py-8 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4"
          >
            <h1 class="mb-4 text-4xl font-extrabold leading-none tracking-tight text-gray-900 dark:text-white">
              Previous Questions
            </h1>
            <GameSessionQuestionList :game_session="game_session" />
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