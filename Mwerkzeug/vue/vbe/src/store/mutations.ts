// import * as R from 'ramda';
// import initialState from "./initialState";
// export const resetState  = (state) => {
//   const clonedState=R.clone(initialState);
//   Object.keys(clonedState).forEach(key => { state[key] = clonedState[key] });
// };

export const mutateCounter = (state, incrementor) => {
    state.counter = state.counter + incrementor;
};


export const setCurrentProductId = (state, val) => {
    state.currentProductId = val;
};




export const setProducts = (state, val) => {
    state.products = val;
};
export const setCartCount = (state, val) => {
    state.cartCount = val;
};
