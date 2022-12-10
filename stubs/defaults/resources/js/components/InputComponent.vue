<script>
export default {
  inheritAttrs: false,
  props: {
    modelValue: {
      type: String,
      default: '',
    },
  },
  methods: {
    focus() {
      return this.$refs.input.focus()
    },
    update(event) {
      this.$emit('update:modelValue', event.target.value)
    },
  },
}
</script>

<template>
  <label class="block font-medium text-sm text-gray-700">
    <span><slot /></span>
    <input ref="input" v-bind="$attrs" :value="modelValue" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full transition" @input="update" />
  </label>
</template>
