
<h4><b>Ihre Bestellung <% if OrderNr %># $OrderNr<% end_if %>:</b></h4>




<div>$BillingFirstname $BillingLastname</div>
<div>$BillingCompany</div>        

<div style='padding:4px 0px'>
    <div><strong>$BillingStreet</strong></div>
    <div><strong>$BillingZip $BillingCity</strong></div>
    <div><strong>$BillingCountry_Str</strong></div>
</div>

<div>    $BillingFon</div>
<div>    $BillingEmail</div>


<% with  CartData4Template %>



    <div>&nbsp;</div>    
    <table width="100%" >
        <tr id='cartitem-$key'>
            <td style='border-bottom:1px solid #222;padding:4px;font-family:arial,serif;font-size:12px'>Menge</td>
            <td style='border-bottom:1px solid #222;padding:4px;font-family:arial,serif;font-size:12px'>Bezeichnung</td>
            <td style='border-bottom:1px solid #222;padding:4px;font-family:arial,serif;font-size:12px'>Artikel-#</td>
            <td style='border-bottom:1px solid #222;padding:4px;font-family:arial,serif;font-size:12px'  align='right'><span >EUR / Stk.</span></td>
            <td style='border-bottom:1px solid #222;padding:4px;font-family:arial,serif;font-size:12px'  align='right'><span >EUR / Gesamt</span></td>
        </tr>
        <% loop items %>
        <tr id='cartitem-$key'>
            <td style='padding:4px;font-family:arial,serif;font-size:12px'>$amount</td>
            <td style='padding:4px;font-family:arial,serif;font-size:12px'>$product_title <i>$variant_title</i>
             <a href="https://engine.slide-it.net/editor/$did" title="design bearbeiten"><div style="background-image:url('https://engine.slide-it.net/editor/png/$did')" class="designpreview"></div></a></td>
            <td style='padding:4px;font-family:arial,serif;font-size:12px'>$variant_number</td>
            <td style='padding:4px;font-family:arial,serif;font-size:12px'  align='right'><span >$singleprice_str</span></td>
            <td style='padding:4px;font-family:arial,serif;font-size:12px'  align='right'><span >$price_str</span></td>
        </tr>
        <% end_loop %>

        
        <% if  versandkosten %>
        <tr>
            <td style='border-top:2px solid #222;padding:4px;font-family:arial,serif;font-size:12px' colspan="1">&nbsp;</td>
            <td style='border-top:2px solid #222;padding:4px;font-family:arial,serif;font-size:12px' colspan="2">Summe Brutto:</td>
            <td style='border-top:2px solid #222;padding:4px;font-family:arial,serif;font-size:12px' align="right">EUR</td>
            <td style='border-top:2px solid #222;padding:4px;font-family:arial,serif;font-size:12px' align='right'><span >$summebrutto_str</span></td>
        </tr>

        <tr>
            <td style='padding:4px;font-family:arial,serif;font-size:12px' colspan="1">&nbsp;</td>
            <td style='padding:4px;font-family:arial,serif;font-size:12px' colspan="2">Versand/Verpackung Brutto:</td>
            <td style='padding:4px;font-family:arial,serif;font-size:12px' align="right">EUR</td>
            <td style='padding:4px;font-family:arial,serif;font-size:12px' align='right'><span >$versandkosten_str</span></td>
        </tr>
        
        <% end_if %>
      
        
        <tr>
            <td style='border-top:1px solid #222;padding:4px;font-family:arial,serif;font-size:12px' colspan="1">&nbsp;</td>
            <td style='border-top:1px solid #222;padding:4px;font-family:arial,serif;font-size:12px' colspan="2"><strong>Gesamtbetrag Brutto:</strong></td>
            <td style='border-top:1px solid #222;padding:4px;font-family:arial,serif;font-size:12px' align="right">EUR</td>
            <td style='border-top:1px solid #222;padding:4px;font-family:arial,serif;font-size:12px' align='right'><span ><strong>$gesamtbrutto_str</strong></span></td>
        </tr>


        <tr>
            <td style='padding:4px;font-family:arial,serif;font-size:10px' colspan="1">&nbsp;</td>
            <td style='padding:4px;font-family:arial,serif;font-size:10px' colspan="2">Enthaltene Ust 20.00 %</td>
            <td style='padding:4px;font-family:arial,serif;font-size:10px' align="right">EUR</td>
            <td style='padding:4px;font-family:arial,serif;font-size:10px' align='right'><span >$steueranteil_str</span></td>
        </tr>

        <tr>
            <td style='padding:4px;font-family:arial,serif;font-size:10px' colspan="1">&nbsp;</td>
            <td style='padding:4px;font-family:arial,serif;font-size:10px' colspan="2">Gesamtbetrag Netto:</td>
            <td style='padding:4px;font-family:arial,serif;font-size:10px' align="right">EUR</td>
            <td style='padding:4px;font-family:arial,serif;font-size:10px' align='right'><span >$gesamtnetto_str</span></td>
        </tr>

    


    </table>

    <div>&nbsp;</div>
    
<% end_with %>

<div>&nbsp;</div>



<div><strong>Zustellvariante</strong>: $DeliveryType_Str</div>
<div><strong>Bezahlmethode</strong>: $PaymentType_Str</div>

<% if  PaymentType=='vorkasse' %>
<div>&nbsp;</div>
Bitte überweisen Sie den Gesamtbetrag von EUR <strong>$gesamtbrutto_str</strong> auf folgendes Konto:
<div>&nbsp;</div>
Empfänger: <strong>derdoppelstock.at</strong><br>
Verwendungszweck: <strong>Bestellung $OrderNr</strong><br>
BLZ: <strong>38460</strong><br>
Konto: <strong>10400547</strong><br>
BIC: RZSTAT2G460<br>
IBAN: AT743846000010400547<br>
<div>&nbsp;</div>
Eventuell auftretende Überweisungskosten aus nicht EU-Ländern müssen vom Käufer übernommen werden!
<div>&nbsp;</div>
<% end_if %>


<div>&nbsp;</div>


<% if UseDeliveryAdress %>
        
<h4><b>Zustellung an:</b></h4>

<div>&nbsp;</div>

<div>$DeliveryFirstname $DeliveryLastname</div>
<div>$DeliveryCompany</div>        
<div style='padding:4px 0px'>
    <div><strong>$DeliveryStreet</strong></div>
    <div><strong>$DeliveryZip $DeliveryCity</strong></div>
    <div><strong>$DeliveryCountry_Str</strong></div>
    
</div>
<div>$DeliveryFon</div>
<div>&nbsp;</div>



<% end_if %>




