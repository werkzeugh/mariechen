import axios from "axios";


export const fetchItems = ({
  commit,
  /*state,*/
  getters
}, query) => {
  console.log("fetchItems", query);
  commit("setLoading", true);
  commit("setItems", {
    list: [],
    count: null,
    page: 1
  });

  return new Promise((resolve, reject) => {
    axios
      .post(getters["apiUrl"] + "/bags/list", query)
      .then(function (response) {
        console.log("items response", response);
        commit("setItems", response.data.items);
        commit("setLoading", false);
        resolve();
      })
      .catch(function (error) {
        commit("setLoading", false);
        reject(error.response);
      });
  });
};


export const fetchTags = ({
  commit,
  /*state,*/
  getters
}, query) => {
  console.log("fetchTags", query);
  commit("setLoading", true);
  return new Promise((resolve, reject) => {
    axios
      .get(getters["apiUrl"] + "/tags/get_tag_tree_for_types?types=colors,size,material,usage,collection&lang=" + getters.lang)
      .then(function (response) {
        console.log("fetchTags response", response);
        commit("setLoading", false);
        commit("setTags", response.data.payload);
        resolve();
      })
      .catch(function (error) {
        commit("setLoading", false);
        reject(error.response);
      });
  });
};
