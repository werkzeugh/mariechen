<template>
  <div>

    <div
      class="imagelist-mobile"
      tabindex="0"
    >
      <swiper
        :options="mobileSwiperOption"
        :ref="'mobileswiper'"
      >
        <swiper-slide
          v-for="(img, index) in variantImages"
          :key="index"
        >
          <div class="swiper-zoom-container">
            <img :src="img.biglink">
          </div>
        </swiper-slide>
        <div
          class="swiper-pagination"
          slot="pagination"
        ></div>

      </swiper>
    </div>
    <div
      class="imagelist-fullscreen"
      :class="{'is-visible':fullscreen,'full-opacity':fullscreen}"
      tabindex="0"
    >
      <a
        class="close-button"
        aria-label="Close"
        @click.prevent="closeImageDetail()"
      ><i class='fal fa-times fa-2x'></i>
      </a>
      <swiper
        :options="swiperOption"
        :ref="'swiper0'"
        v-if="fullscreen"
      >
        <swiper-slide
          v-for="(img, index) in variantImages"
          :key="index"
        >
          <div class="swiper-zoom-container">
            <img :src="img.biglink">
          </div>
        </swiper-slide>
        <div
          class="swiper-button-prev swiper-button-black"
          slot="button-prev"
        ></div>
        <div
          class="swiper-button-next swiper-button-black"
          slot="button-next"
        ></div>

      </swiper>
    </div>
    <div class="image-list">
      <figure
        class="prod-image zoom"
        :style="{backgroundImage: 'url('+img.biglink+')'}"
        :key="index"
        v-for="(img, index) in variantImages"
        @mousemove="zoom"
        @click.prevent="openImageDetail(index)"
        :id="'img_'+index"
        :ref="'img_'+index"
      >
        <img :src="img.link" />
      </figure>

    </div>

  </div>
</template>

<script>
import "swiper/dist/css/swiper.css";

import { swiper, swiperSlide } from "vue-awesome-swiper";
const R = require("ramda");

var splitPlus = function(str, sep) {
  var a = str.split(sep);
  if (a[0] == "" && a.length == 1) return [];
  return a;
};
export default {
  name: "ImageList",
  data() {
    return {
      fullscreen: false,
      fullscreenImgIdx: 0,
      swiperOption: {
        // effect: "fade",
        speed: 1000,
        loop: true,
        preventClicks: false,
        slidesPerView: 1,
        keyboard: {
          enabled: true,
          onlyInViewport: false
        },
        navigation: {
          nextEl: ".swiper-button-next",
          prevEl: ".swiper-button-prev"
        }
      },
      mobileSwiperOption: {
        // effect: "fade",
        speed: 1000,
        loop: true,
        preventClicks: false,
        slidesPerView: 1,
        pagination: {
          el: ".swiper-pagination",
          type: "fraction"
        }
      }
    };
  },
  props: {
    childNodes: { type: Array }
  },
  components: {
    swiper,
    swiperSlide
  },
  methods: {
    openImageDetail: function(index) {
      this.fullscreen = true;
      this.fullscreenImgIdx = index;
    },
    closeImageDetail: function() {
      this.fullscreen = false;
    },
    watchTnScroll: function(e) {
      //    // Get container scroll position
      var fromTop = document.documentElement.scrollTop;

      // Get id of current scroll item

      let cur = R.pipe(
        R.map(ref =>
          this.$refs[ref][0].offsetTop + this.$refs[ref][0].offsetHeight / 2 <
          fromTop
            ? this.$refs[ref][0]
            : null
        ),
        R.filter(el => el !== null)
        // R.last()
      )(this.variantImageElementRefs);
      this.$store.commit("setActiveImageIndex", cur.length);
    },
    setupScrollWatch: function() {
      window.addEventListener("scroll", this.watchTnScroll);
    },
    getImgById: function(id) {
      return this.images.find(t => t.id == id);
    },
    zoom: function(e) {
      // console.log("#log 1480", e);
      var zoomer = e.currentTarget;
      let offsetX = 0;
      let offsetY = 0;
      e.offsetX ? (offsetX = e.offsetX) : (offsetX = e.touches[0].pageX);
      e.offsetY ? (offsetY = e.offsetY) : (offsetX = e.touches[0].pageX);
      const x = (offsetX / zoomer.offsetWidth) * 100;
      const y = (offsetY / zoomer.offsetHeight) * 100;
      zoomer.style.backgroundPosition = x + "% " + y + "%";
    },
    setupEscapeKey: function(callback) {
      document.addEventListener("keyup", function(evt) {
        if (evt.keyCode === 27) {
          console.log("#log 6877 ESC ");
          callback();
        }
      });
    }
  },
  computed: {
    images: function() {
      return R.map(item => {
        return {
          id: parseInt(item.dbid, 10),
          link: item.src,
          biglink: item.bigsrc,
          smalllink: item.smallsrc,
          tagIds: R.map(a => parseInt(a, 10))(splitPlus(item.tags, " "))
        };
      })(this.childNodes);
    },
    currentVariant: function() {
      return this.$store.getters.currentVariant;
    },
    variantImages: function() {
      if (!this.currentVariant) {
        return this.images;
      }
      return R.map(imgid => this.getImgById(imgid))(this.currentVariant.imgIds);
    },
    variantImageElementRefs: function() {
      var mapIndexed = R.addIndex(R.map);

      return mapIndexed((el, idx) => "img_" + idx, this.variantImages);
    }
  },
  watch: {
    variantImages: function(imgs) {
      this.$store.commit("setImages", [...imgs]);
      this.$refs.mobileswiper.swiper.slideTo(1);
    },
    fullscreen: function(fullscreen) {
      if (fullscreen) {
        this.swiperOption.initialSlide = this.fullscreenImgIdx;

        document.body.classList.add("u-overflow-hidden");
      } else {
        document.body.classList.remove("u-overflow-hidden");
      }
    }
  },
  created: function() {},
  mounted: function() {
    this.setupScrollWatch();
    this.setupEscapeKey(this.closeImageDetail);
  }
};
</script>
<style lang="scss">
@import "../styles/settings.scss";

figure.active {
  border: 1px solid red !important;
}

.imagelist-fullscreen {
  background-color: #fff;
  z-index: 100;
  transition: opacity 2s ease;
  display: none;
  opacity: 0;
  position: fixed;
  right: 0;
  bottom: 0;
  top: 0;
  left: 0;
  overflow: auto;

  &.full-opacity {
    opacity: 1;
  }

  &.is-visible {
    display: block;
  }
  .swiper-button-prev,
  .swiper-button-next {
    position: fixed;
    fill: black;
    stroke: black;
    zoom: 0.7;
  }
  .swiper-button-next {
    right: 20px;
  }

  .swiper-zoom-container > img {
    max-height: 100vh;
  }
  .close-button {
    top: 25px;
    right: 25px;
    position: fixed;
    z-index: 1000;
  }
}

.u-overflow-hidden {
  overflow: hidden;
}
</style>
