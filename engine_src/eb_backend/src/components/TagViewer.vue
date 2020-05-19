<template>
  <div class="tag-viewer">
    <template v-if="loading">
      <i class='fa fa-spin fa-spinner'></i>
    </template>

    <div>
      <span
        v-for="tag in tags"
        :key="tag.key"
        class="ti-tag"
        :class="tag.classes"
      >{{tag.text}}</span>
      <button
        class="btn btn-xs btn-default"
        v-if="editable"
        @click="toggleEdit()"
      ><i class='fa fa-pencil'></i></button>
    </div>
  </div>
</template>

<script>
export default {
  name: "TagViewer",
  data() {
    return {
      tags: [],
      loading: true,
      editable: false,
      initialTagString: "aa"
    };
  },
  components: {},
  methods: {
    toggleEdit: function() {
      this.$parent.go("/edit");
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
    }
  },
  computed: {},
  created: function() {
    console.log("#log 9900", this.$root.attributes);

    if (this.$root.attributes.record) {
      this.record = this.$root.attributes.record;
      this.getTagsForRecord(this.record);
      if (this.$root.attributes.editable) {
        this.editable = true;
      }
    } else {
      this.initialTagString = this.$root.attributes.tagids;
      this.getTagsForTagString(this.initialTagString);
    }
  }
};
</script>
<style lang="scss">
@import "../styles/settings.scss";

.tag-viewer {
  span.ti-tag {
    background: #5c6bc0;
    color: #black;
    border-radius: 2px;
    display: inline-block;
    padding: 3px 5px;
    margin: 2px;
    font-size: 0.85em;
  }
}
</style>
