<template>

  <div
    class="shopitemchooser bootstrap group"
    :data-articleid="selectedShopItem.articleId"
    :data-amount="currentAmount"
  >

    <div
      class="oldprice"
      v-show="selectedShopItem.listPrice"
    ><span class="instead">Statt</span> {{selectedShopItem.listPrice}} <i class='fa fa-euro'></i></div>
    <div class="price"><span
        class="only"
        v-show="selectedShopItem.listPrice"
      >nur</span>{{selectedShopItem.price}} <i class='fa fa-euro'></i></div>

    <div class="infoline">inkl. 20% MWST zzgl. <a
        href="/de/kundenservice/versand/"
        target="_blank"
      >Versandkosten</a></div>

    <div class="variant-title">Größe / Länge / Farbe:</div>
    <div class="shopitem current">
      <div class="text">
        <span class="title">{{selectedShopItem.title}}</span>
        <span class="deliveryinfo"> (<span v-show="selectedShopItem.inStock">sofort lieferbar</span>
          <span
            v-show="!selectedShopItem.inStock"
            v-html="settings.zeroStockInfo "
          ></span>)</span>
      </div>

      <button
        class="btn btn-sm btn-info pull-right choose-btn"
        type="button"
        @click="chooseMode=!chooseMode"
        v-show="settings.shopItems.length>1"
      ><i :class="{fa:true,'fa-caret-up':chooseMode,'fa-caret-down':!chooseMode}"></i></button>

    </div>

    <div class="group">

      <ul
        class="variants"
        :class="{'choosemode':chooseMode}"
      >
        <li
          v-for="(shopItem, index) in settings.shopItems"
          :key="index"
          class="shopitem"
          :class="{'active':selectedShopItem.articleId==shopItem.articleId}"
          @click="chooseItem(shopItem)"
        >

          <div class="title">{{shopItem.title}}</div>
        </li>
      </ul>
    </div>

    <div class="group cart-add-line">
      <div class='pull-right align-right'>
        <div v-show="settings.isHidden">
          <button
            class="btn  intocart"
            type="submit"
            disabled
          ><i class="icon-white icon-plus"></i> in den Warenkorb</button>
          <div>&nbsp;</div>
          <div class='alert alert-danger'><i class="icon-warning-sign"></i> Dieses Produkt ist zur Zeit nicht verfügbar.</div>
        </div>
        <div v-show="!settings.isHidden">
          <button
            class="btn btn-info  intocart"
            type="button"
            :disabled="!(selectedShopItem.maxAmount && currentAmount>0)"
          ><i class='fa fa-chevron-right'></i> in den Warenkorb</button>
        </div>
      </div>
      <select
        class="amount_dd form-control"
        v-show="selectedShopItem.maxAmount"
        v-model="currentAmount"
      >
        <option
          v-for="(item, index) in options"
          :key="index"
          :value="item"
        >{{item}}</option>
      </select>

    </div>

  </div>

</template>

<script>
export default {
  name: "SubProductChooser",
  props: {
    settings: Object
  },
  data: function() {
    return {
      app: {},
      selectedShopItem: null,
      currentAmount: 0,
      chooseMode: false
    };
  },
  computed: {
    options: function() {
      return [...Array(this.selectedShopItem.maxAmount).keys()].map(i => i + 1);
    }
  },
  methods: {
    chooseItem: function(item) {
      if (this.chooseMode) {
        this.selectedShopItem = item;
        this.chooseMode = false;
        this.currentAmount = 1;
      }
    }
  },
  created: function() {
    // if (extraSettings != null ? extraSettings.query : void 0) {
    //   angular.extend($scope.query, extraSettings.query);
    // }
    if (this.settings.shopItems) {
      this.selectedShopItem = this.settings.shopItems[0];
      this.currentAmount = 1;
    }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style  lang="scss">
</style>
