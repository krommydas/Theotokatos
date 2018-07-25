<form id="attiki_bank_form" method="post" action="<?php echo $action; ?>" >
<input type="hidden" name="txntype" value="sale">
<input type="hidden" name="timezone" value="<?php echo date_default_timezone_get(); ?>"/>
<input type="hidden" name="txndatetime" />
<input type="hidden" name="hash_algorithm" value="SHA256"/>
<input type="hidden" name="hash"/>
<input type="hidden" name="storename" value="<?php echo $store_name;  ?>"/>
<input type="hidden" name="mode" value="payonly"/>
<input type="hidden" name="oid" value="<?php echo $order_id;  ?>"/>
<input type="hidden" type="text"   name="chargetotal" value="<?php echo $charge_total; ?>"/>
<input type="hidden" name="currency" value="<?php echo $currency; ?>"/>
<input type="hidden" name="responseSuccessURL" value="<?php echo $return_url; ?>"/>
<input type="hidden" name="responseFailURL" value="<?php echo $return_url; ?>"/>
 <div class="buttons">
    <div class="right">
      <button id="btn" class="button"><?php echo $button_confirm; ?></button>
    </div>
  </div>
</form>
<script type="text/javascript">
<!-- 
    $('#btn').click(function(e){
        $.ajax({
            url: "<?php echo $initiate_url; ?>",
            data: {'order_id' : "<?php echo $order_id;  ?>"} 
            })
        	.done(function(response) {
            	if(response.Hash && response.Date) {
            		$('input[name="hash"]').attr('value', response.Hash);
            		$('input[name="txndatetime"]').attr('value', response.Date);
            		$('#attiki_bank_form').submit();
            	}
            	else if(response.Error)
                	alert(response.Error);
            	else 
            		alert("<?php echo $initiate_error; ?>");
        	})
        	.fail(function() {
        	    alert("<?php echo $initiate_error; ?>");
        	  });
        	
        return false;
  });
//--></script>