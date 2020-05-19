<template>
  <div>

    <div class="thumbnail-list">
      <img
        :src="img.smalllink"
        v-for="(img, index) in $store.getters.images"
        :key="index"
        :ref="index"
        @click="scrollTo(index)"
        :class="{'active':index==$store.getters.activeImageIndex}"
      >
    </div>
  </div>
</template>

<script>
const R = require("ramda");

var splitPlus = function(str, sep) {
  var a = str.split(sep);
  if (a[0] == "" && a.length == 1) return [];
  return a;
};
export default {
  name: "ThumbnailList",
  data() {
    return {};
  },
  props: {},
  components: {},
  methods: {
    scrollTo: function(index) {
      const e = document.getElementById("img_" + index);
      e.scrollIntoView({ behavior: "smooth", block: "center" });
    }
  },
  computed: {},
  created: function() {}
};
</script>
<style lang="scss">
@import "../styles/settings.scss";
</style>
