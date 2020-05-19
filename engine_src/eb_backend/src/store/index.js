import Vue from "vue";
import Vuex from "vuex";
import * as getters from "./getters";
import * as actions from "./actions";
import * as mutations from "./mutations";
import * as R from "ramda";

// import createPersistedState from 'vuex-persistedstate';

import initialState from "./initialState";
import moduletags from "./modules/tags";

Vue.use(Vuex);

// const cacheKey='vuex_foo';

const store = new Vuex.Store({
    state: R.clone(initialState),
    getters,
    actions,
    mutations,
    modules: {
        tags: moduletags,
    },
    // plugins: [createPersistedState({key:cacheKey,paths:['cart']})],
    strict: true
});

export default store;
