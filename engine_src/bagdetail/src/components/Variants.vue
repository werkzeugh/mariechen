<template>
  <div>

    <div
      class="variants"
      :class="useDropdown?'use-dropdown':'no-dropdown'"
    >
      <a
        :href="currentVariant.link"
        @click.prevent="toggleDropdown"
        class="opener active"
        v-if="useDropdown"
      >
        <span class="swatch">
          <i
            class="swatch"
            :style="{backgroundColor:currentVariant.color}"
          ></i>
        </span>

        <span class="text">{{currentVariant.name}}</span>
        <span class="marker">
          <i
            v-if="dropdownOpen"
            class='fal fa-chevron-up'
          ></i>
          <i
            v-else
            class='fal fa-chevron-down'
          ></i>
        </span>
      </a>
      <div
        class="listedVariants"
        v-if="dropdownVisible"
      >
        <a
          v-for="(v, index) in listedVariants"
          :key="index"
          :href="v.link"
          :class="[(v.id==$store.getters.currentVariantId)?'active':'not-active',(v.inStock>0)?'in-stock':'no-stock']"
          @click.prevent="setCurrentVariantId(v.id)"
        >
          <span class="swatch">
            <i
              class="swatch"
              :style="{backgroundColor:v.color}"
            ></i>
          </span>

          <span class="text">{{v.name}}</span>
          <span class="marker">
            <i class='fal fa-check'></i>
          </span>
        </a>
      </div>
    </div>
    <div>&nbsp;</div>
    <div class="addtocart">
      <div v-if="currentVariant.inStock>0">
        <button
          class="btn btn-outline-primary btn-intocart"
          @click="addToCart($store.getters.currentVariantId)"
        >Add to Cart</button>
      </div>
      <div v-else>
        <span class="btn btn-outline btn-intocart">Sold Out</span>
      </div>
    </div>
  </div>
</template>

<script>
const R = require("ramda");

var splitPlus = function(str, sep) {
  var a = str.split(sep);
  if (a[0] == "" && a.length == 1) return [];
  return a;
};
export default {
  name: "Variants",
  data() {
    return {
      dropdownOpen: false
    };
  },
  props: {
    childNodes: { type: Array },
    currentVariantId: { type: String }
  },
  components: {},
  methods: {
    setCurrentVariantId: function(id) {
      this.$store.commit("setCurrentVariantId", id);
      this.dropdownOpen = false;
    },
    toggleDropdown: function() {
      console.log("#log 5226", this.dropdownOpen);
      this.dropdownOpen = !this.dropdownOpen;
    },
    addToCart: function(sku) {
      this.$store.dispatch("addToCart", sku);
    }
  },
  computed: {
    dropdownVisible: function(re) {
      if (this.useDropdown) {
        return this.dropdownOpen;
      }
      return true;
    },
    useDropdown: function() {
      return false;
      // return R.length(this.variants || []) > 10;
    },
    variants: function() {
      return R.map(item => {
        return {
          name: item.name,
          link: item.href,
          id: parseInt(item.dbid, 10),
          inStock: parseInt(item.instock, 10),
          color: item.colorstr,
          imgIds: R.map(a => parseInt(a, 10))(splitPlus(item.imgs, ","))
        };
      })(this.childNodes);
    },
    listedVariants: function() {
      if (this.useDropdown) {
        const currentVariantId = this.$store.getters.currentVariantId;
        return R.filter(a => a.id !== currentVariantId)(this.variants);
      }
      return this.variants;
    },
    currentVariant: function() {
      return this.$store.getters.currentVariant;
    }
  },
  created: function() {
    this.$store.commit(
      "setCurrentVariantId",
      parseInt(this.currentVariantId, 10)
    );

    this.$store.commit("setVariants", [...this.variants]);
  }
};
</script>
<style lang="scss">
@import "../styles/settings.scss";
.no-stock .text {
  opacity: 0.4;
}
</style>
