import Vue from "vue";
import VueRouter from "vue-router";
import ImageList from "../components/ImageList";
import ThumbnailList from "../components/ThumbnailList";
import Variants from "../components/Variants";

const make_router = function (widgetProps) {
    return new VueRouter({
        linkActiveClass: "is-active",
        mode: 'abstract',
        routes: [{
                path: "/images",
                component: ImageList,
                name: "bagdetail-imagelist",
                props: widgetProps
            }, {
                path: "/variants",
                component: Variants,
                name: "bagdetail-variants",
                props: widgetProps
            },
            {
                path: "/thumbnails",
                component: ThumbnailList,
                name: "bagdetail-thumbnails",
                props: widgetProps
            },


        ]
    });

};

export default make_router;
