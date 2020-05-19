define([
'MessageFormat'
], function (MessageFormat) {
// create a global variable
window.MessageFormat = MessageFormat;

// also return the module so it can be used in other AMD modules from here
return MessageFormat;
});
