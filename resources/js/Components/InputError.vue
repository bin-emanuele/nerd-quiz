<script setup lang="ts">
defineProps<{
    message?: string | string[];
}>();
</script>

<template>
    <div v-show="message">
        <p v-for="error in message" class="text-sm text-red-600 dark:text-red-400">
            {{ error }}
        </p>
    </div>
</template>
