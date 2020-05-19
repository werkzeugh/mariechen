// import * as R from "ramda";
import Vue from "vue";

export const setTagsForType = (state, {
    type,
    tags
}) => {
    state.allTags[type] = tags;
};

export const incVersion = state => state.version++;
