<template>
  <div id="app">
    <CustomizerChooser :settings="settings" />
  </div>
</template>

<script>
import CustomizerChooser from "./components/CustomizerChooser.vue";

console.log("#logwindow", window);
export default {
  name: "app",
  components: {
    CustomizerChooser
  },
  data: function() {
    return {
      settings: window.vueAppConf
    };
  }
};
</script>

<style lang="scss">
#app {
}
</style>
