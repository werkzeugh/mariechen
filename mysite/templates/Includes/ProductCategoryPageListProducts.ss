<style>
    #main_savelink {
        display: none
    }
    .is-hidden {
     opacity:0.5;
    }
    .zero {
     font-weight:bold;
     color:red;
     border:1px solid red;
     padding:0.2em .5em;
     display:inline-block;
     border-radius:4px;
    }
</style>


OOO
<div>&nbsp;</div>

<form method="POST">

 <eb-row-sorter class="vueapp-eb_backend" name="SortedIds"> </eb-row-sorter>


    <div class="list-search">
        <div>&nbsp;</div>
        <input type="text" class="table-filter form-control" data-table="productlist"
            placeholder="Filter..." />
        <div>&nbsp;</div>
    </div>
    <table class="productlist table table-bordered table-striped taggable-items" id="productlist"> 

        <thead>
            <tr>
                <th><input type="checkbox" class="taggable-toggle" name="taggable_toggle" value=""></th>
                <th>Nr.</th>
                <th>Name</th>
                <th>Preis</th>
                <th>Tags</th>
                <th></th>
            </tr>
        </thead>
        <tbody class="js-sortable">
            <% loop Variants %>
    
            <tr class="js-sortable-tr" data-id="$ID">
                <td class="taggable-cb-td">
                    <input type="checkbox" class="taggable-cb" name="taggable_ids[]" value="SiteTree-$ID">
                </td>
                <td>
                $ProductNr
                </td>
                <td>
                    <a href="$EditLink" class="<% if  $Hidden %>is-hidden<% end_if %>">$Title</a>
                    <div>
                        $Title_de 
                    </div>
                </td>
                <td>
                   $Price
                </td>
                <td>
    
                    <eb-tag-viewer class="vueapp-eb_backend" record="SiteTree-$ID" editable="1"
                        types="$Top.AllTagTypes">
                    </eb-tag-viewer>
    
                </td>
                
                <td class="js-sortable-handle">
                    <i class='fa fa-bars fa-lg'></i>
                </td>
            </tr>
            <% end_loop %>
        </tbody>
    </table>

<% include Tagabble_Items_Form %>

  
</form>
<style>
.imagelist img { 
    border:2px solid transparent;
    max-height:80px;
}
.imagelist img.is-list-image { 
  border-color:grey;
}

.productlist {
    width:auto;
    min-width:700px;
}

</style>


<script>
(function () {
    'use strict';

    var TableFilter = (function () {
        var Arr = Array.prototype;
        var input, visible, resultinfo, resultinfo_value, newclass;

        function onInputEvent(e) {
            visible = 0;
            input = e.target;
            var table1 = document.getElementsByClassName(input.getAttribute('data-table'));
            Arr.forEach.call(table1, function (table) {
                Arr.forEach.call(table.tBodies, function (tbody) {
                    Arr.forEach.call(tbody.rows, filter);
                });
            });
            resultinfo_value.textContent = visible;

            if (input.value == '') {
                newclass = 'result-all';
            } else if (visible == 0) {
                newclass = 'result-zero';
            } else if (visible == 1) {
                newclass = 'result-single';
            } else {
                newclass = 'result-multiple';
            }

            if (!resultinfo.classList.contains(newclass)) {
                removeClassByPrefix(resultinfo, "result-");
                resultinfo.classList.add(newclass);
            }

        }

        function filter(row) {
            var text = row.textContent.toLowerCase();
            //console.log(text);
            var val = input.value.toLowerCase();
            //console.log(val);
            if (text.indexOf(val) === -1) {
                row.style.display = 'none';
            } else {
                row.style.display = 'table-row';
                visible++;
            }

        }

        function removeClassByPrefix(node, prefix) {
            var regx = new RegExp('\\b' + prefix + '[^ ]*[ ]?\\b', 'g');
            node.className = node.className.replace(regx, '');
            return node;
        }

        return {
            init: function () {
                var inputs = document.getElementsByClassName('table-filter');
                resultinfo = document.querySelector(".resultinfo");
                resultinfo_value = document.querySelector(".resultinfo_value");
                Arr.forEach.call(inputs, function (input) {
                    input.oninput = onInputEvent;
                });
            }
        };

    })();


    TableFilter.init();
})();
</script>
