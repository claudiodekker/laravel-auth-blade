<script>
export default {
  props: {
    error: {
      type: [String, Array],
    },
  },
  computed: {
    message() {
      if (Array.isArray(this.error)) {
        return this.error[0]
      }

      return this.error
    },
  },
}
</script>

<template>
  <div v-if="message" class="mt-2">
    <p class="text-sm text-red-600">{{ message }}</p>
  </div>
</template>
