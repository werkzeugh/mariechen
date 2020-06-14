import Vue from "vue";
import App from "./App.vue";
import MultiVue from "vue-multivue";

import make_router from "./router";
import store from "./store";

import VueRouter from "vue-router";
const R = require("ramda");

Vue.config.productionTip = false;

Vue.use(VueRouter);

let querySelector = ".vueapp-vbe";
const appElements: HTMLElement[] = Array.from(
  document.querySelectorAll(querySelector)
);
const instances: Vue[] = [];

const camelizeRE = /-(\w)/g;
export const camelize = (string: string) => {
  return string.replace(camelizeRE, (_, c) => (c ? c.toUpperCase() : ""));
};

if (appElements.length > 0) {
  for (const appEl of appElements) {
    let widgetProps = R.reduce(
      (acc: object, item: Attr) => ({
        ...acc,
        [camelize(item.name)]: item.value,
      }),
      {}
    )(appEl.attributes);

    let childData: object[] = [];
    for (let node of appEl.children) {
      childData.push(
        R.reduce(
          (acc, item: Attr) => ({
            ...acc,
            [item.name]: item.value,
          }),
          {}
        )(node.attributes)
      );
    }

    widgetProps.childNodes = childData;

    let vueInstance = new Vue({
      render: (h) => h(App),
      el: appEl,
      data: {
        tagName: null as string | null,
        attributes: {},
      },
      beforeMount: function() {
        this.tagName = this.$el.tagName.toLowerCase();
      },
      router: make_router(widgetProps),
      store,
    });

    instances.push(vueInstance);
  }
}
