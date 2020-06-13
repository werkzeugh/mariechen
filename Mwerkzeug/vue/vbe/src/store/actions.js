// import {augmentApiUrl,devOfflineMode} from '../utils';
import axios from 'axios';


export const addToCart = ({
    commit,
    /*state,*/
    getters
}, quantities) => {

    return new Promise((resolve, reject) => {
        axios.post('/cartapi/add_to_cart/', {
                quantities
            })
            .then(function (response) {
                // window.updateCartCount();
                // window.showCartAdd();
                resolve(response.data.payload);
            })
            .catch(function (error) {
                reject(error.response);
            });
    });
};

export const getCartCount = ({
    commit,
    /*state,*/
    getters
}, quantities) => {

    return new Promise((resolve, reject) => {
        axios.get('/cartapi/get_cart_count/')
            .then(function (response) {
                // window.updateCartCount();
                // window.showCartAdd();
                const num = response.data.payload;
                commit('setCartCount', num);
                resolve(num);
            })
            .catch(function (error) {
                reject(error.response);
            });
    });
};
