<template>
  <div class="vbe">
    <router-view></router-view>
  </div>
</template>


<script>
export default {
  name: "App",
  components: {},
  methods: {},

  computed: {},

  mounted: function() {
    this.$router.push({ name: this.$parent.tagName });
  },
  methods: {
    go: function(path) {
      console.log("#logGO", path);
      this.$router.push(path);
    }
  }
};
</script>

<style lang="scss">
@import "styles/settings.scss";

.vbe {
  font-size: inherit;
}
</style>
