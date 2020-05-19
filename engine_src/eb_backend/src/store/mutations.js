// import * as R from 'ramda';
// import initialState from "./initialState";
// export const resetState  = (state) => {
//   const clonedState=R.clone(initialState);
//   Object.keys(clonedState).forEach(key => { state[key] = clonedState[key] });
// };

export const mutateCounter = (state, incrementor) => {
    state.counter = state.counter + incrementor;
};
