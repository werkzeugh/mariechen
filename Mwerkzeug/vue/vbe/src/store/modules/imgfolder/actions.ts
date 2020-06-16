import axios from "axios";
// import {
//     array_get
// } from "../../../utils.js";
// import socket from "../../../socket";

// const R = require("ramda");

export const allFiles = (
  {
    commit,
    /*state,*/
    rootGetters,
  },
  query = {}
) => {
  commit("setAllFiles", []);

  return new Promise((resolve, reject) => {
    axios
      .get(rootGetters["backendBaseUrl"] + "/MwFile_Api/get_files", {
        params: query,
      })
      .then(function(response) {
        commit("setAllFiles", response.data.payload);
        resolve(response);
      })
      .catch(function(error) {
        console.error(error);

        reject(error.response);
      });
  });
};

export const removeFiles = (
  {
    commit,
    /*state,*/
    rootGetters,
  },
  payload
) => {
  return new Promise((resolve, reject) => {
    axios
      .post(rootGetters["backendBaseUrl"] + "/MwFile_Api/remove_files", payload)
      .then(function(response) {
        resolve(response);
      })
      .catch(function(error) {
        console.error(error);
        reject(error.response);
      });
  });
};

export const saveSort = (
  {
    commit,
    /*state,*/
    rootGetters,
  },
  payload
) => {
  return new Promise((resolve, reject) => {
    axios
      .post(rootGetters["backendBaseUrl"] + "/MwFile_Api/sort_files", payload)
      .then(function(response) {
        resolve(response);
      })
      .catch(function(error) {
        console.error(error);
        reject(error.response);
      });
  });
};

export const hideFiles = (
  {
    commit,
    /*state,*/
    rootGetters,
  },
  payload
) => {
  return new Promise((resolve, reject) => {
    axios
      .post(rootGetters["backendBaseUrl"] + "/MwFile_Api/hide_files", payload)
      .then(function(response) {
        resolve(response);
      })
      .catch(function(error) {
        console.error(error);
        reject(error.response);
      });
  });
};

export const unhideFiles = (
  {
    commit,
    /*state,*/
    rootGetters,
  },
  payload
) => {
  return new Promise((resolve, reject) => {
    axios
      .post(rootGetters["backendBaseUrl"] + "/MwFile_Api/unhide_files", payload)
      .then(function(response) {
        resolve(response);
      })
      .catch(function(error) {
        console.error(error);
        reject(error.response);
      });
  });
};
