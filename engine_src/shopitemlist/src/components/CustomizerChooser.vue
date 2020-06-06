<template>

  <div
    class="shopitemchooser bootstrap group customizer_chooser"
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

    <div class="group">
      <a
        v-for="(shopItem, index) in settings.shopItems"
        :key="index"
        class="variant"
        :class="{'active':selectedShopItem.articleId==shopItem.articleId}"
        @click.prevent="chooseItem(shopItem)"
        href="#"
      >{{shopItem.title}}</a>
    </div>

    <div class="group cart-add-line">
      <div class='pull-right align-right'>
        <div v-show="settings.isHidden">
          <button
            class="btn  intocart"
            type="submit"
            disabled
          ><i class="icon-white icon-plus"></i> Motiv hochladen</button>
          <div>&nbsp;</div>
          <div class='alert alert-danger'><i class="icon-warning-sign"></i> Dieses Produkt ist zur Zeit nicht verfügbar.</div>
        </div>
        <div v-show="!settings.isHidden">
          <a
            class="btn btn-info"
            type="button"
            :href="'https://engine.slide-it.net/editor/new/'+selectedShopItem.nr+'?article='+selectedShopItem.articleId"
            :disabled="!(selectedShopItem.maxAmount && currentAmount>0)"
          ><i class='fa fa-chevron-right'></i> Motiv hochladen</a>
        </div>
      </div>

    </div>

  </div>

</template>

<script>
export default {
  name: "CustomizerChooser",
  props: {
    settings: Object
  },
  data: function() {
    return {
      app: {},
      selectedShopItem: null,
      currentAmount: 0
    };
  },
  computed: {
    options: function() {
      return [...Array(this.selectedShopItem.maxAmount).keys()].map(i => i + 1);
    }
  },
  methods: {
    chooseItem: function(item) {
      this.selectedShopItem = item;
      this.currentAmount = 1;
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
@import "../styles/settings.scss";

.customizer_chooser {
  .variant {
    display: inline-block;
    padding: 0.5em 1em;
    border: 1px solid #999;
    color: #555;
    margin: 0.2em;
    border-width: 1.5px;
    background: rgba(white, 0.5);
    &.active,
    &:hover {
      background: white;
      border-color: $c-primary;
      border-width: 1.5px;
      font-weight: bold;
      text-decoration: none;
    }
  }
}
</style>
