<style>
  .pagelist li {margin:5px 0px} .pagelist li a.current, .pagelist li a:hover {font-weight:bold}
</style>
<div>
  $Content.RAW  
</div>
<table>
  <tr>
    <td>
      <% include BpPage_Pagetree %>
    </td>
    <td>
      <div style='padding:30px'>
        <% if record.Title %>
          <h1 class='pagetitle'>
            Page: $record.Title
          </h1>
          <a href='#' class='button mwlink pagelinkbutton' mwlink='$record.MwLink'><span class='tinyicon ui-icon-circle-arrow-e'></span>
			<% _t('choosepage','link to this page') %></a>
          <% else %>
            <% _t( 'pleasechoose', 'please choose a page from the page-tree on your left') %>
          <% end_if %>
      </div>
    </td>
  </tr>
</table>
<script>
	

	$(document).ready(function() {

	  $("a.mwlink").live('click', function(e) {
	    e.preventDefault();
	    var mwlink = $(this).attr('mwlink');
 		parent.setMwLink(mwlink);

	  });

      $('.pagetitle')[0].scrollIntoView();

	});
</script>
