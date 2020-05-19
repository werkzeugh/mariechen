<template>
  <div>
    <template>

      <div
        v-if="dirty"
        class="savesort-div"
      >
        <div>&nbsp;</div>
        <button
          class="btn btn-primary btn-sm"
          type="submit"
        ><i class='fa fa-check'></i> save new sort-order</button>
        <div>&nbsp;</div>
      </div>
      <input
        type="hidden"
        v-model="idString"
        :name="name"
      ></input>
    </template>
  </div>
</template>

<script>
const R = require("ramda");

export default {
  name: "RowSorter",
  data() {
    return {
      idString: "",
      dirty: false
    };
  },
  props: {
    name: { type: String, required: true }
  },
  methods: {
    saveSorting: function() {
      this.idString = R.pipe(
        R.map(row => row.dataset.id),
        R.join(",")
      )(Object.values(document.getElementsByClassName("js-sortable-tr")));
    },
    startSorting: function() {
      sortable(".js-sortable", {
        items: "tr.js-sortable-tr",
        placeholder:
          '<tr><td colspan="10"><span class="center">The row will appear here</span></td></tr>',
        forcePlaceholderSize: false,
        handle: ".js-sortable-handle"
      });
      sortable(".js-sortable")[0].addEventListener("sortupdate", e => {
        this.saveSorting();
        this.dirty = true;
      });
    }
  },
  watch: {},
  computed: {},
  mounted: function() {
    this.startSorting();
  }
};
</script>
<style lang="scss">
@import "../styles/settings.scss";

.dummy-tag {
  display: none;
}
.savesort-div {
  text-align: right;
}
.js-sortable-handle {
  text-align: right;
}
</style>
