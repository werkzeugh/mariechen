export const debug = state => state.debug;

export const extconf = state => state.extconf;
export const counter = state => state.counter;
export const csrfToken = state => state.extconf.csrfToken;
export const liveApiUrl = state => state.extconf.liveApiUrl;

// export const products = (state) => state.products;
// export const getProductById= (state, getters) => (id) => {
//   return getters.products.find(i => i.id === id);
// };
