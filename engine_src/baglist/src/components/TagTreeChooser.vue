<template>
  <div class="tag-chooser">
    <div class="tag-chooser-title">
      <span class="text">{{title}}</span>
    </div>

    <div class="tag-chooser-pane">
      <ul class="tagfilter">
        <li
          v-for="t in tags"
          :key="t.key"
          :class="[ hasKey(value,t.key) ? 'active':'not-active' ]"
        ><a @click.prevent="toggleKey(value,t.key)"><i
              class="fal fa-fw"
              :class="[ hasKey(value,t.key) ? 'fa-check':'fa-empty' ]"
            ></i>{{t.title}}</a></li>

      </ul>
    </div>

  </div>
</template>

<script>
const R = require("ramda");

export default {
  name: "TagTreeChooser",
  props: {
    tags: {
      required: true
    },
    value: Array,
    title: String
  },
  data: function() {
    return {
      expanded: {
        top: true
      }
    };
  },
  methods: {
    expandPane: function(paneName) {
      this.expanded[paneName] = !this.expanded[paneName];
    },
    toggleKey: function(bucket, item) {
      if (this.hasKey(bucket, item)) {
        this.$delete(bucket, bucket.indexOf(item));
      } else {
        bucket.push(item);
      }
    },
    hasKey: function(bucket, item) {
      return R.contains(item, bucket);
    }
  }
};
</script>


<style lang="scss">
@import "../styles/settings.scss";
</style>
