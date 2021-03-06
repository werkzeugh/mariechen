import Vue from "vue";
import Vuex from "vuex";
import * as getters from "./getters";
import * as actions from "./actions";
import * as mutations from "./mutations";
// import createPersistedState from 'vuex-persistedstate';

import state from "./initialState";

Vue.use(Vuex);

const store = new Vuex.Store({
  state,
  getters,
  actions,
  mutations,
  strict: true
});

if (module.hot) {
  module.hot.accept(["./getters", "./actions", "./mutations"], () => {
    store.hotUpdate({
      getters: require("./getters"),
      actions: require("./actions"),
      mutations: require("./mutations")
    });
  });
}

export default store;
