<template>

  <div>
    <div class="vbe-imgfolder">

      <Upload
        :upload-url="upload_url"
        :mwfile-path="path"
        :button-text="buttonText"
      ></Upload>

      <div class="vbe-imglist">

        <div class="listheader">
          <span @click="toggleAll()"><input
              type="checkbox"
              class="taggable-toggle"
              name="taggable_toggle"
              :checked="isAllSelected"
            >&nbsp;alle/keine markieren</span>
        </div>

        <div class="item-list">
          <div
            class="item"
            :class="{'is-hidden':f.hidden,'is-checked':isChecked(f.id)}"
            @click="toggle(f.id)"
            v-for="(f, index) in files"
            :key="index"
          >
            <input
              type="checkbox"
              class="cb"
              value="MwFile-$ID"
              :checked="isChecked(f.id)"
            >
            <div class="imgholder">
              <div class='imgbox'>
                <div
                  class='img'
                  :style="{backgroundImage:`url(${f.thumbnail_url})`}"
                >
                </div>
              </div>
            </div>
            <div class="info">
              <div class="title">
                {{f.name}}
              </div>
            </div>

          </div>
        </div>

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
  </div>

</template>

<style lang="scss">
@import "../styles/settings.scss";

.vbe-imgfolder {
  // border: 1px solid red;
  .vbe-imglist {
    margin-top: 3rem;
    .listheader {
      // border: 1px solid #ccc;
      padding: 0.6rem;
      background: #eee;
    }

    .imgbox {
      height: 0;
      padding-bottom: (200/200) * 100%;
      position: relative;
      // border: 2px solid #ddd;
      // background-color: ##fff;

      > .img {
        display: block;
        position: absolute;
        top: 0px;
        left: 0px;
        right: 0px;
        bottom: 0px;
        // border: 1px solid red;
        background-position: center center;
        // background-size: cover;
        background-repeat: no-repeat;
      }
    }

    .item-list {
      display: flex;
      flex-wrap: wrap;
      .item {
        width: 200px;
        height: 200px;
        position: relative;
        &.is-hidden {
          .imgbox,
          .info {
            opacity: 0.356;
          }
        }
        &.is-checked {
          background: #eeeeff;
        }

        .cb {
          position: absolute;
          left: 0.5em;
          top: 0.5em;
        }
        border: 1px solid #ccc;
        border-radius: 3px;
        padding: 1rem;
        margin: 1rem;
      }
    }
  }
}
</style>




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

