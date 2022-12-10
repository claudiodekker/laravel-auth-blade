<script>
const flavors = {
  primary: 'inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-sm font-semibold leading-4 text-white shadow-sm hover:bg-gray-700 focus:outline-none active:bg-gray-900 focus:border-gray-900 focus:ring-2 focus:ring-gray-300 disabled:opacity-25 transition',
  secondary: 'inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold leading-4 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none active:bg-gray-100 focus:ring-2 focus:ring-gray-300 disabled:opacity-25 transition',
  danger: 'inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-600 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 disabled:opacity-25 transition',
}

export default {
  props: {
    flavor: {
      type: String,
      default: 'primary',
    },
  },
  computed: {
    classes() {
      return flavors[this.flavor]
    },
  },
}
</script>

<template>
  <button :class="classes">
    <slot />
  </button>
</template>
