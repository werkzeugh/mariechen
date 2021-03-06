export const debug = state => state.debug;

export const extconf = state => state.extconf;
export const counter = state => state.counter;
export const csrfToken = state => state.extconf.csrfToken;
export const liveApiUrl = state => state.extconf.liveApiUrl;
export const lang = state => state.extconf.lang;

export const currentVariantId = state => state.currentVariantId;
export const activeImageIndex = state => state.activeImageIndex;

export const variants = state => state.variants;
export const images = state => state.images;

export const getVariantById = (state, getters) => (id) => {
    console.log('#log 2512', id);
    return getters.variants.find(i => i.id === id);
};

export const currentVariant = (state, getters) => getters.getVariantById(getters.currentVariantId);
// export const products = (state) => state.products;
// export const getProductById= (state, getters) => (id) => {
//   return getters.products.find(i => i.id === id);
// };
