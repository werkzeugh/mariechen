<div id="lagerstand" class="lagerstand" ng-controller="lagerstandMainCtrl">



    <input type="text" class="form-control" ng-model="query.keyword" placeholder="keyword"
    ng-enter="loadProducts()" id="keyword">

    <div class='itemcount'>gefunden: {{items.length}}</div>

    <div ng-show="listStatus=='loading'" class="alert alert-warning"><img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading"></div>

    <div ng-show="listStatus=='empty'" class="alert alert-warning">keine Einträge gefunden</div>
    <table class="table table-bordered table-striped">
        <tr  ng-repeat="i in items">
          <td>
          <a ng-href="{{i.Link}}" target="_blank"><i class="fa fa-chevron-right"></i><strong ng-bind-html="i.Title | highlight:searchTerms">{{i.Title}}</strong></a>
          <div ng-bind-html="i.Keywords | highlight:searchTerms">{{i.Keywords}}</div>
          </td>
          <td class='subitems'>
              <table  class="table table-bordered table-condensed">
                  <tr ng-repeat="si in i.items">
                      <td>{{si.Number}}</td>
                      <td>{{si.Title}}</td>
                      <td>{{si.Price}}</td>
                      <td class='instock'>
                        <div class="form-group " ng-class="(si.saved)?'has-feedback has-success':''">
                          <input type="text" ng-model="si.InStock" class="form-control input-sm" ng-enter="saveValue(i,si)">
                          <span class="fa fa-check form-control-feedback"></span>
                        </div>
                      </td>
                  </tr>
              </table>
          </td>
       </tr>
  </table>

</div>

<script>
    angular.bootstrap(document.getElementById('lagerstand'), ['lagerstand']);
    
</script>
