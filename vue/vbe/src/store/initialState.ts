declare global {
  interface Window {
    vbeAppConf: object;
  }
}

export default {
  debug: true,
  counter: 0,
  currentProductId: 0,
  activeImageIndex: 0,
  images: [],
  products: [],
  extconf: window.vbeAppConf,
  cartCount: null,
};
