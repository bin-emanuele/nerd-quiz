<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'
import Alert from '@/Components/Alert.vue'
import StatusBadge from '@/Components/GameSession/StatusBadge.vue'
import GameSessionPartecipants from '@/Components/GameSession/Partecipants.vue';
import GameSessionQuestionList from '@/Components/GameSession/QuestionsList.vue';
import Countdown from '@/Components/GameSession/Countdown.vue';
import InputError from '@/Components/InputError.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { ref, reactive, computed, PropType } from 'vue';
import axios from 'axios';
import { onMounted } from 'vue'
import { GameSession } from '@/Models/GameSession';
import { Question } from '@/Models/Question';
import { Answer } from '@/Models/Answer';
import { Partecipant } from '@/Models/Partecipant';

const props = defineProps({
  auth: {
    type: Object as PropType<any>,
  },
  game_session: {
    type: Object as PropType<GameSession>,
    required: true,
  },
  winning_answers_count: {
    type: Number,
    required: true,
  },
  partecipant: {
    type: Object as PropType<Partecipant>,
  },
});

const game_session = reactive<GameSession>(new GameSession(props.game_session));
const game_status = computed(() => {
  return game_session.status;
});

function getRandomAmazingName () {
  const names = ['Hulk', 'Spiderman', 'Ironman', 'Thor', 'Captain America', 'Black Widow', 'Hawkeye', 'Black Panther', 'Doctor Strange', 'Antman', 'Wasp', 'Scarlet Witch', 'Vision', 'Falcon', 'Winter Soldier', 'Star Lord', 'Gamora', 'Drax', 'Rocket', 'Groot', 'Mantis', 'Nebula', 'Captain Marvel', 'Valkyrie', 'Okoye', 'Shuri', 'Wong', 'Nick Fury', 'Maria Hill', 'Phil Coulson', 'Agent 13', 'Quicksilver', 'War Machine', 'Heimdall', 'Korg', 'Miek', 'The Collector', 'Eitri', 'Ned Leeds', 'Happy Hogan', 'Pepper Potts', 'Morgan Stark', 'Yondu', 'Kraglin', 'Nova Prime', 'Howard the Duck', 'The Grandmaster', 'M’Baku', 'Nakia', 'Ramonda', 'Zuri', 'Sharon Carter', 'Crossbones', 'Baron Zemo', 'Red Skull', 'The Ancient One', 'Mordo', 'Kaecilius', 'Dormammu', 'Ego', 'Ayesha', 'Taserface', 'The Vulture', 'Shocker', 'Scorpion', 'Mysterio', 'Hydro'];
  const adjectives = ['Angry', 'Amazing', 'Astonishing', 'Brave', 'Bold', 'Courageous', 'Clever', 'Daring', 'Determined', 'Energetic', 'Fearless', 'Friendly', 'Generous', 'Gentle', 'Helpful', 'Honest', 'Inventive', 'Jolly', 'Kind', 'Loyal', 'Modest', 'Noble', 'Optimistic', 'Polite', 'Quick', 'Quiet', 'Reliable', 'Responsible', 'Sincere', 'Sensible', 'Talented', 'Trustworthy', 'Unselfish', 'Valiant', 'Wise', 'Witty', 'Xenial', 'Youthful', 'Zealous'];

  return `${adjectives[Math.floor(Math.random() * adjectives.length)]} ${names[Math.floor(Math.random() * names.length)]}`;
}

const name = ref(getRandomAmazingName());
const joinModalErrors = ref({});
const joinModalShow = ref(!props.auth.user);

const currentQuestion = computed<Question>(() => {
  return game_session.currentQuestion as Question;
});

const latestAnswer = ref<Answer | null>(null);
const answerResult = ref<Answer | null>(null);

