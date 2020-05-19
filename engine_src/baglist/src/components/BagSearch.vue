<template>
  <div class="bagsearch">

    <div
      class="bagsearch-title"
      :class="{'expanded':expanded.top,'collapsed':!expanded.top,'filters-set':(filtercount>0)}"
    >
      <a
        @click.prevent="expandPane('top')"
        class="opener"
      >
        <span class="icon">
          <i
            class="fas fa-minus"
            v-if="expanded.top"
          ></i>
          <i
            class="fal fa-plus"
            v-else
          ></i>
        </span>

        <span class="text">Filter

          <span class='filtercount'>({{filtercount}})</span>

        </span>
      </a>
      <a
        @click.prevent="resetFilters"
        class="resetter"
        v-if="filtercount>0"
      >{{trans('Reset','Zurücksetzen')}}</a>
    </div>

    <div
      class="filters expandable"
      :class="expanded.top?'expanded':'collapsed'"
    >

      <div class="filterpane">

        <div class="f-header mobile">
          <div class="f-title">
            Filter
          </div>
          <div class="f-closer">
            <a @click.prevent="expandPane('top')"><i class='fal fa-times fa-2x'></i></a>
          </div>
        </div>

        <TagTreeChooser
          :tags="tags.material"
          title="Material"
          v-model="data.material"
        ></TagTreeChooser>

        <TagTreeChooser
          :tags="tags.colors"
          title="Colour"
          v-model="data.color"
        ></TagTreeChooser>

        <TagTreeChooser
          :tags="tags.size"
          title="Size"
          v-model="data.size"
        ></TagTreeChooser>
      </div>
      <div class="buttonpane mobile">
        <div>
          <button
            class="btn btn-sm btn-outline-secondary"
            type="button"
            @click.prevent="resetFilters();expandPane('top');"
            v-if="filtercount>0"
          >{{trans('Reset','Zurücksetzen')}}</button>
        </div>
        <div>
          <button
            class="btn btn-sm btn-primary"
            type="button"
            @click.prevent="expandPane('top')"
          >{{trans('apply','OK')}}</button>
        </div>
      </div>

    </div>

  </div>

</template>

<script>
// const {create, env} = require('sanctuary');
// const checkTypes = process.env.NODE_ENV !== 'production';
// const S = create({checkTypes, env});
const R = require("ramda");

import debounce from "lodash.debounce";

import Vue from "vue";
// some JS file
import store from "../store";

import TagTreeChooser from "./TagTreeChooser";

