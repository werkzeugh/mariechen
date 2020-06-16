<template>

  <div>
    <div class="vbe-imgfolder">

      <div v-if="mode=='sort'">

        <div>
          <slick-list
            axis="xy"
            v-model="sortedfiles"
            class="sort-list"
            helperClass="sort-item"
          >
            <slick-item
              v-for="(item, index) in sortedfiles"
              :index="index"
              :key="index"
              class="sort-item"
            >
              <div class="imgholder">
                <div class='imgbox'>
                  <div
                    class='img'
                    :style="{backgroundImage:`url(${item.thumbnail_url})`}"
                  >
                  </div>
                </div>
              </div>
              <div class="info">
                <div class="title">
                  {{item.name}}
                </div>
              </div>
            </slick-item>
          </slick-list>
        </div>

        <div class="sort-buttons">
          <div class="sb-label">
            Sortierung
          </div>
          <button
            class="btn btn-primary"
            type="button"
            @click="saveSort()"
          ><i class="fa fa-check"></i> speichern</button>
          <button
            class="btn "
            type="button"
            @click="mode='list'"
          ><i class="fa fa-times"></i> abbrechen</button>
        </div>
      </div>
      <div v-else>
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
            <button
              class="btn "
              type="button"
              @click="mode='sort'"
            ><i class="fa fa-chevron-right"></i> Bilder sortieren</button>
          </div>

          <div class="item-list">
            <div
              class="item"
              :class="{'is-hidden':f.hidden,'is-checked':isChecked(f.id)}"
              @click.prevent="toggle(f.id)"
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
                  <a
                    class='img'
                    :href="f.url"
                    :style="{backgroundImage:`url(${f.thumbnail_url})`}"
                  >
                  </a>
                </div>
              </div>
              <div class="info">
                <div class="title">
                  {{f.name}}
                </div>
              </div>

            </div>
          </div>

          <div
            v-if="isMinimumOneSelected"
            class="list-buttons"
          >
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
  </div>

</template>

<style lang="scss">
@import "../styles/settings.scss";

.sort-item {
  display: flex;
  align-items: center;
  height: 4rem;
  background: white;
  margin: 1px;
  &:first-child {
    margin-top: 0;
  }
  &:last-child {
    margin-bottom: 0;
  }
  .info {
    flex: 1 1 auto;
  }
  .imgholder {
    flex: 0 0 4rem;
    width: 2rem;
    @include mx(1rem);
    padding: 4px;
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
        background-position: 20%;
        background-size: contain;
        background-repeat: no-repeat;
      }
    }
  }
}
.vbe-imgfolder {
  // border: 1px solid red;
  .sort-list {
    margin: 3rem;
    max-width: 300px;
    margin-left: auto;
    margin-right: auto;
    background: #ccc;
    overflow-y: auto;
    max-height: calc(100vh - 200px);
    border-top: 1px solid #ccc;
    border-bottom: 1px solid #ccc;
  }
  .sort-buttons {
    @include mx(auto);
    max-width: 360px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    .sb-label {
      // font-weight: bold;
      font-size: 1.5em;
      text-align: right;
    }
  }

  .item {
    .imgholder {
      padding: 0.9rem;
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
        background-size: contain;
        background-repeat: no-repeat;
      }
    }

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
  .list-buttons {
    display: flex;
    justify-content: flex-start;
    > * {
      margin-right: 1em;
    }
  }
  .vbe-imglist {
    margin-top: 3rem;
    .listheader {
      // border: 1px solid #ccc;
      padding: 0.6rem;
      background: #eee;
      display: flex;
      justify-content: space-between;
    }

    .item-list {
      display: flex;
      flex-wrap: wrap;
    }
  }
}
</style>




<script lang="ts">
const R = require("ramda");

import { handleErrorsFromResponse } from "../utils";

import { SlickList, SlickItem } from "vue-slicksort";

// import { ContainerMixin, ElementMixin } from "vue-slicksort";

import Vue from "vue";
import Upload from "./Upload.vue";

export default Vue.extend<any, any, any, any>({
  name: "ImgFolder",
  data: function() {
    return {
      waiting: 0,
      mode: "list",
      checkedFileIds: [] as number[],
      sortedfiles: [] as object[]
    };
  },
  props: {
    path: { type: String, required: true },
    buttonText: { type: String, required: false, default: "Upload Files..." }
  },
  components: {
    Upload,
    "slick-item": SlickItem,
    "slick-list": SlickList
  },
  methods: {
    saveSort: function() {
      const sorted_ids = R.map(f => f.id, this.sortedfiles);

      this.waiting++;
      this.$store
        .dispatch("imgfolder/saveSort", { sorted_ids, path: this.path })
        .then(res => {
          this.waiting--;
          this.mode = "list";
          this.fetchData();
        })
        .catch(res => {
          console.error(res);
          handleErrorsFromResponse(this, res);
          this.waiting--;
        });
    },
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
          this.sortedfiles = [...this.files];
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

