const R = require("ramda");

export const devMode = process.env.NODE_ENV !== "production";

export const devOfflineMode = false;
import ModalNotification from "./components/ModalNotification";

/**
 *  replicates array_get feature from laravel
 */
export const array_get = function(i, k, d) {
    if (typeof d === "undefined") {
        d = null;
    }
    if (!k) return i;

    var s = k.split(".");

    var o = i;

    for (var x = 0; x < s.length; x++) {
        if (null !== o && typeof o !== "undefined" && o.hasOwnProperty(s[x])) {
            o = o[s[x]];
        } else {
            return d;
        }
    }

    return o;
};

export const parseErrorsFromResponse = function(res) {
    let errors = [];

    if ((res || {}).data) {
        let data = res.data;
        if ((data || {}).errors) {
            R.map(i => {
                if (typeof i === "string" || i instanceof String) {
                    errors.push({
                        type: "modal",
                        title: "Error",
                        class: "is-danger",
                        text: i
                    });
                } else {
                    errors.push({
                        type: "snackbar",
                        class: "is-danger",
                        text: i[0]
                    });
                }
            }, data.errors);
        }
        if ((data || {}).message) {
            errors.push({
                type: "toast",
                class: "is-danger",
                text: res.message
            });
        }
    } else {
        errors.push({
            type: "toast",
            class: "is-warning",
            text: "error while sending request"
        });
    }
    return errors.length ? errors : null;
};

export const displayNotifications = function(component, notifications) {
    if (notifications !== null) {
        notifications.map(e => {
            if (e.type == "toast") {
                component.$toast.open({
                    duration: 3000,
                    message: e.text,
                    type: e.class,
                    position: "is-top",
                    queue: false
                });
            } else if (e.type === "snackbar") {
                component.$snackbar.open({
                    message: e.text,
                    type: e.class,
                    position: "is-top",
                    actionText: "ok",
                    indefinite: true,
                    queue: true
                });
            } else if (e.type === "modal") {
                component.$modal.open({
                    parent: component,
                    component: ModalNotification,
                    hasModalCard: true,
                    props: { data: e },
                    canCancel: ["outside", "escape"]
                });
            }
        });
    }
};

export const handleErrorsFromResponse = function(component, res) {
    console.log("#handleErrorsFromResponse", res);
    displayNotifications(component, parseErrorsFromResponse(res));
};

export const handleNotificationsFromResponse = function(component, res) {
    displayNotifications(component, array_get(res, "data.notifications", []));
};

export const parseRolesFromToken = function(token) {
    var base64Url = token.split(".")[1];
    var base64 = base64Url.replace("-", "+").replace("_", "/");
    let data = JSON.parse(window.atob(base64));
    return array_get(data, "roles", []);
};

/********************************************************
Name: str_to_color
Description: create a hash from a string then generates a color
Usage: alert('#'+str_to_color("Any string can be converted"));
author: Brandon Corbin [code@icorbin.com]
website: http://icorbin.com
********************************************************/

export const stringToColor = function(str, prc) {
    // Check for optional lightness/darkness
    var prc = typeof prc === "number" ? prc : -10;

    // Generate a Hash for the String
    var hash = function(word) {
        var h = 0;
        for (var i = 0; i < word.length; i++) {
            h = word.charCodeAt(i) + ((h << 5) - h);
        }
        return h;
    };

    // Change the darkness or lightness
    var shade = function(color, prc) {
        var num = parseInt(color, 16),
            amt = Math.round(2.55 * prc),
            R = (num >> 16) + amt,
            G = ((num >> 8) & 0x00ff) + amt,
            B = (num & 0x0000ff) + amt;
        return (
            0x1000000 +
            (R < 255 ? (R < 1 ? 0 : R) : 255) * 0x10000 +
            (G < 255 ? (G < 1 ? 0 : G) : 255) * 0x100 +
            (B < 255 ? (B < 1 ? 0 : B) : 255)
        )
            .toString(16)
            .slice(1);
    };

    // Convert init to an RGBA
    var int_to_rgba = function(i) {
        var color =
            ((i >> 24) & 0xff).toString(16) +
            ((i >> 16) & 0xff).toString(16) +
            ((i >> 8) & 0xff).toString(16) +
            (i & 0xff).toString(16);
        return color;
    };

    return "#" + shade(int_to_rgba(hash(str)), prc);
};

export const getUTCDate = dateString => {
    if (!dateString) {
        return null;
    }
    const date = new Date(dateString);

    return new Date(
        date.getUTCFullYear(),
        date.getUTCMonth(),
        date.getUTCDate(),
        date.getUTCHours(),
        date.getUTCMinutes(),
        date.getUTCSeconds()
    );
};

// function trim(stringToTrim) {
//     return stringToTrim.replace(/^\s+|\s+$/g, "");
// }
// function ltrim(stringToTrim) {
//     return stringToTrim.replace(/^\s+/, "");
// }
// function rtrim(stringToTrim) {
//     return stringToTrim.replace(/\s+$/, "");
// }
