import axios from "axios";
import {
    array_get
} from "../../../utils.js";

const R = require("ramda");

export const fetchTagsForType = ({
    commit,
    /*state,*/
    rootGetters
}, type) => {
    console.log('#log getTagsForType', type, rootGetters["liveApiUrl"]);

    return new Promise((resolve, reject) => {
        axios
            .get(rootGetters["liveApiUrl"] + "/get_tags_for_types?types=" + type)
            .then(function (response) {
                // console.log('#log', response);
                resolve(response.data.payload[type]);
            })
            .catch(function (error) {
                console.error(error);

                reject(error.response);
            });
    });
};

export const getTagsForString = ({
    commit,
    /*state,*/
    rootGetters
}, tag_string) => {
    console.log('#log getTagsForString', tag_string, rootGetters["liveApiUrl"]);

    return new Promise((resolve, reject) => {
        axios
            .post(rootGetters["liveApiUrl"] + "/get_tags_for_string", {
                tag_string
            })
            .then(function (response) {
                // console.log('#log', response);
                resolve(response.data.payload);
            })
            .catch(function (error) {
                console.error(error);

                reject(error.response);
            });
    });
};


export const setTagsForRecord = ({
    commit,
    /*state,*/
    rootGetters
}, {
    record,
    tag_string
}) => {

    return new Promise((resolve, reject) => {
        axios
            .post(rootGetters["liveApiUrl"] + "/set_tags_for_record/" + record, {
                tag_string
            })
            .then(function (response) {
                // console.log('#log', response);
                resolve(response.data.payload);
            })
            .catch(function (error) {
                console.error(error);

                reject(error.response);
            });
    });
};
export const getTagsForRecord = ({
    commit,
    /*state,*/
    rootGetters
}, record) => {
    console.log('#log getTagsForRecord', record, rootGetters["liveApiUrl"]);

    return new Promise((resolve, reject) => {
        axios
            .get(rootGetters["liveApiUrl"] + "/get_tags_for_record/" + record)
            .then(function (response) {
                // console.log('#log', response);
                resolve(response.data.payload);
            })
            .catch(function (error) {
                console.error(error);

                reject(error.response);
            });
    });
};
