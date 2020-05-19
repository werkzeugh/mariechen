  <div class="taggable-form">

      <table>
          <tr>
              <td>add Tags:</td>
              <td>
                  <input type="hidden" name="add_tags" id="input_AddTags">
                  <eb-tag-editor class="vueapp-eb_backend" types='$AllTagTypes' ref_id='input_AddTags'>
                  </eb-tag-editor>
              </td>
          </tr>
          <td>remove Tags:</td>
          <td>

              <input type="hidden" name="remove_tags" id="input_RemoveTags">
              <eb-tag-editor class="vueapp-eb_backend" types='$AllTagTypes' ref_id='input_RemoveTags'>
              </eb-tag-editor>

          </td>
          </tr>
      </table>
      <div>&nbsp;</div>

      <button type="submit" class="btn btn-primary">apply</button>
  </div>

  <script>
      jQuery(document).ready(function ($) {

          var countCheckedItems = function () {
              return $('.taggable-cb:checked').length;
          };

         $('.taggable-items').on('change','.taggable-toggle',function() {
         console.log('#log 6500');
            if ($(this).is(":checked")) {
              $('.taggable-cb', '.taggable-items').attr('checked', '1');
              toggleTagForm();
            } else {
              $('.taggable-cb', '.taggable-items').removeAttr('checked');
            }
          });


        


          $('.taggable-items').on('change', '.taggable-cb', function () {
              toggleTagForm();
          });

          $('.taggable-items').on('click', '.taggable-cb-td', function (e) {
              if (e.srcElement.tagName == 'TD') {
                  $('.taggable-cb', this).trigger('click');
              }
          });



          var toggleTagForm = function () {
              var n = countCheckedItems();
              var tagForm = $('.taggable-form')
              if (n > 0) {
                  tagForm.show();
              } else {
                  tagForm.hide();
              }
          }

      });
  </script>
  <style>
      .taggable-cb-td:hover {
          background: yellow;
      }

      .taggable-cb-td {
          max-width: 30px;
      }

      .taggable-form {
          display: none;
      }
  </style>
