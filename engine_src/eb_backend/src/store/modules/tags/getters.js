// import { array_get } from "../../../utils.js";

// import * as R from "ramda";

export const allTags = state => state.allTags;
export const version = state => state.version;

export const getTagsForType = state => (type) => {
    const ret = state.allTags[type];
    if (!ret) {
        return null;
    } else {
        return ret;
    }
};
