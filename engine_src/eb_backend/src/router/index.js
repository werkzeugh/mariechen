import Vue from "vue";
import VueRouter from "vue-router";
import TagEditor from "../components/TagEditor";
import TagViewer from "../components/TagViewer";
import RowSorter from "../components/RowSorter";


const make_router = function (widgetProps) {
    return new VueRouter({
        linkActiveClass: "is-active",
        mode: 'abstract',
        routes: [{
                path: "/edit",
                component: TagEditor,
                name: "eb-tag-editor",
                // props: widgetProps

            },
            {
                path: "/view",
                component: TagViewer,
                name: "eb-tag-viewer",
                // props: widgetProps

            },
            {
                path: "/rowsorter",
                component: RowSorter,
                name: "eb-row-sorter",
                props: widgetProps

            },
        ]
    });

};

export default make_router;
