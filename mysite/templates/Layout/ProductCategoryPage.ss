



<div class="topcontainer">

<h1>$Title</h1>
<div class="product-list">
    
    <% loop Products %>
            <a href="$Link" class="product-box">
                <span class="img"><img src="$getMainImage.SetSize(300,300).Link"></span>
                <span class="content">
                    <span class="title">$Title</span>
                    <span class="shorttext">$ListText</span>
                    <span class="price">$PriceStr</span>
                </span>
                <span class="m-btn">mehr Info</span>
            </a>
    <% end_loop %>
</div>

</div>
