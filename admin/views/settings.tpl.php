@extend('layout.tpl.php')

@block('content')
<h3>Settings</h3>
<p>Edit your settings</p><hr /><br />
  <? if(isset($_SESSION['success'])) : ?>
    <div class="alert alert-success" role="alert"><?= $_SESSION['success'] ?></div>   
  <? endif; ?>
  
  <? if(isset($_SESSION['error'])) : ?>
    <div class="alert alert-warning" role="alert"><?= $_SESSION['error'] ?></div>   
  <? endif; ?>

<form role="form" class="form-horizontal" action="<?= site_url('settings/save') ?>" method="POST">
<div class="form-group">
    <label class="col-sm-3 control-label">Site name</label>
    <div class="col-sm-6">
    <input type="text" class="form-control" value="<?= @$settings['site_name'] ?>" name="site_name">
    </div>
  </div>
<div class="form-group">
    <label class="col-sm-3 control-label">Site url</label>
    <div class="col-sm-6">
    <input type="text" class="form-control" value="<?= @$settings['site_url'] ?>" name="site_url">
    </div>
  </div>
  
<div class="form-group">
    <label class="col-sm-3 control-label">Email</label>
    <div class="col-sm-6">
    <input type="text" class="form-control" value="<?= @$settings['email'] ?>" name="email">
    </div>
  </div>

<div class="form-group">
	<label class="col-sm-3 control-label">Default payment method</label>
	<div class="col-sm-6">
		<select name="payment_method" class="form-control">
			<? foreach($payment_methods as $payment_method) : ?>
				<option value="<?= $payment_method ?>" <? if($payment_method == @$settings['payment_method']): ?>selected<?endif; ?>><?= ucfirst($payment_method) ?></option>
			<? endforeach; ?>
		</select>
	</div>
</div>
<hr />
<div class="form-group">
	<label class="col-sm-3 control-label">Paypal UserName</label>
	<div class="col-sm-6">
		<input type="text" class="form-control" value="<?= @$settings['paypal_username'] ?>" name="paypal_username">
	</div>
</div>
    
<div class="form-group">
	<label class="col-sm-3 control-label">Paypal Password</label>
	<div class="col-sm-6">
		<input type="text" class="form-control" value="<?= @$settings['paypal_password'] ?>" name="paypal_password">
	</div>
</div>
    
<div class="form-group">
	<label class="col-sm-3 control-label">Paypal Signature</label>
	<div class="col-sm-6">
		<input type="text" class="form-control" value="<?= @$settings['paypal_signature'] ?>" name="paypal_signature">
	</div>
</div>
<div class="form-group">
	<label class="col-sm-3 control-label">Paypal environment</label>
	<div class="col-sm-6">
		<select name="paypal_environment" class="form-control">
			<? foreach($paypal_environments as $paypal_environment) : ?>
				<option value="<?= $paypal_environment ?>" <? if($paypal_environment == @$settings['paypal_environment']): ?>selected<?endif; ?>><?= ucfirst($paypal_environment) ?></option>
			<? endforeach; ?>
		</select>
	</div>
</div>
<hr />

<div class="form-group">
    <label class="col-sm-3 control-label">Stripe secret key</label>
    <div class="col-sm-6">
    <input type="text" class="form-control" value="<?= @$settings['stripe_secret_key'] ?>" name="stripe_secret_key">
    </div>
  </div>
    
<div class="form-group">
    <label class="col-sm-3 control-label">Stripe publishable key</label>
    <div class="col-sm-6">
    <input type="text" class="form-control" value="<?= @$settings['stripe_publishable_key'] ?>" name="stripe_publishable_key">
    </div>
  </div>
<hr />
      
<div class="form-group">
    <label class="col-sm-3 control-label">Shop currency</label>
    <div class="col-sm-4">
    <select name="currency" class="form-control">
        <? foreach($currencies as $currency) : ?>
          <option value="<?= $currency->code ?>" <? if($currency->code == @$settings['currency']): ?>selected<?endif; ?>><?= $currency->name ?> (<?= $currency->code ?>)</option>
        <? endforeach; ?>
    </select>   
	</div>
  </div>
      
<div class="form-group">
    <label class="col-sm-3 control-label">Default product</label>
    <div class="col-sm-4">
    <select name="default_product" class="form-control">
		<option value="">-- SELECT --</option>
        <? foreach($products as $product) : ?>
          <option value="<?= $product['slug'] ?>" <? if($product['slug'] == @$settings['default_product']): ?>selected<?endif; ?>><?= $product['name'] ?></option>
        <? endforeach; ?>
    </select>    
	</div>
  </div>

     <hr />
	  <div class="form-group">
    <label class="col-sm-3 control-label">Admin password</label>
    <div class="col-sm-6">
    <input type="password" name="password" class="form-control" placeholder="Enter password" autocomplete="off">
    </div>
  </div>
	  <div class="form-group">
    <label class="col-sm-3 control-label">Admin password confirm</label>
    <div class="col-sm-6">
    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" autocomplete="off">
    </div>
  </div>

  <hr />
  <button type="submit" class="btn btn-primary pull-right">Save settings</button>
</form>
<br />
<br />
@endblock