export default {
  name: "bagsearch",
  props: {},
  data: function() {
    return {
      expanded: {
        top: false
      },
      initialLoad: true,
      loadCount: 0,
      data: {
        keyword: "",
        color: [],
        material: [],
        size: [],
        usage: []
      }
    };
  },
  components: {
    TagTreeChooser
  },
  methods: {
    trans: function(en, de) {
      return this.$store.getters.lang == "de" ? de : en;
    },
    resetFilters: function() {
      console.log("#log 6220 resetf");
      this.data.keyword = "";
      this.data.color = [];
      this.data.material = [];
      this.data.size = [];
      this.data.usage = [];
    },
    expandPane: function(paneName) {
      console.log("#log 3753", paneName);
      this.expanded[paneName] = !this.expanded[paneName];
    },
    restoreQueryFromUrl: function() {
      console.log("#log 1646 restoreQueryFromUrl", this.$route.query);
      for (const key in this.$route.query) {
        if (this.$route.query.hasOwnProperty(key)) {
          const element = this.$route.query[key];
          this.data[key] = element.split(",");
        }
      }
      if (this.$route.query["page"]) {
        this.$store.commit("setCurrentPage", this.$route.query["page"]);
      }
    },
    setQueryInUrl: function(query) {
      if (!this.initialLoad) {
        let obj = Object.assign({}, this.$route.query);

        Object.keys(query).forEach(key => {
          let value = query[key];
          if (value) {
            obj[key] = value;
          } else {
            delete obj[key];
          }
        });
        this.$router
          .replace({
            ...this.$router.currentRoute,
            query: obj
          })
          .catch(err => {});
      }
    },
    removeQueryInUrl: function(queryNameArray) {
      console.log("#log 8891 removeQueryInUrl", queryNameArray);
      let obj = {};
      queryNameArray.forEach(key => {
        obj[key] = null;
      });
      this.setQueryInUrl(obj);
    },
    searchFormSubmit: function(mode) {
      this.loadCount++;
      let query = {};
      console.log("#searchFormSubmit " + mode, this.data);

      if (this.data.color.length > 0) {
        query.color = this.data.color.join(",");
      } else {
        this.removeQueryInUrl(["color"]);
      }
      if (this.data.size.length > 0) {
        query.size = this.data.size.join(",");
      } else {
        this.removeQueryInUrl(["size"]);
      }
      if (this.data.material.length > 0) {
        query.material = this.data.material.join(",");
      } else {
        this.removeQueryInUrl(["material"]);
      }
      if (this.data.usage.length > 0) {
        query.usage = this.data.usage.join(",");
      } else {
        this.removeQueryInUrl(["size"]);
      }

      if (this.$store.getters.currentPage > 1) {
        query.page = this.$store.getters.currentPage;
      } else {
        this.removeQueryInUrl(["page"]);
      }
      this.setQueryInUrl(query);
      this.initialLoad = false;
      let query_for_api = this.prepareQueryForApi(query);
      query_for_api.per_page = 100;
      console.log("#log 1068", query_for_api);
      this.$store.dispatch("fetchItems", { ...query_for_api });
    },
    prepareQueryForApi: function(object) {
      const newObject = {};
      var n = 1;
      if (this.$store.getters.baseTags) {
        R.split(" ", this.$store.getters.baseTags).forEach(element => {
          newObject["tags" + n++] = element;
        });
      }
      for (const key in object) {
        if (["color", "material", "size", "usage"].includes(key)) {
          const val = object[key];

          if (val) {
            newObject["tags" + n++] = val;
          }
        }
        if (["page", "per_page"].includes(key)) {
          const val = object[key];

          if (val) {
            newObject[key] = val;
          }
        }
      }
      newObject["currency"] = this.$store.getters.currency;
      newObject["lang"] = this.$store.getters.lang;

      return newObject;
    },

    toggleKey: function(bucket, item) {
      console.log("#log", bucket, item);
      if (this.hasKey(bucket, item)) {
        this.$delete(bucket, bucket.indexOf(item));
      } else {
        bucket.push(item);
      }
    },
    hasKey: function(bucket, item) {
      return R.contains(item, bucket);
    },
    fetchItemsIfNeeded: function() {
      this.$store.dispatch("fetchTags");
    },
    setWatchers: function() {
      this.$watch("$store.getters.currentPage", function(v) {
        this.searchFormSubmit("pageChange");
      });

      this.$watch("data", {
        handler: debounce(function(v) {
          this.$store.commit("setCurrentPage", 1);
          this.searchFormSubmit("datawatch");
        }, 1000),
        deep: true
      });
    }
  },
  created: function() {
    this.restoreQueryFromUrl();
    this.fetchItemsIfNeeded();
    this.searchFormSubmit("initialLoad");
    this.setWatchers();
  },
  watch: {},
  computed: {
    filtercount: function() {
      return (
        this.data.color.length +
        this.data.material.length +
        this.data.size.length +
        this.data.usage.length
      );
    },
    isLoading: function() {
      return this.$store.getters.isLoading;
    },
    tags: function() {
      return this.$store.getters.tags;
    },
    lang: function() {
      return this.$store.getters.lang;
    }
  }
};
</script>

<style lang="scss">
@import "../styles/settings.scss";
</style>

