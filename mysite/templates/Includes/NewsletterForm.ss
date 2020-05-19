 <section class="ce ce-text  align-$Alignment $getCssClassString" style="$getCssStyleString">
     <div class="ce-outer">
         <div class="ce-inner" id="subscr_nl">

             <!-- Begin MailChimp Signup Form -->
             <div class="newsletter_channel">
                 <span id="list3"><i class='fal fa-square fa-fw
                    '></i><i class='fal fa-check-square fa-fw'></i>&nbsp;Sympathisants</span>
                 <span id="list1"><i class='fal fa-square fa-fw
                    '></i><i class='fal fa-check-square fa-fw'></i>&nbsp;Buyers</span>
                 <span id="list2"><i class='fal fa-square fa-fw
                    '></i><i class='fal fa-check-square fa-fw'></i>&nbsp;Press and editors</span>
             </div>
             <div class="mc_embed_signup" id="chan_3">
                 <form
                     action="https://evablut.us8.list-manage.com/subscribe/post?u=dbfcbd0ce922eda9c0e535130&amp;id=0c8d44f586"
                     method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate"
                     target="_blank" novalidate>
                     <input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address"
                         required>
                     <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                     <div style="position: absolute; left: -5000px;"><input type="text"
                             name="b_dbfcbd0ce922eda9c0e535130_0c8d44f586" tabindex="-1" value=""></div>
                     <button class="mc-embedded-subscribe btn btn-link" type="submit" value=""><i class='fal fa-check fa-2x
                             '></i></button>
                 </form>
             </div>
             <div class="mc_embed_signup" id="chan_2">
                 <form
                     action="https://evablut.us8.list-manage.com/subscribe/post?u=dbfcbd0ce922eda9c0e535130&amp;id=55288cb9d4"
                     method="post" id="mc-embedded-subscribe-form2" name="mc-embedded-subscribe-form" class="validate"
                     target="_blank" novalidate>
                     <input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL2" placeholder="email address"
                         required>
                     <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                     <div style="position: absolute; left: -5000px;"><input type="text"
                             name="b_dbfcbd0ce922eda9c0e535130_55288cb9d4" tabindex="-1" value=""></div>
                     <button class="mc-embedded-subscribe btn btn-link" type="submit" value=""><i class='fal fa-check fa-2x
                             '></i></button>
                 </form>
             </div>
             <div class="mc_embed_signup" id="chan_1">
                 <form
                     action="https://evablut.us8.list-manage.com/subscribe/post?u=dbfcbd0ce922eda9c0e535130&amp;id=7c83b5270d"
                     method="post" id="mc-embedded-subscribe-form3" name="mc-embedded-subscribe-form" class="validate"
                     target="_blank" novalidate>
                     <input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL3" placeholder="email address"
                         required>
                     <div style="position: absolute; left: -5000px;"><input type="text"
                             name="b_dbfcbd0ce922eda9c0e535130_7c83b5270d" tabindex="-1" value=""></div>
                     <button class="mc-embedded-subscribe btn btn-link" type="submit" value=""><i class='fal fa-check fa-2x
                             '></i></button>
                 </form>
             </div>
             <!--End mc_embed_signup-->

         </div>
     </div>
 </section>
 <script>
     jQuery(document).ready(function ($) {

         $("#list1").click(function () {
             $(this).addClass('checked');
             $('#list2, #list3').removeClass('checked');

             $("#chan_1").css('display', 'block');
             $("#chan_2").css('display', 'none');
             $("#chan_3").css('display', 'none');


         });

         $("#list2").click(function () {
             $(this).addClass('checked');
             $('#list1, #list3').removeClass('checked');
             $("#chan_2").css('display', 'block');
             $("#chan_1").css('display', 'none');
             $("#chan_3").css('display', 'none');

         });

         $("#list3").click(function () {
             $(this).addClass('checked');
             $('#list2, #list1').removeClass('checked');

             $("#chan_3").css('display', 'block');
             $("#chan_2").css('display', 'none');
             $("#chan_1").css('display', 'none');

         });


     });
 </script>
