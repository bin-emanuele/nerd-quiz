<script setup>
import { Head, router } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'
import Alert from '@/Components/Alert.vue'
import StatusBadge from '@/Components/GameSession/StatusBadge.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { ref, reactive } from 'vue';
import axios from 'axios';
import { onMounted } from 'vue';

const { game_session, auth } = defineProps({
  auth: {
    type: Object,
  },
  game_session: {
    type: Object,
    required: true,
  },
});


// TODO: This will be optained from the websocket connection
const game_status = 'waiting-partecipants';
const partecipants = reactive([...game_session.partecipants || []]);

function getRandomAmazingName () {
  const names = ['Hulk', 'Spiderman', 'Ironman', 'Thor', 'Captain America', 'Black Widow', 'Hawkeye', 'Black Panther', 'Doctor Strange', 'Antman', 'Wasp', 'Scarlet Witch', 'Vision', 'Falcon', 'Winter Soldier', 'Star Lord', 'Gamora', 'Drax', 'Rocket', 'Groot', 'Mantis', 'Nebula', 'Captain Marvel', 'Valkyrie', 'Okoye', 'Shuri', 'Wong', 'Nick Fury', 'Maria Hill', 'Phil Coulson', 'Agent 13', 'Quicksilver', 'War Machine', 'Heimdall', 'Korg', 'Miek', 'The Collector', 'Eitri', 'Ned Leeds', 'Happy Hogan', 'Pepper Potts', 'Morgan Stark', 'Yondu', 'Kraglin', 'Nova Prime', 'Howard the Duck', 'The Grandmaster', 'M’Baku', 'Nakia', 'Ramonda', 'Zuri', 'Sharon Carter', 'Crossbones', 'Baron Zemo', 'Red Skull', 'The Ancient One', 'Mordo', 'Kaecilius', 'Dormammu', 'Ego', 'Ayesha', 'Taserface', 'The Vulture', 'Shocker', 'Scorpion', 'Mysterio', 'Hydro'];
  const adjectives = ['Angry', 'Amazing', 'Astonishing', 'Brave', 'Bold', 'Courageous', 'Clever', 'Daring', 'Determined', 'Energetic', 'Fearless', 'Friendly', 'Generous', 'Gentle', 'Helpful', 'Honest', 'Inventive', 'Jolly', 'Kind', 'Loyal', 'Modest', 'Noble', 'Optimistic', 'Polite', 'Quick', 'Quiet', 'Reliable', 'Responsible', 'Sincere', 'Sensible', 'Talented', 'Trustworthy', 'Unselfish', 'Valiant', 'Wise', 'Witty', 'Xenial', 'Youthful', 'Zealous'];

  return `${adjectives[Math.floor(Math.random() * adjectives.length)]} ${names[Math.floor(Math.random() * names.length)]}`;
}

const name = ref(getRandomAmazingName());
const partecipant = reactive({})
const joinModalErrors = ref({});
const joinModalShow = ref(!auth.user);

function onPartecipantJoin () {
  axios.post(`/game/${game_session.slug}/join`, { name: name.value })
    .then((response) => {
      joinModalShow.value = false;
      partecipant.value = response.data;

      connect();
    })
    .catch((error) => {
      console.log(error);
      joinModalErrors.value = error.response.data.errors;
    });
}

onMounted(() => {
  if (auth.user) {
    connect();
  }
});

function connect () {
  Echo.join(`game-session.${game_session.slug}`)
    .here((partecipants) => {
      console.log('Partecipants', partecipants);
      partecipants = partecipants;
    })
    .joining((partecipant) => {
      console.log('Partecipant joined', partecipant);

      if (partecipants.map(p => p.id).includes(partecipant.id)) {
        return;
      }

      partecipants.push(partecipant);
    })
    .leaving((partecipant) => {
      console.log('Partecipant left', partecipant);
    })
    .listen('*', (e) => {
      console.log(e);
    });
}

function leaveGame () {
  Echo.leave(`game-session.${game_session.slug}`);
  router.post(`/game/${game_session.slug}/leave`);
}
</script>

<template>
  <GuestLayout>
    <Head :title="`Game Session - ${game_session.title}`" />

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

            <div class="flex mt-3 justify-end">
              <button @click="leaveGame" class="text-white inline-flex items-center bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                Exit Game
              </button>
            </div>
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
              <span
                class="absolute -top-2 -end-2 bg-blue-100 text-blue-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300"
              >
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
          </div>
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
                required=""
              >
              <div
                v-if="joinModalErrors.name"
                class="text-red-500 text-sm mt-1"
              >{{ joinModalErrors.name.message }}</div>
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