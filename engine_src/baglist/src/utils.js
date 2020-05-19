const R = require('ramda');

export const devMode = process.env.NODE_ENV !== 'production';

export const devOfflineMode = false;

export const augmentApiUrl = function (url) {

  if (devMode) {
    if (devOfflineMode) {
      url = R.replace('https://engine.naturfreunde.at/madb', 'http://engine.naturfreunde.at.localhost/madb', url);
      url = R.replace(/^.*\/madb\/(alle.*)/, '/apicache/$1.json', url);
    }
    url = R.replace('https://madb.naturfreunde.at/NFOEMADB/Autocomplete_TB_Search_PLZ_Ort.ashx', '/apicache/autocomplete_plz.json', url);
  }
  return url;
};

export const rangeValue = function (inValue, inMin, inMax, outMin = 0, outMax = 1) {

  let ref = inValue - inMin;
  const inRange = inMax - inMin;
  const outRange = outMax - outMin;

  if (ref < 0) {
    ref = 0;
  }
  if (ref > inRange) {
    ref = inRange;
  }

  return outMin + (ref / inRange * outRange);

};


var utilsBaseUrl = '/';

export const setUtilsBaseUrl = function (url) {
  utilsBaseUrl = url;
  console.log('#setUtilsBaseUrl', url);
};


export const makeLinkFromPath = function (path) {

  let mypath = null;


  if (_.isArray(path)) {
    mypath = _.flattenDeep(path).filter(x => x !== null).join('/');
  } else {
    mypath = path;
  }

  console.log('#setUtilsBaseUrl makeLinkFromPath', utilsBaseUrl, path, mypath);
  return (utilsBaseUrl + '#/' + mypath).replace('//', '/');

};
