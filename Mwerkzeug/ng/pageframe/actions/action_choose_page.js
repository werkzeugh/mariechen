define(function() {
  return angular.module('coreModule').registerController('action_choose_page_controller', function($scope, topScope, params, $q) {
    var commandArgs, doChoosePage, doShowModal, main, orFail;
    if (window.console && console.log) {
      console.log("action_choose_page_controller:", params);
    }
    orFail = topScope.app.failPopup;
    params.topScope = topScope;
    commandArgs = {
      pageId: params.pageId
    };
    main = function() {
      return topScope.callPageManager('getNodeData', commandArgs).then(doShowModal, orFail);
    };
    doShowModal = function(nodeData) {
      params.page = nodeData;
      return topScope.app.showModal('ChoosePage', params).then(doChoosePage);
    };
    doChoosePage = function(data) {
      var mwlink;
      if (data === 'ok') {
        if (window.console && console.log) {
          console.log("choose page", null);
        }
        mwlink = "mwlink://SiteTree-" + params.pageId;
        return params.onChoose.resolve(mwlink);
      }
    };
    return main();
  });
});
