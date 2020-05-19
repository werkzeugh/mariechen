import Vue from 'vue'
import App from './App.vue'
import MultiVue from 'vue-multivue';

import make_router from "./router";
import store from "./store";

import VueRouter from "vue-router";
const R = require('ramda');



Vue.config.productionTip = false

Vue.use(VueRouter);

let querySelector = '.vueapp-bagdetail';
const appElements = Array.from(document.querySelectorAll(querySelector));
const instances = [];

const camelizeRE = /-(\w)/g
export const camelize = string => {
  return string.replace(camelizeRE, (_, c) => c ? c.toUpperCase() : '')
};


const prodInfoEl = document.querySelector(".product-info");
const imgInfoEl = document.querySelector(".thumbnail-container");
const detailRightEl = document.querySelector(".detail-right");
const setFixedColumnWidth = function () {

  console.log('#log 6566 setFixedColumnWidth', document.querySelector(".detail-right").offsetWidth);
  let parentWidth = document.querySelector(".detail-right").offsetWidth;
  prodInfoEl.style.width = parentWidth + 'px';

  let parentWidth2 = document.querySelector(".detail-thumbnails").offsetWidth;
  imgInfoEl.style.width = parentWidth2 + 'px';

};

var matches = function (el, selector) {
  return (el.matches || el.matchesSelector || el.msMatchesSelector || el.mozMatchesSelector || el.webkitMatchesSelector || el.oMatchesSelector).call(el, selector);
};



const watchScroll1 = function () {
  const imageListHeight = document.querySelector(".image-list").offsetHeight;
  const prodInfoTop = detailRightEl.offsetTop;
  const prodInfoHeight = prodInfoEl.offsetHeight;
  const diff = imageListHeight - prodInfoHeight;



  if (imageListHeight > window.innerHeight && diff > 0 && document.documentElement.scrollTop > 0) {
    prodInfoEl.classList.add('scroll-start');
  } else {
    if (matches(prodInfoEl, '.scroll-start')) {
      prodInfoEl.classList.remove('scroll-start');
    }

  }

  if (diff > 0) {
    if (document.documentElement.scrollTop >= diff) {
      prodInfoEl.classList.add('scroll-end');
    } else {
      if (matches(prodInfoEl, '.scroll-end')) {
        prodInfoEl.classList.remove('scroll-end');
      }
    }
  } else {
    if (matches(prodInfoEl, '.scroll-end')) {
      prodInfoEl.classList.remove('scroll-end');
    }
  }

};


const watchScroll2 = function () {
  const imageListHeight = document.querySelector(".image-list").offsetHeight;
  const imgInfoTop = detailRightEl.offsetTop;
  const imgInfoHeight = imgInfoEl.offsetHeight;
  const diff = imageListHeight - imgInfoHeight;



  if (imageListHeight > window.innerHeight && diff > 0 && document.documentElement.scrollTop > 0) {
    imgInfoEl.classList.add('scroll-start');
  } else {
    if (matches(imgInfoEl, '.scroll-start')) {
      imgInfoEl.classList.remove('scroll-start');
    }

  }

  if (diff > 0) {
    if (document.documentElement.scrollTop >= diff) {
      imgInfoEl.classList.add('scroll-end');
    } else {
      if (matches(imgInfoEl, '.scroll-end')) {
        imgInfoEl.classList.remove('scroll-end');
      }
    }
  } else {
    if (matches(imgInfoEl, '.scroll-end')) {
      imgInfoEl.classList.remove('scroll-end');
    }
  }

};


setFixedColumnWidth();

document.addEventListener("DOMContentLoaded", setFixedColumnWidth);

window.addEventListener('resize', setFixedColumnWidth);
window.addEventListener('scroll', watchScroll1);
window.addEventListener('scroll', watchScroll2);

if (appElements.length > 0) {

  for (const appEl of appElements) {



    let widgetProps = R.reduce(
      (acc, item) => ({
        ...acc,
        [camelize(item.name)]: item.value
      }), {}
    )(appEl.attributes);


    let childData = [];
    for (let node of appEl.children) {
      childData.push(R.reduce(
        (acc, item) => ({
          ...acc,
          [item.name]: item.value
        }), {}
      )(node.attributes));
    }

    widgetProps.childNodes = childData;


    let vueInstance = new Vue({
      render: h => h(App),
      el: appEl,
      data: {
        tagName: null,
        attributes: {}
      },
      beforeMount: function () {
        this.tagName = this.$el.tagName.toLowerCase();
      },
      router: make_router(widgetProps),
      store
    });


    instances.push(vueInstance);
  }

}
