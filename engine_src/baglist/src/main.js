/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

// require('./bootstrap');

// import 'babel-polyfill';
// import 'es6-promise/auto';

import Vue from "vue";
window.Vue = Vue;

import store from "./store";
// import router from './router';
import VueRouter from "vue-router";
Vue.use(VueRouter);

const router = new VueRouter({
  routes: [{
    path: '/'
  }]
});

import AppWrapper from "./components/AppWrapper";

import VueVisible from "vue-visible";
Vue.use(VueVisible);

Vue.config.productionTip = false;

// import { sync } from 'vuex-router-sync'
// sync(store, router);

/* eslint-disable no-new */

window.baglistApp = new Vue({
  el: "#baglistapp",
  router,
  store,
  template: "<AppWrapper/>",
  components: {
    AppWrapper
  }
});
