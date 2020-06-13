export const debug = state => state.debug;

export const extconf = state => state.extconf;
export const counter = state => state.counter;
export const csrfToken = state => state.extconf.csrfToken;
export const liveApiUrl = state => state.extconf.liveApiUrl;
export const lang = state => state.extconf.lang;

export const currentProductId = state => state.currentProductId;
export const cartCount = state => state.cartCount;

export const products = state => state.products;
export const images = state => state.images;

export const getProductById = (state, getters) => (id) => {
    console.log('#log 2512', id);
    return getters.products.find(i => i.id === id);
};

export const currentProduct = (state, getters) => getters.getProductById(getters.currentProductId);
// export const products = (state) => state.products;
// export const getProductById= (state, getters) => (id) => {
//   return getters.products.find(i => i.id === id);
// };
