<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watchEffect } from 'vue'

const { ends_at } = defineProps({
  ends_at: {
    type: Date,
    required: true,
  },
});

const now = ref(new Date());
let interval: number | null = null;

const countdown = computed(() => {
  const diff = Math.max(Math.floor((ends_at.getTime() - now.value.getTime()) / 1000), 0);

  if (diff === 0) {
    return 'Expired';
  }

  return `${String(diff).padStart(2, '0')}s`;
});

onMounted(() => {
  if (ends_at <= new Date()) {
    console.log('Date already passed', ends_at.getTime(), new Date().getTime());
    return;
  }

  interval = setInterval(() => {
    now.value = new Date();
  }, 1000);
});

onUnmounted(() => {
  if (interval != null) {
    clearInterval(interval);
  }
});

watchEffect(() => {
  if (ends_at > new Date()) {
    return;
  }

  if (interval != null) {
    clearInterval(interval);
  }
});
</script>

<template>
  <div class="text-3xl font-bold text-center">
    {{ countdown }}
  </div>
</template>
