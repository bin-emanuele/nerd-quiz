<template>
  <div
    v-if="type === 'info'"
    class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-blue-900 dark:text-blue-100"
    role="alert"
  >
    <slot></slot>
  </div>

  <div
    v-if="type === 'error'"
    class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-red-900 dark:text-red-100"
    role="alert"
  >
    <slot></slot>
  </div>

  <div
    v-if="type === 'success'"
    class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-green-900 dark:text-green-100"
    role="alert"
  >
    <slot></slot>
  </div>

  <div
    v-if="type === 'warning'"
    class="p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-yellow-900 dark:text-yellow-100"
    role="alert"
  >
    <slot></slot>
  </div>

  <div
    v-if="type === 'dark'"
    class="p-4 text-sm text-gray-800 rounded-lg bg-gray-50 dark:bg-gray-900 dark:text-gray-100"
    role="alert"
  >
    <slot></slot>
  </div>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: 'info',
  },
});
</script>