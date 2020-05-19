<div style="position:absolute;z-index:10000;width:100%;">
    <div style="width:600px;margin:20px auto;background:white;color:black;border:4px solid red;padding:40px">
        <h1>Ihr Web-Browser wird leider nicht mehr unterstützt</h1>

        <div>&nbsp;</div>

        Verwenden Sie bitte eine aktuelle Internet-Explorer-Version ( ab Version 8)
        oder einen anderen modernen Web-Browser.

        <div>&nbsp;</div>

        <% if  HostmasterEmail %>  
        <div>
            <small>Falls Sie einen modernen Browser verwenden, und diese Meldung dennoch angezeigt wird,
                senden Sie uns Bitte ein e-Mail mit folgendem Text:
            </small>

            <div>&nbsp;</div>
            <div style="font-family:monospace">$ServerString</div>
            <div>&nbsp;</div>

            <small>
                an: $HostmasterEmail
            </small>
        </div>
        <% end_if %>
    </div>
</div>
