<template>
  <div class="eb-backend">
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

.eb-backend {
  .ti-tag,
  .ti-item {
    color: black !important;
    &.ti-selected-item {
      border: 1px solid black;
    }
  }
  .ti-item {
    border: 1px solid transparent;
  }
}
</style>
