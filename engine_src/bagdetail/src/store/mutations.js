// import * as R from 'ramda';
// import initialState from "./initialState";
// export const resetState  = (state) => {
//   const clonedState=R.clone(initialState);
//   Object.keys(clonedState).forEach(key => { state[key] = clonedState[key] });
// };

export const mutateCounter = (state, incrementor) => {
    state.counter = state.counter + incrementor;
};


export const setCurrentVariantId = (state, val) => {
    state.currentVariantId = val;
};


export const setActiveImageIndex = (state, val) => {
    state.activeImageIndex = val;
};

export const setVariants = (state, val) => {
    state.variants = val;
};

export const setImages = (state, val) => {
    state.images = val;
};
