<template>
  <div>
    <template v-if="loading">
      <i class='fa fa-spin fa-spinner'></i>
    </template>
    <template v-else>
      <vue-tags-input
        ref="taginput"
        v-model="tag"
        :tags="tags"
        :autocomplete-items="autocompleteItems"
        :add-only-from-autocomplete="true"
        :autocomplete-min-length="1"
        @tags-changed="newTags => tags = newTags"
        @before-adding-tag="obj => checkAddedTag(obj,_self)"
        placeholder=""
        :add-on-key="[13]"
      />
      <button
        class="btn btn-xs btn-default"
        v-if="record"
        @click="saveTags()"
      ><i class='fa fa-check'></i></button>
      <button
        class="btn btn-xs btn-default"
        v-if="record"
        @click="$parent.go('/view')"
      ><i class='fa fa-times'></i></button>
    </template>
  </div>
</template>

<script>
import VueTagsInput from "@johmun/vue-tags-input";
import { array_get } from "../utils";

const R = require("ramda");

export default {
  name: "TagEditor",
  data() {
    return {
      loading: true,
      types: [],
      tag: "",
      record: null,
      tags: [],
      inputFieldId: "",
      initialTagString: ""
    };
  },
  components: { VueTagsInput },
  methods: {
    checkAddedTag: (obj, self) => {
      if (self.tag == "") {
        self.saveTags();
      } else {
        obj.addTag();
      }
    },
    saveTags: function() {
      this.loading = true;
      this.$store
        .dispatch("tags/setTagsForRecord", {
          record: this.record,
          tag_string: this.tagString
        })
        .then(tags => {
          this.loading = false;
          this.$parent.go("/view");
        })
        .catch(res => {
          this.loading = false;
        });
    },
    getTagsForType: function(type) {
      return this.$store.getters["tags/getTagsForType"](type);
    },
    fetchTypes: function() {
      R.map(type => {
        if (this.getTagsForType(type) === null) {
          this.$store
            .dispatch("tags/fetchTagsForType", type)
            .then(tags => {
              console.log("#log save", { type, tags });
              this.$store.commit("tags/setTagsForType", { type, tags });
              this.$store.commit("tags/incVersion");
            })
            .catch(res => {});
        }
      })(this.types);
    },
    getTagsForTagString: function(tagstring) {
      this.$store
        .dispatch("tags/getTagsForString", tagstring)
        .then(tags => {
          console.log("#tags got", tags);
          this.tags = tags;
          this.loading = false;
        })
        .catch(res => {});
    },
    getTagsForRecord: function(record) {
      this.$store
        .dispatch("tags/getTagsForRecord", record)
        .then(tags => {
          console.log("#tags got", tags);
          this.tags = tags;
          this.loading = false;
        })
        .catch(res => {});
    },
    readInitialValue: function() {
      this.inputField = document.getElementById(this.inputFieldId);
      this.initialTagString = this.inputField.getAttribute("value");
      if (this.initialTagString && this.initialTagString.length > 1) {
        this.getTagsForTagString(this.initialTagString);
      } else {
        this.loading = false;
      }
    },
    updateInputField: function(val) {
      if (this.inputField) {
        this.inputField.setAttribute("value", val);
      }
    }
  },
  watch: {
    tagString: function(newval, oldval) {
      this.updateInputField(newval ? newval : "#");
    }
  },
  computed: {
    version: function() {
      return this.$store.getters["tags/version"];
    },
    tagString: function() {
      return R.pipe(
        R.map(tag => "#" + tag.key),
        R.join(" ")
      )(this.tags);
    },
    availableTags: function() {
      let d = this.version;
      return R.pipe(
        R.map(type => {
          let tags = this.getTagsForType(type);
          return tags === null ? [] : tags;
        }),
        R.flatten()
      )(this.types);
    },
    filteredItems: function() {
      return this.availableTags.filter(i => {
        return i.text.toLowerCase().indexOf(this.tag.toLowerCase()) !== -1;
      });
    },
    autocompleteItems: function() {
      return [
        // { key: "####", text: "####", classes: "dummy-tag" },
        ...this.filteredItems
      ];
    }
  },
  created: function() {
    if (this.$root.attributes.types) {
      this.types = this.$root.attributes.types.split(",");
      this.fetchTypes();
    }
    if (this.$root.attributes.ref_id) {
      this.inputFieldId = this.$root.attributes.ref_id;
      this.readInitialValue();
    }
    if (this.$root.attributes.record) {
      this.record = this.$root.attributes.record;
      this.getTagsForRecord(this.record);
    }
    setTimeout(() => {
      console.log("#log 1533", this.$refs);
      this.$refs.taginput.$refs.newTagInput.focus();
    }, 500);
  }
};
</script>
<style lang="scss">
@import "../styles/settings.scss";

.dummy-tag {
  display: none;
}
</style>