function connect () {
  game_session.connect()
  .listen('GameSession\\CheckingAnswer', (data: { answer: Answer, game_session: GameSession }) => {
    console.log('Answer result', data.answer);
        latestAnswer.value = data.answer;
        answerResult.value = null;
      })
  .listen('GameSession\\AnswerResult', (data: { answer: Answer, game_session: GameSession }) => {
    console.log('Answer result', data.answer);
        latestAnswer.value = null;
        answerResult.value = data.answer;
        setTimeout(() => {
          answerResult.value = null;
        }, 10000);
      });
}

function onPartecipantJoin () {
  router.post(`/game/${game_session.slug}/join`, { name: name.value }, {
    onSuccess: (response) => {
      joinModalShow.value = false;
      connect();
    },
    onError: (errors) => {
      joinModalErrors.value = errors;
    },
  })
}

onMounted(() => {
  if (props.auth.user) {
    connect();
  }

  if (currentQuestion.value?.answers?.length) {
    const answers = currentQuestion.value.answers.sort((a: Answer, b: Answer) => {
      return (a.answered_at?.getTime() || 0) - (b.answered_at?.getTime() || 0)
    });
    latestAnswer.value = answers?.length ? answers[0] : null;
  }
});

function leaveGame () {
  Echo.leave(`game-session.${game_session.slug}`);
  router.post(`/game/${game_session.slug}/leave`);
}

function bookQuestion () {
  axios.post(route('game-sessions.front.book', [ game_session.slug, currentQuestion.value.id ]), {})
    .then(({ data }) => {
      console.log('Question booked', data);
    })
    .catch(({ response }) => {
      console.log('Error booking question', response.data);
      alert(response.data.message);
    });
}

const answerText = ref('');
const answerErrors = ref([]);
function answerSubmit() {
  axios.post(route('game-sessions.front.answer', [ game_session.slug, currentQuestion.value.id ]), { answer: answerText.value })
    .then(({ data }) => {
      console.log('Answer submitted', data);
      answerText.value = '';
    })
    .catch(({ response }) => {
      console.log('Error submitting answer', response.data);
      answerErrors.value = response.data.message;
    });
}

</script> 

