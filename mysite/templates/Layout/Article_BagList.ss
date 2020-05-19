<div class="main-row">


  <div class="nav-col">
    <div id="section-nav">
    
      <% loop ShopCategories %>
      <a href="$Link" class="$CssClass link-{$URLSegment}">$MenuTitle</a>
      
      <% if  $CssClass=='active' %>
      
          <% if SubTypes%>
          
          <div class="submenu $CssClass">
            <% loop SubTypes %>
            <% if $Count>0  %>
            <a href="$Link" class="$CssClass">$MenuTitle <span class="count">($Count)</span></a>
            <% end_if %>
            <% end_loop %>
          </div>
          <% end_if %>
      <% end_if %>      

      <% end_loop %>

    </div>
  </div>
  <div class="main-col">

    <div class="ce ce-text">
      <div class="ce-outer">
        <div class="ce-inner">
          <div class="typography">
            <h2>$Title$AddonTitle</h2>
          </div>
        </div>
      </div>
    </div>
    <% loop getArticleElements %>

    $getHTML('maincontent')

    <% end_loop %>
  </div>
</div>

<script>
  jQuery(document).ready(function ($) {
    var navcol = $('.nav-col')[0];
    var section_div = document.querySelector("#section-nav");

    function setFixedColumnWidth() {
      let parentWidth2 = navcol.offsetWidth;
      section_div.style.maxWidth = parentWidth2 + 'px';
    };

    setFixedColumnWidth();
    window.addEventListener('resize', setFixedColumnWidth);

  });
</script>
