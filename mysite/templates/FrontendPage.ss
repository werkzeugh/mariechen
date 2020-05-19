<!doctype html>

<!--[if lt IE 7]><script src="/mysite/thirdparty/ie_upgrade_warning/warning.js"></script><script>window.onload=function(){e("/mysite/thirdparty/ie_upgrade_warning/")}</script><![endif]-->

<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="en">
<!--<![endif]-->

<head>
    <meta charset="utf-8">
    <title>$HTMLTitle</title>
    <meta name="viewport" content="width=device-width">
    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="320">
    <meta name="Description" content="$getTranslated('Config_MetaDescription')">

    <meta property="og:title" content="$getTranslated('Config_GoogleTitle')">
    <meta property="og:description" content="$getTranslated('Config_MetaDescription')">
    <meta property="og:url" content="$AbsoluteLink">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="EVA BLUT Online Shop">
    <meta property="og:image" content="$OgImageUrl">

    <link rel="shortcut icon" href="/favicon.ico?v=2" /> 

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-31486823-1"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-31486823-1');
    </script>

    <script src="https://kit.fontawesome.com/22b173a768.js"></script>

    <!--[if IE 7]>
        <link rel="stylesheet" href="/Mwerkzeug/thirdparty/font-awesome/css/font-awesome-ie7.min.css" >
        <![endif]-->
    $HeadAddon.RAW

</head>

<body
    class='$PageCssClasses page-$ClassName page-{$Level(2).URLSegment} mysite lang-$CurrentLanguage page-$LightOrDark'>

<% cached 'page', ID, myLastEdited, Hostname, MasterCacheKey %>  
$CacheRequirements.RAW



    <% with  getHeader %>

    <header role="banner" id="top">
        <div class="header-outer">
            <div class="header-inner">
                <div class="logo">
                    <a href="$Top.CurrentSite.Link" title="Home"><img
                            src="/mysite/images/eva_blut_on_{$Top.LightOrDark}.svg"></a>
                </div>

                <% if  $Top.SiteSection=='shop'  %>

                    <div class="d-sm-none">
                        <a href="/ex/{$Top.CurrentLanguage}/cart/" title="cart">
                            <span class="fa-layers">
                                <i class="fal fa-lg fa-shopping-bag"></i>
                                <span id="cart-count" class="cart-count fa-layers-text" data-fa-transform="shrink-6 down-2 right-1" style="font-weight:900"></span>
                            </span>
                        </a>
                    </div>
                <div class="search d-none d-sm-block">
                    <label for="keyword">Suchen</label>
                    <input type="text" name="keyword">
                </div>
                <div class="icons d-none d-sm-block">


                    <div class="currencypicker">
                        <i class='fal fa-fw fa-euro-sign' id="currencylink"></i>
                        <div class="chooser" id="currencychooser">
                            <i class='fal fa-fw fa-dollar-sign'></i>
                            <i class='fal fa-fw fa-yen-sign'></i>
                        </div>
                    </div>

 <!--                   <i class='fal fa-lg fa-user-circle'></i>
                    <i class='fal fa-lg fa-heart'></i> -->
                    

                    <a href="/ex/{$Top.CurrentLanguage}/cart/" title="cart">
                        <span class="fa-layers">
                            <i class="fal fa-lg fa-shopping-bag"></i>
                            <span id="cart-count" class="cart-count fa-layers-text" data-fa-transform="shrink-6 down-2 right-1" style="font-weight:900"></span>
                        </span>
                    </a>

                    <div id="cart-infobox" style="display:none">
                        <p>
                            $Top.trans("item was added to cart","das Produkt befindet sich nun im Warenkorb")
                        </p>
                        <a class="btn btn-primary" href="/ex/{$Top.CurrentLanguage}/cart/">$Top.trans("view cart","zum Warenkorb")</a>
                    </div>


                </div>
                <% end_if %>
                <div class="langchooser d-none d-sm-block">
                    <a href="/de{$Top.PlainLink}" class="link-de">de</a>
                    <a href="{$Top.PlainLink}" class="link-en">en</a>
                </div>
                <div class="burger d-sm-none">
                    <a href="javascript:void(0);" class="icon" id="burgerlink"><i class='fal fa-bars fa-lg'></i></a>
                </div>
            </div>

        </div>
    </header>
    <nav role='navigation' id="pagenav">

        <% loop  $Top.TopNavItems %>
        <% if $IsCurrentNav %> <span class="active nav-$URLSegment current-content" data-key="$URLSegment"><a
                href="$Link">$MenuTitle</a></span>
        <% else %>
        <a class="not-active nav-$URLSegment " data-key="$URLSegment" href="$Link"><span>$MenuTitle
            </span></a>
        <% end_if %>
        <% end_loop %>
        <div class="mobile-langchooser d-sm-none">
                <a href="/de{$Top.PlainLink}" class="link-de">de</a>
                <a href="{$Top.PlainLink}" class="link-en">en</a>
        </div>

    </nav>



    <% end_with %>
    <div class="content-container">

        <div role="main" id="main-content" class="main-content <% if  hasNavCol %>has-navcol<% end_if %>">

            $Layout

        </div>
    </div>


    <% with  getFooter %>

    <footer>

        <div class="footer-outer">
            <div class="footer-inner">
                <div class="columns">
                    <div class="col1">
                        <% loop Col1NavItems %>
                        $getHTML().RAW
                        <% end_loop %>
                    </div>
                    <div class="col2">
                        <% loop Col2NavItems %>
                        $getHTML().RAW
                        <% end_loop %>
                    </div>
                    <div class="col3">
                        <% loop Col3NavItems %>
                        $getHTML().RAW
                        <% end_loop %>
                    </div>
                    <div class="col4">
                        <% loop Col4NavItems %>
                        $getHTML().RAW
                        <% end_loop %>
                    </div>
                </div>

                <div class="bottomnav">
                    <% loop getBottomNavItems %>
                    $getHTML().RAW
                    <% end_loop %>

                </div>

            </div>
        </div>

    </footer>



    <% end_with %>


<% end_cached %>
</body>

</html>
