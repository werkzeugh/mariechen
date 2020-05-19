<div ng-init="showHelp=false">
  <a ng-click="showHelp=!showHelp" href="javascript:void(0)"><i class="fa fa-chevron-{{showHelp?'down':'right'}}"></i> 
    verfügbare Platzhalter im Text:</a>


    <div ng-show="showHelp" class="well">

      <table>
        <tr valign="top">
          <td>
            <b>Person:</b><div>&nbsp;</div>
            <% loop PersonFieldsForPlaceholderHelp %>
              #$Key#<br>
            <% end_loop %>
            <div>&nbsp;</div>
            "#MainPerson.Feldname#" anstelle verwenden, um bei Begleitpersonen auf die Daten der eingeladenen Person zuzugreifen)
            <div>&nbsp;</div>
          </td>
          <td>
            <b>Event:</b><div>&nbsp;</div>
            #Event.Name#<br>
            #Event.SubTitle#<br>
          </td>
        </tr>
      </table>
      <div>&nbsp;</div>
      <b>Links:</b><div>&nbsp;</div>

      <% loop LinksForPlaceholderHelp %>
          #Link.$Key# : $Title<br>
      <% end_loop %>


    </div>


  </div>
