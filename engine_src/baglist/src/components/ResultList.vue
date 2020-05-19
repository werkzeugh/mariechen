<template>

  <div class="baglist">

    <div v-if="isLoading">
      <i class='fal fa-spinner fa-rotate fa-spin'></i>
    </div>

    <div
      class="prod-list"
      :style="containerStyles"
      v-else
    >
      <div
        v-if="items.count==0"
        class="notfound"
      >
        <div class="notfound-pic">
          <img src="/mysite/images/notfound.png">
        </div>
        <div class="notfound-txt">
          {{items.count}} {{trans('sorry, no products matching your search','Leider wurden zu deiner Suchanfrage keine Artikel gefunden')}}
        </div>
      </div>

      <div
        v-for="cat in grouped_items"
        :key="cat.key"
      >
        <a
          v-for="(item, index) in cat.items"
          :key="index"
          :class="'cnt-'+item.files.length"
          :href="itemlink(item)"
          @mouseover="mouseover(index,item.files)"
          @mouseleave="mouseleave(index)"
        >
          <div class="imgpane">

            <img :src="item.files[0].url">

            <img
              :src="hoverimgs[index]"
              class="on-hover"
              v-if="hoverimgs[index]"
            >

            <div class="variant-title">
              {{item.p_title}}
            </div>
          </div>
        </a>
      </div>
    </div>

    <b-pagination
      v-if="items.count>0 && items.count>items.per_page"
      v-model="localCurrentPage"
      :total-rows="items.count"
      :per-page="items.per_page"
      aria-controls="my-table"
      icon-pack="fal"
      align="center"
      :hide-goto-end-buttons="true"
    >
      <template v-slot:prev-text><i class='fal fa-chevron-left'></i></template>
      <template v-slot:next-text><i class='fal fa-chevron-right'></i></template>

    </b-pagination>
  </div>

</template>

<script>
// import "swiper/dist/css/swiper.css";

// import { swiper, swiperSlide } from "vue-awesome-swiper";

import debounce from "lodash.debounce";

import { BPagination } from "bootstrap-vue";

import { rangeValue } from "../utils";

const R = require("ramda");

function RememberScrollPage(scrollPos) {
  let UrlsObj = localStorage.getItem("rememberScroll");
  let urlsArr = JSON.parse(UrlsObj);

  if (urlsArr == null) {
    urlsArr = [];
  }

  if (urlsArr.length == 0) {
    urlsArr = [];
  }

  //special case: only store position for last list,delete others
  urlsArr = [];

  let urlWindow = window.location.href;
  let urlScroll = scrollPos;
  let urlObj = { url: urlWindow, scroll: scrollPos };
  let matchedUrl = false;
  let matchedIndex = 0;

  if (urlsArr.length != 0) {
    urlsArr.forEach(function(el, index) {
      if (el.url === urlWindow) {
        matchedUrl = true;
        matchedIndex = index;
      }
    });

    if (matchedUrl === true) {
      urlsArr[matchedIndex].scroll = urlScroll;
    } else {
      urlsArr.push(urlObj);
    }
  } else {
    urlsArr.push(urlObj);
  }

  localStorage.setItem("rememberScroll", JSON.stringify(urlsArr));
}

export default {
  name: "ResultList",
  data: function() {
    return {
      lastHoveredItemIndex: -1,
      hoverimgs: {},
      windowW: document.documentElement.clientWidth

      // swiperOption: {
      //   lazy: true,
      //   effect: "fade",
      //   speed: 2000,
      //   loop: true,
      //   preventClicks: false,
      //   navigation: {
      //     nextEl: ".swiper-button-next",
      //     prevEl: ".swiper-button-prev"
      //   }
      // }
    };
  },
  methods: {
    setTab: function(newtab) {
      this.currentTab = newtab;
    },
    trans: function(en, de) {
      return this.$store.getters.lang == "de" ? de : en;
    },
    handleWindowResize: debounce(function() {
      this.windowW = document.body.clientWidth;
      // console.log("#log 2362set w", this.windowW);
    }, 200),
    mouseover: function(i, files) {
      // console.log("#log 1167 mouseover", i, files.length);
      if (files.length == 2) {
        this.$set(this.hoverimgs, i, files[1].url);
      }
    },
    mouseleave: function(i) {
      if (this.hoverimgs[i] !== null) {
        this.$set(this.hoverimgs, i, null);
      }
    },
    itemlink: function(item) {
      return "/shop/" + item.p_category + "/" + item.p_url + "/" + item.pv_url;
    }
  },
  computed: {
    containerStyles: function() {
      return {
        fontSize: this.baseFontSize + "px"
      };
    },
    baseFontSize: function() {
      return rangeValue(this.windowW, 300, 2000, 12, 25);
    },
    items: function() {
      return this.$store.getters.items;
    },
    grouped_items: function() {
      let items = R.pipe(
        R.groupBy(R.prop("p_category")),
        R.toPairs(),
        R.map(([key, items]) => {
          return { key, items };
        })
      )(this.$store.getters.items.list);

      if (this.$store.getters.baseTags == "#classics") {
        items = R.reverse(items);
      }

      return items;
    },
    isLoading: function() {
      return this.$store.getters.isLoading;
    },
    total_items: function() {
      return this.items.length;
    },
    localCurrentPage: {
      get() {
        return this.$store.getters.currentPage;
      },
      set(v) {
        // console.log("#log 8135 set localCurrentPage", v);
        this.$store.commit("setCurrentPage", v);
      }
    }
  },
  watch: {
    "$store.getters.currentPage": function(val) {
      // console.log("#log 3901");
      this.localCurrentPage = val;
    }
  },
  components: {
    // swiper,
    // swiperSlide,
    "b-pagination": BPagination
  },
  created: function() {
    this.localCurrentPage = this.$store.getters.currentPage;
    // console.log("#log 6323", this.localCurrentPage);
  },
  mounted: function() {
    window.addEventListener("resize", this.handleWindowResize);
    setTimeout(
      function() {
        this.handleWindowResize();
      }.bind(this),
      10
    );
    setTimeout(
      function() {
        this.handleWindowResize();
      }.bind(this),
      500
    );

    window.addEventListener("scroll", function(event) {
      let topScroll = $(window).scrollTop();
      // console.log("Scrolling", topScroll);
      RememberScrollPage(topScroll);
    });

    let UrlsObj = localStorage.getItem("rememberScroll");
    let ParseUrlsObj = JSON.parse(UrlsObj);
    let windowUrl = window.location.href;

    if (ParseUrlsObj == null) {
      return false;
    }

    ParseUrlsObj.forEach(function(el) {
      if (el.url === windowUrl) {
        let getPos = el.scroll;
        setTimeout(
          function() {
            window.scrollTo(0, getPos);
          }.bind(this),
          500
        );
      }
    });
  }
};
</script>

<style lang="scss">
@import "../styles/settings.scss";
</style>

