<template>

  <div>
    <div class="vbe-imgfolder">

      <Upload
        :upload-url="upload_url"
        :mwfile-path="path"
        :button-text="buttonText"
      ></Upload>
    </div>
  </div>

</template>


<script lang="ts">
// const R = require("ramda");

import Vue from "vue";
import Upload from "./Upload.vue";

export default Vue.extend({
  name: "ImgFolder",
  data: function() {
    return {};
  },
  props: {
    path: { type: String, required: true },
    buttonText: { type: String, required: false, default: "Upload Files..." }
  },
  components: {
    Upload
  },
  computed: {
    upload_url: function() {
      return this.$store.getters.backendBaseUrl + "/MwFile/receiveDropzoneFile";
    },
    state: function() {
      return this.$store.state;
    }
  },
  methods: {},
  mounted: function() {}
});
</script>

<style lang="scss">
@import "../styles/settings.scss";
// .vbe-imgfolder {
//   // border: 1px solid red;
// }
</style>

