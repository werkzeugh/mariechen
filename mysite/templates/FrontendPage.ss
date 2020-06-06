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
    <meta property="og:site_name" content="Mariechen">
    <meta property="og:image" content="$OgImageUrl">

    <link rel="shortcut icon" href="/favicon.ico?v=2" />

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-31486823-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
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

        <section class="topheader topcontainer">
            <div class="ce-outer">
                <div class="ce-inner">

                    <div class="logo">
                        <img src="/mysite/svg/marienapo_star_red.svg">
                    </div>
                    <div>
                        Powered by Marien Apotheke Wien
                    </div>
                    <a href="" class="aboutlink">Über Mariechen</a>
                    <a href="">Anmelden</a>
                    <a href="">Kontakt</a>
                </div>
        </section>
        <section class="mainheader topcontainer">
            <div class="ce-outer">
                <div class="ce-inner">
                    <div class="logo">
                        <img src="/mysite/images/mariechen_logo.png">
                        <div class="claim">
                            Besser leben mit <strong>Mag. pharm Karin Simonitsch</strong>
                        </div>
                    </div>
                    <div class="carticon">
                        <img src="/mysite/images/cart_icon.png">
                        <div class="claim">
                            Warenkorb (0)
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </header>




    <% end_with %>
    <div class="content-container">

        <div role="main" id="main-content" class="main-content <% if  hasNavCol %>has-navcol<% end_if %>">

            $Layout

        </div>
    </div>


    <% with  getFooter %>

    <footer>



    </footer>



    <% end_with %>


    <% end_cached %>
</body>

</html>
