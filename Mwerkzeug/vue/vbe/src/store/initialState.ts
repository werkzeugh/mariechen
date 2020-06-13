declare global {
  interface Window {
    backendBaseUrl: string;
  }
}

export default {
  debug: true,
  counter: 0,
  currentProductId: 0,
  activeImageIndex: 0,
  images: [],
  products: [],
  extconf: window.backendBaseUrl,
  cartCount: null,
};
