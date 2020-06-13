<template>

  <div>
    <div class="vbe-imgfolder">

      IMGFOLDER whats going on !
    </div>
  </div>

</template>


<script>
// const R = require("ramda");

export default {
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
};
</script>

<style lang="scss">
@import "../styles/settings.scss";
.vbe-imgfolder {
  border: 1px solid red;
}
</style>

