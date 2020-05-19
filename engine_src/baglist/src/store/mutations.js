const R = require("ramda");


export const setItems = (state, payload) => {
    state.items = payload;
};

export const setTags = (state, payload) => {
    state.tags = payload;
};

export const setLoading = (state, payload) => {
    state.isLoading = payload;
};

export const setCurrentPage = (state, pagenum) => {
    if (!Number.isInteger(pagenum)) {
        pagenum = Number.parseInt(pagenum, 10);
    }
    state.currentPage = pagenum;
};
