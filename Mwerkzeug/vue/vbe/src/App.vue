<template>
  <div class="vbe">
    <router-view></router-view>
  </div>
</template>


<script lang="ts">
import Vue from "vue";

export default Vue.extend({
  name: "App",
  components: {},
  computed: {},
  mounted: function() {
    this.$router.push({ name: (this.$parent as VueExtensions).tagName });
  },
  methods: {
    go: function(path) {
      console.log("#logGO", path);
      this.$router.push(path);
    }
  }
});
</script>

<style lang="scss">
@import "styles/settings.scss";

.vbe {
  font-size: inherit;
}
</style>
