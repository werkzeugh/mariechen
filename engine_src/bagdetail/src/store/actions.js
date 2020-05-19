// import {augmentApiUrl,devOfflineMode} from '../utils';
import axios from 'axios';
// let token = document.head.querySelector('meta[name="csrf-token"]');

// if (token) {
//     axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
// } else {
//     console.error(
//         "CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token"
//     );
// }

// export const modifyCounter = ({ commit, getters, state }, payload) => {
//     switch (payload.verb) {
//         case "increment":
//             commit("mutateCounter", 1);
//             break;
//         case "decrement":
//             commit("mutateCounter", -1);
//             break;
//     }
// };

// export const fetchFooBar = ({ commit, /*state,*/ getters }, query) => {

//     console.log('fetchFooBar', query);
//     return new Promise((resolve, reject) => {
//         axios.get(augmentApiUrl(getters.apiUrl + '/alleFooBar'), { params: query })
//             .then(function (response) {
//                 console.log('proj response', response);
//                 commit('setFooBar', response.data);
//                 resolve();
//             })
//             .catch(function (error) {
//                 reject(error.response);
//             });
//     });
// };


export const addToCart = ({
    commit,
    /*state,*/
    getters
}, sku) => {

    return new Promise((resolve, reject) => {
        axios.get(getters.liveApiUrl + '/cartapi/add_to_cart/' + sku)
            .then(function (response) {
                window.updateCartCount();
                window.location = `/ex/${getters.lang}/cart`;
                // window.showCartAdd();
                resolve();
            })
            .catch(function (error) {
                reject(error.response);
            });
    });
};
