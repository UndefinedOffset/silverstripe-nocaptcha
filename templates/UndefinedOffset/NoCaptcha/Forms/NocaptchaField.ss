<% if $RecaptchaVersion != 3 %>
    <div class="g-recaptcha" id="Nocaptcha-$ID" data-sitekey="$SiteKey" data-theme="$CaptchaTheme.ATT" data-type="$CaptchaType.ATT" data-size="$CaptchaSize.ATT" data-form="$FormID" data-badge="$CaptchaBadge.ATT"></div>
<% else %>
    <input type="hidden" id="Nocaptcha-{$Form.FormName}" name="g-recaptcha-response"/>
<% end_if %>

<noscript>
    <p><%t UndefinedOffset\\NoCaptcha\\Forms\\NocaptchaField.NOSCRIPT "You must enable JavaScript to submit this form" %></p>
</noscript>
