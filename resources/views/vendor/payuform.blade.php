
<form action="{{ $action or '--'}}"  method="post" name="payuForm"> 
<input type="hidden" name="key" value="{{ $key or '--' }}" />
<input type="hidden" name="hash" value="{{ $hash or '--' }}"/>
<input type="hidden" name="txnid" value="{{ $txnid or '--' }}" />
<input type="hidden" name="amount" value="{{ $amount or '--' }}" />
<input type="hidden" name="firstname" value="{{ $firstname or '--' }}" />
<input type="hidden" name="email" value="{{ $email or '--' }}" />
<input type="hidden" name="phone" value="{{ $phone or '--' }}" />
<input type="hidden" name="service_provider" value="payu_paisa" size="64" />
<input type="hidden" name="productinfo" value="{{ $productinfo or '--' }}" />
<input type="hidden" name="surl" value="{{ $surl or '--' }}" />
<input type="hidden" name="furl" value="{{ $furl or '--' }}" />
</form>

<script type="text/javascript">
	var payuForm = document.forms.payuForm;
    payuForm.submit();
</script>