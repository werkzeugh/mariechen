const R = require("ramda");

export const items = state => state.items;
export const tags = state => state.tags;
export const isLoading = state => state.isLoading;
export const apiUrl = state => state.extconf.apiUrl;
export const baseTags = state => R.trim(state.extconf.baseTags || "");
export const lang = state => state.extconf.lang;
export const currency = state => state.extconf.currency;
export const currencySymbol = state => state.extconf.currency == 'usd' ? '$' : '€';
export const currentPage = state => state.currentPage;
