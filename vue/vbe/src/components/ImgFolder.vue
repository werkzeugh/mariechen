<template>

  <div>
    <div class="vbe-imgfolder">

      <Upload
        :upload-url="upload_url"
        :mwfile-path="path"
        :button-text="buttonText"
      ></Upload>

      <div class="listheader">
        <span @click="toggleAll()"><input
            type="checkbox"
            class="taggable-toggle"
            name="taggable_toggle"
            :checked="isAllSelected"
          >alle/keine markieren</span>
      </div>

      <table class="table table-bordered table-striped taggable-items vbe-imglist">
        <thead>
          <tr>

            <th>Img</th>
            <th>Title</th>
            <th></th>
          </tr>
        </thead>
        <tbody>

          <tr
            class="js-sortable-tr"
            :class="{'is-hidden':f.hidden}"
            v-for="f in files"
            :key="f.id"
          >
            <td
              class="taggable-cb-td"
              @click="toggle(f.id)"
            >
              <input
                type="checkbox"
                class="taggable-cb"
                value="MwFile-$ID"
                :checked="isChecked(f.id)"
              >
            </td>
            <td>
              <img
                :src="f.thumbnail_url"
                @click="toggle(f.id)"
              >
            </td>
            <td>
              {{f.name}}
            </td>

          </tr>
        </tbody>
      </table>

      <div v-if="isMinimumOneSelected">
        <button
          class="btn "
          type="button"
          @click="removeSelected()"
        ><i class="fa fa-trash-o"></i> alle markierten Bilder löschen</button>

        <button
          class="btn "
          type="button"
          @click="hideSelected()"
        ><i class="fa fa-trash-o"></i> alle markierten Bilder sperren</button>
        <button
          class="btn "
          type="button"
          @click="unhideSelected()"
        ><i class="fa fa-trash-o"></i> alle markierten Bilder entsperren</button>

      </div>
    </div>
  </div>

</template>


<script lang="ts">
const R = require("ramda");

import { handleErrorsFromResponse } from "../utils";

import Vue from "vue";
import Upload from "./Upload.vue";

export default Vue.extend<any, any, any, any>({
  name: "ImgFolder",
  data: function() {
    return {
      waiting: 0,
      checkedFileIds: [] as number[]
    };
  },
  props: {
    path: { type: String, required: true },
    buttonText: { type: String, required: false, default: "Upload Files..." }
  },
  components: {
    Upload
  },
  methods: {
    isChecked: function(id) {
      return R.includes(id, this.checkedFileIds);
    },
    toggle: function(id) {
      if (this.isChecked(id)) {
        this.checkedFileIds = R.without([id], this.checkedFileIds);
      } else {
        this.checkedFileIds.push(id);
      }
    },
    toggleAll: function(id) {
      if (this.checkedFileIds.length > 0) {
        this.checkedFileIds = [];
      } else {
        this.checkedFileIds = R.map(f => f.id, this.files);
      }
    },
    fetchData: function(): void {
      this.waiting++;
      this.$store
        .dispatch("imgfolder/allFiles", { path: this.path })
        .then(res => {
          this.waiting--;
          // this.fetchData();
        })
        .catch(res => {
          console.error(res);
          handleErrorsFromResponse(this, res);
          this.waiting--;
        });
    },
    removeSelected: function() {
      if (confirm("Sind sie sicher ?")) {
        this.waiting++;
        this.$store
          .dispatch("imgfolder/removeFiles", {
            path: this.path,
            file_ids: this.checkedFileIds
          })
          .then(res => {
            this.waiting--;
            this.fetchData();
          })
          .catch(res => {
            console.error(res);
            handleErrorsFromResponse(this, res);
            this.waiting--;
          });
      }
    },
    hideSelected: function() {
      this.waiting++;
      this.$store
        .dispatch("imgfolder/hideFiles", {
          path: this.path,
          file_ids: this.checkedFileIds
        })
        .then(res => {
          this.waiting--;
          this.fetchData();
        })
        .catch(res => {
          console.error(res);
          handleErrorsFromResponse(this, res);
          this.waiting--;
        });
    },
    unhideSelected: function() {
      this.waiting++;
      this.$store
        .dispatch("imgfolder/unhideFiles", {
          path: this.path,
          file_ids: this.checkedFileIds
        })
        .then(res => {
          this.waiting--;
          this.fetchData();
        })
        .catch(res => {
          console.error(res);
          handleErrorsFromResponse(this, res);
          this.waiting--;
        });
    }
  },
  computed: {
    isAllSelected: function() {
      return this.checkedFileIds.length == this.files.length;
    },
    isMinimumOneSelected: function() {
      return this.checkedFileIds.length > 0;
    },
    files: function(): object[] {
      return this.$store.getters["imgfolder/allFiles"];
    },
    upload_url: function(): string {
      return this.$store.getters.backendBaseUrl + "/MwFile/receiveDropzoneFile";
    }
  },
  mounted: function() {
    this.fetchData();
  }
});
</script>

<style lang="scss">
@import "../styles/settings.scss";

.vbe-imgfolder {
  // border: 1px solid red;
  .vbe-imglist {
    margin-top: 3rem;
    .is-hidden {
      opacity: 0.356;
    }
  }
}
</style>

