<template>

  <div>
    <div class="vbe-imgfolder">

      IMGFOLDER hutn
    </div>
  </div>

</template>


<script lang="ts">
// const R = require("ramda");

import Vue from "vue";

export default Vue.extend({
  name: "ImgFolder",
  data: function() {
    return {
      win_h: 0,
      win_w: 0
    };
  },
  computed: {},
  methods: {},
  mounted: function() {}
});
</script>

<style lang="scss">
@import "../styles/settings.scss";
.vbe-imgfolder {
  border: 1px solid red;
}
</style>

