<script
  setup
  lang="ts"
>
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'

defineProps<{
  canLogin?: boolean;
  canRegister?: boolean;
  game_sessions: Array<{ slug: string, title: string; description: string; max_partecipants: number; }>;
}>();

function gotoSession (slug: string) {
  router.get(`/game/${slug}`)
}
</script>

<template>
  <Head title="Welcome" />
  <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
    <img
      id="background"
      class="absolute -left-20 top-0 max-w-[877px]"
      src="https://laravel.com/assets/img/welcome/background.svg"
    />
    <div
      class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white"
    >
      <div class="flex-col grow relative h-full w-full max-w-2xl px-6 lg:max-w-7xl">
        <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
          <nav
            v-if="canLogin"
            class="-mx-3 flex flex-1 justify-end lg:col-start-3"
          >
            <Link
              v-if="$page.props.auth.user"
              :href="route('dashboard')"
              class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
            >
            Dashboard
            </Link>

            <template v-else>
              <Link
                :href="route('login')"
                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
              >
              Log in
              </Link>

              <Link
                v-if="canRegister"
                :href="route('register')"
                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
              >
              Register
              </Link>
            </template>
          </nav>
        </header>

        <main class="mt-6 grow">
          <div>
            <h1 class="text-4xl font-extrabold leading-none tracking-tight text-gray-900 dark:text-white">
              Welcome to Nerd Quiz Game Show!
            </h1>
            <h2 class="text-lg">
              The best quiz game show on the web
            </h2>
          </div>

          <div class="mt-10">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Game Sessions</h2>
            <p>Select your next adventure</p>
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-2 mt-3">
            <div
              v-for="game in game_sessions"
              :key="game.slug"
              class="bg-white dark:bg-gray-300 dark:bg-opacity-30 overflow-hidden sm:rounded-lg cursor-pointer shadow-md"
            >
              <div
                class="px-4 py-5 sm:px-6 cursor-pointer"
                @click="gotoSession(game.slug)"
              >
                <h3 class="text-lg font-semibold text-gray-500 dark:text-gray-200 leading-tight">{{ game.title }}</h3>
                <p class="text-sm text-gray-300 dark:text-gray-100">{{ game.description || '&nbsp;' }}</p>
                <p class="text-sm text-gray-300 dark:text-gray-100">Max partecipants: {{ game.max_partecipants }}</p>
              </div>
            </div>
          </div>
        </main>

        <footer></footer>
      </div>
    </div>
  </div>
</template>
