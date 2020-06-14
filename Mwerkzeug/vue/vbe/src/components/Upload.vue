<template>
  <div id="app">

    <vue-dropzone
      ref="myVueDropzone"
      id="dropzone"
      :options="dropzoneOptions"
    ></vue-dropzone>

  </div>
</template>

<script lang="ts">
import vue2Dropzone from "vue2-dropzone";
import "vue2-dropzone/dist/vue2Dropzone.min.css";

// const R = require("ramda");

import Vue from "vue";

export default Vue.extend({
  name: "Upload",
  components: {
    vueDropzone: vue2Dropzone
  },
  data() {
    const germanTexts = {
      dictDefaultMessage: `<button class='btn btn-primary'>${this.buttonText}</button>`,
      dictFallbackMessage:
        "der Foto-Upload funktioniert leider nicht mit deiner Browser-Version, Bitte verwende einen aktuelleren Browser.",
      dictFallbackText: "",
      dictFileTooBig:
        "Die Datei ist zu groß ({{filesize}}MiB). Maximale Dateigröße: {{maxFilesize}}MiB.",
      dictInvalidFileType:
        "Du kannst leider nur Bilder im JPG Format hochladen",
      dictResponseError: "Server reagiert mit {{statusCode}} code.",
      dictCancelUpload: "Upload abbrechen",
      dictCancelUploadConfirmation:
        "Bist Du sicher, dass Du diesen Upload abbrechen möchtest?",
      dictRemoveFile: "Bild löschen",
      dictMaxFilesExceeded: "Du kannst nur insgesamt 100 Bilder hochladen"
    };
    return {
      dropZoneIsActive: true,
      dropzoneOptions: {
        url: this.uploadUrl,
        thumbnailWidth: 160,
        maxFilesize: 10,
        headers: { "mwfile-path": this.mwfilePath },
        ...germanTexts
      }
    };
  },
  props: {
    uploadUrl: { type: String, required: true },
    mwfilePath: { type: String, required: true },
    buttonText: { type: String, required:true }
  },
  computed: {},
  methods: {
    onSuccess: function(file) {
      var foto = JSON.parse(file.xhr.responseText);
      file.localFilename = foto.file;
      this.$store.commit("addFoto", foto);
    },
    onRemovedFile: function(file) {
      if (this.dropZoneIsActive) {
        console.log("remove file", file);
        this.$store.commit("removeFoto", file.localFilename);
      }
    }
  },
  created: function() {
    this.dropZoneIsActive = true;
  },
  beforeDestroy: function() {
    this.dropZoneIsActive = false;
    console.log("before Destroy", null);
  }
});
</script>

<style lang='scss'>
.vue-dropzone {
  .fa-cloud-upload {
    font-size: 4em;
    display: block;
  }

  .dz-preview .dz-error-mark,
  .dz-preview .dz-success-mark {
    text-align: center;
  }
  .dz-preview .dz-error-mark i.fa {
    color: #e74c05 !important;
  }
  .dz-preview .dz-success-mark i.fa {
    color: #54af2e !important;
  }

  .dz-fallback {
    display: none;
  }
}
</style>