<template>
  <GuestLayout>
    <Head :title="`Game Session - ${game_session.title}`"></Head>

    <div class="py-12 min-h-[80vh]">
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
          </div>

          <div
            v-if="['writing-question', 'waiting-booking', 'answer-booked', 'answer-check'].includes(game_status)"
            class="px-8 py-4 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4"
          >
            <h1 class="mb-4 text-4xl font-extrabold leading-none tracking-tight text-gray-900 dark:text-white">
              Current Question
            </h1>

            <div 
              v-if="['waiting-booking', 'answer-booked', 'answer-check'].includes(game_status)"
              class="flex justify-between items-center bg-indigo-500 rounded-lg px-6 py-4"
            >
              <div class="text-xl">
                {{ currentQuestion?.text }}
              </div>

              <div class="w-32 flex justify-end">
                <Countdown v-if="game_status == 'waiting-booking' && currentQuestion.expires_at" :ends_at="currentQuestion.expires_at" />
                <span v-if="game_status == 'answer-booked'" class="text">Booked!</span>
              </div>
            </div>

            <div v-if="game_status == 'answer-check'" class="my-4">
              <template v-if="!answerResult && latestAnswer">
                <p class="italic text-lg mb-3">> {{ latestAnswer.text }}</p>
                <Alert
                  v-if="latestAnswer?.partecipant?.id != partecipant?.id"
                  type="success"
                >
                  The host is checking the answer of {{ latestAnswer?.partecipant?.name }}. Please wait.
                </Alert>

                <Alert
                  v-if="latestAnswer?.partecipant?.id == partecipant?.id"
                  type="success"
                >
                  The host is checking your answer. Please wait.
                </Alert>
              </template>
            </div>

              
            <template v-if="!!answerResult">
              <Alert v-if="answerResult.is_correct" type="success">Congratulations {{ answerResult.partecipant.name }} the answer is correct!</Alert>
              <Alert v-else type="error">Wrong answer! {{ answerResult.partecipant.name }} you'll have better luck on the next question!</Alert>
            </template>

            <p
              v-if="['writing-question'].includes(game_status)"
              class="text-md dark:text-gray-400"
            >
              The host is writing a new question. Please wait.
            </p>

            <p
              v-if="['answer-booked'].includes(game_status) && currentQuestion.booked_by_id != partecipant?.id"
              class="text-md dark:text-gray-400"
            >
              {{ currentQuestion.booked_by?.name }} was faster an booked the question. Please wait for the answer!
            </p>

            <button
              class="text-white focus:ring-4 focus:outline-none focus:ring-blue-300 text-xl rounded-lg w-full py-4 text-center bg-blue-700 hover:bg-blue-800 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 my-4"
              :class="{'cursor-not-allowed bg-gray-500 hover:bg-gray-500 dark:bg-gray-500 dark:hover:bg-gray-500 dark:focus:ring-gray' : !['waiting-booking'].includes(game_status)}"
              :disabled="!['waiting-booking'].includes(game_status)"
              @click="bookQuestion"
            >
              Request to answer the question
            </button>

            <div
              v-if="['answer-booked'].includes(game_status) && currentQuestion.booked_by_id == partecipant?.id"
              class="px-4 py-4 dark:bg-blue-700 shadow-sm sm:rounded-lg mb-4"
            >
              <label
                for="answer"
                class="block text-gray-700 dark:text-white text-sm font-bold mb-2"
              >
                Write your answer
              </label>
              <textarea
                v-model="answerText"
                class="shadow appearance-none border rounded w-full py-2 px-3 dark:bg-gray-200 text-gray-900 leading-tight focus:outline-none focus:shadow-outline"
                placeholder="Answer"
                rows="3"
              ></textarea>
              <InputError class="mt-2" :message="answerErrors" />

              <button
                type="button"
                class="text-white inline-flex items-center bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-green-700 dark:hover:bg-green-800 dark:focus:ring-green-700"
                @click="answerSubmit"
              >
                Submit
              </button>
            </div>
          </div>

          <div
            v-if="game_session.closedQuestions?.length"
            class="px-8 py-8 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4"
          >
            <h1 class="mb-4 text-4xl font-extrabold leading-none tracking-tight text-gray-900 dark:text-white">
              Previous Questions
            </h1>

            <GameSessionQuestionList :game_session="game_session" :partecipant="partecipant" />
          </div>
        </div>

        <div class="flex flex-col">
          <GameSessionPartecipants
            :game_session="game_session"
            :winning_answers_count="winning_answers_count"
          />

          <button @click="leaveGame" class="text-white text-center bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800 my-3">
            Exit Game
          </button>
        </div>

      </div>
    </div>

    <Modal
      :show="joinModalShow"
      maxWidth="md"
      :closeable="false"
    >
      <!-- Modal content -->
      <div class="relative rounded-lg">
        <!-- Modal header -->
        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            What's your name?
          </h3>
        </div>

        <p class="px-5 mt-5 text-sm text-gray-900 dark:text-white mb-0">
          Choose a name to use to play the game then press "Any key" to start.
        </p>
        <form
          class="p-4 md:p-5 pt-0"
          @submit.prevent="onPartecipantJoin"
        >
          <div class="grid gap-4 mb-4 grid-cols-2">
            <div class="col-span-2">
              <input
                type="text"
                v-model="name"
                name="name"
                id="name"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                placeholder="Insert your nickname"
              >
              <div v-for="message in joinModalErrors.name" class="text-red-500 text-sm mt-1">{{ message }}</div>
            </div>
          </div>

          <div class="flex justify-end pt-2">
            <button
              type="submit"
              class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
            >
              Any key
            </button>
          </div>
        </form>
      </div>

    </Modal>
  </GuestLayout>
</template>