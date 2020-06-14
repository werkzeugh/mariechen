// import Vue from 'vue';
// import Vuex from 'vuex';
import * as getters from './getters';
import * as actions from './actions';
import * as mutations from './mutations';
import * as R from 'ramda';

// import createPersistedState from 'vuex-persistedstate';

import initialState from './initialState';


export default {
  namespaced: true,
  state: function () {
    return R.clone(initialState);
  },
  getters,
  actions,
  mutations
};
