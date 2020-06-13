import Vue from "vue";
import VueRouter from "vue-router";
import ImgFolder from "../components/ImgFolder";
const make_router = function (widgetProps) {
  return new VueRouter({
    linkActiveClass: "is-active",
    mode: "abstract",
    routes: [{
      path: "/imgfolder",
      component: ImgFolder,
      name: "vbe-imgfolder",
      props: widgetProps
    }, ]
  });
};

export default make_router;
