@extend('layout.tpl.php')

@block('content')
<? if($imagick_not_installed) : ?>
<div class="alert alert-warning" role="alert">
	<strong>Warning!</strong> ImageMagick is not installed.
</div>
<? endif; ?>
<div ng-controller="OrderController">

	<h3>Order id : <?= $order->id ?></h3>
	<hr />
	<div class="row">
		<div class="col-md-12">
			
			<div class="row">
				<div class="col-md-12">	

			<div class="row">
				<div class="col-md-6">
			
			<div class="well">
					<strong>Order details</strong>				
					<div class="row">
						<div class="col-md-4">Sub-total</div>
						<div class="col-md-4 text-right"><?= number_format($order->subtotal, 2) ?> <?= $order->currency ?></div>
					</div>							
					<div class="row">
						<div class="col-md-4">Postage</div>
						<div class="col-md-4 text-right"><?= number_format($order->postage, 2) ?> <?= $order->currency ?></div>
					</div>			
					<hr />
					<div class="row">
						<div class="col-md-4">Total price</div>
						<div class="col-md-4 text-right"><strong><?= number_format($order->amount, 2) ?> <?= $order->currency ?></strong></div>
					</div>				
				</div>
					

			<? if($customer_details) : ?>
			
		<div class="well">
					<strong>Customer details</strong>
					<div class="row">
						<div class="col-md-4">First name</div>
						<div class="col-md-4"><?= @$customer_details->firstname ?></div>
					</div>					
					<div class="row">
						<div class="col-md-4">Last name</div>
						<div class="col-md-4"><?= @$customer_details->lastname ?></div>
					</div>					
					<div class="row">
						<div class="col-md-4">Email</div>
						<div class="col-md-4"><?= @$customer_details->email ?></div>
					</div>	
					<div class="row">
						<div class="col-md-4">TransactionID </div>
						<div class="col-md-6"><?= $order->transaction_id ?></div>
					</div>	

			</div>
			<? endif; ?>
			</div>
			
				<div class="col-md-6">
				

					<? if($order->dispatched): ?>
					<div class="row">
						<div class="col-md-6">
							<label style="margin-top: 10px">Date dispatched</label>
						</div>						
						<div class="col-md-6">
							<p class="form-control-static">
								<?= $carbon->parse($order->dispatched_on)->toDayDateTimeString() ?></p>
						</div>
					</div>	
					<? else: ?>
					<div class="row">
						<div class="col-md-6">
							<label style="margin-top: 10px">Has been dispatched</label>
						</div>						
						<div class="col-md-6">
							<p class="form-control-static" ng-show="order.dispatched == 1">YES</p>
							<select class="form-control" ng-model="order.dispatched" ng-show="order.dispatched != 1">
								<option value="1">Yes</option>
								<option value="0">No</option>
							</select>
						</div>
					</div>
					<? endif; ?>
					<br />
					<div class="row">
						<div class="col-md-12">
							<strong>Notes</strong>
							<textarea class="form-control" ng-model="order.notes"></textarea>
						</div>
					</div>					
					<br />
					<div class="row">
						<div class="col-md-12">
							<a href="" ng-show="savingNotes" class="btn btn-primary btn-sm pull-right">Saving...</a>
							<a href="" ng-hide="savingNotes" ng-click="saveNotes()" class="btn btn-primary btn-sm pull-right">Save</a>
						</div>
					</div>


				</div>
				</div>
					<hr />
								
				<div class="row">
					
					<div class="col-md-12">
						<div class="panel panel-default">
  							<div class="panel-heading">Order items</div>
  								<div class="panel-body">
								<table class="table table-striped">
							      <thead>
							        <tr>
							          <th>Product</th>
							          <th>Qty</th>
							          <th>Price</th>
							          <th>Total</th>
							        </tr>
							      </thead>
							      <tbody>
							      	<?php foreach($cart as $index => $item) : ?>
							        <tr>
							          <td>
							          	<strong><?= $item->product->name ?> (<?= $item->variant->name ?>)</strong><br />
										SKU: <?= @$item->product->SKU ?></strong>
							          </td>
							          <td>
							          	<? foreach($item->quantities as $label => $qty) : ?>
											<?= $label ?> : <?= $qty ?> <br />
							          	<? endforeach; ?>
							          </td>
							          <td>
							          	Base price: <?= number_format($item->product->price + $item->variant->additional_price, 2) ?> <?= $order->currency ?><br />
							          	With layers: <?= number_format($item->single_price, 2) ?> <?= $order->currency ?>
							          </td>
							          <td><?= number_format(@$item->total_price, 2) ?> <?= $order->currency ?>
										  <br /><small>(<?= $item->total_quantity ?> item/s)</small>
									  </td>
							        </tr>
							        <tr>
							          <td colspan="4">
							          	<div class="row">
							          		<? foreach($item->product->orientations as $orientation) : ?>
							          		<div class="col-md-3 text-center">
							          			 <div class="thumbnail">
													<img src="../../api/image_ordered.php?order=<?= $order->id ?>&index=<?= $index ?>&orientation=<?= $orientation->name ?>" alt="Click to download">
													<div class="caption">
														<h3><? $orientation->name ?></h3>
														<p ng-show="!isReady">Please wait...</p>
														<p ng-show="isReady"><a href="" class="" role="button" ng-click="startDownload(<?= $index ?>, '<?= $orientation->name ?>')">Download image</a></p>
													</div>
												</div>
							          			<strong><? $orientation->name ?></strong>
							          		</div>
							          		<? endforeach; ?>
							          	</div>
							          </td>
							        </tr>
							    	<?php endforeach; ?>
							      </tbody>
								</table>
							</div>
						</div>
					</div>

				</div>
					
				</div>		
				</div>		
			
		</div>		
		</div>		
		<hr />
		<div class="row">



			<div class="col-md-6">
			<? if($payment_details) : ?>
			
		<div class="well">
			<strong>Payment details</strong>

					<? if($order->payment_method == 'paypal') : ?>
					<div class="row">
						<div class="col-md-4">Payer name: </div>
						<div class="col-md-6"><?= implode(" ", array_values( (array) $payment_details->GetExpressCheckoutDetailsResponseDetails->PayerInfo->PayerName)) ?></div>
					</div>						
					<div class="row">
						<div class="col-md-4">Payer email: </div>
						<div class="col-md-6"><?= $payment_details->GetExpressCheckoutDetailsResponseDetails->PayerInfo->Payer ?></div>
					</div>			
					<div class="row">
						<div class="col-md-4">TransactionID: </div>
						<div class="col-md-6"><?= $order->transaction_id ?></div>
					</div>					
					<? else: ?>
					
					<div class="row">
						<div class="col-md-4">Name: </div>
						<div class="col-md-6"><?= $payment_details->card->name ?></div>
					</div>
										
					<div class="row">
						<div class="col-md-4">Number: </div>
						<div class="col-md-8">**** **** **** <?= $payment_details->card->last4 ?></div>
					</div>		
					
					<div class="row">
						<div class="col-md-4">Expires: </div>
						<div class="col-md-6"><?= $payment_details->card->exp_month ?> / <?= $payment_details->card->exp_year ?></div>
					</div>
										
					<div class="row">
						<div class="col-md-4">Type: </div>
						<div class="col-md-6"><?= $payment_details->card->brand ?> <?= $payment_details->card->funding ?></div>
					</div>
					<div class="row">
						<div class="col-md-4">Country: </div>
						<div class="col-md-6"><?= $payment_details->card->country ?></div>
					</div>
					<? endif; ?>

			</div>
			<? endif; ?>	

			</div>
			
						<div class="col-md-6">
					<div class="well">
					<strong>Shipping details</strong>
					<div class="row">
						<div class="col-md-12">
						<?= @$customer_details->address1 ?><br />
						<?= @$customer_details->address2 ?><br />
						<?= @$customer_details->city ?><br />
						<?= @$customer_details->state ?><br />
						<?= @$customer_details->zip ?><br />
						</div>
					</div>	
			</div>
			</div>
			
			<canvas id="printableArea" width="460" height="460" style="display: none; width: 460px; height: 460px;border: 1px solid #00ff00"></canvas>

			</div>

		</div>
	</div>
</div>
<script>
    var canvas = new fabric.Canvas('printableArea');
</script>

<script>
(function(c){var b,d,e,f,g,h=c.body,a=c.createElement("div");a.innerHTML='<span style="'+["position:absolute","width:auto","font-size:128px","left:-99999px"].join(" !important;")+'">'+Array(100).join("wi")+"</span>";a=a.firstChild;b=function(b){a.style.fontFamily=b;h.appendChild(a);g=a.clientWidth;h.removeChild(a);return g};d=b("monospace");e=b("serif");f=b("sans-serif");window.isFontAvailable=function(a){return d!==b(a+",monospace")||f!==b(a+",sans-serif")||e!==b(a+",serif")}})(document);
		
var cart = <?= json_encode($cart); ?>;
var fonts_required = <?= json_encode($fonts_required); ?>;
function OrderController($scope, $http, $q, $interval) {
	window.scope = $scope;
	$scope.designerWidth = 460;
	$scope.designerHeight = 460;
	$scope.order = {};
	$scope.order.id = <?= $order->id ?>;
	$scope.order.dispatched = <?= $order->dispatched ?>;
	$scope.order.notes = '<?= $order->notes ?>';
	
	$scope.fonts_loaded = [];
	$scope.preloadFonts = function(){
    	var deferred = $q.defer();

		var self = this;
		
		if(_.size(fonts_required) == 0) {
			deferred.resolve($scope.fonts_loaded);
		}

    	_.each(fonts_required, function(font) {
			console.log(font);
			if (!isFontAvailable(font.regular.fontface)) {

				$('head').append('<link rel="stylesheet" type="text/css" href="../../data/'+font.regular.stylesheet+'">');
				console.log("loading " + font.regular.stylesheet, font);
				var stop = $interval(function() {
					if (isFontAvailable(font.regular.fontface)) {
						console.log("loaded " + font.regular.fontface);
						$scope.fonts_loaded.push(font.regular.fontface);
						if(_.size($scope.fonts_loaded) == _.size(fonts_required)) {
							deferred.resolve($scope.fonts_loaded);
						}
						$interval.cancel(stop);
					}
				}, 100);
				
			}
			
		});
		
		return deferred.promise;
	};
	
	$scope.savingNotes = false;
	$scope.saveNotes = function() {
		$scope.savingNotes = true;
		$http.post('<?= site_url("orders/save") ?>', $scope.order).
			success(function(data, status, headers, config) {
				$scope.savingNotes = false;
				$scope.order.dispatched = 1;
				if(data.error != "") {
					swal("Mailing error", data.error);
				}
			}).
			error(function(data, status, headers, config) {
				$scope.savingNotes = false;
			});
	};
	
	$scope.downloadImage = function(params) {
		$http.post('<?= site_url("orders/print") ?>', params).
		success(function(data, status, headers, config) {
			if(!data.path) {
				alert(data);	
			} else {
				$('.sweet-alert .confirm').show();
				$scope.currentPath = '../' + data.path;
			}
		}).
		error(function(data, status, headers, config) {
			alert(data);
		});
	};
	
	$scope.currentPath = "";
	$scope.startDownload = function(index, orientation) {
		$scope.currentPath = "";
		swal({
			title: "Processing image..",
			text: "This may take 15 to 60 seconds...",
			confirmButtonText: "Download"
		}, function(){ 
			window.location.href = $scope.currentPath;
		});
		
		$('.sweet-alert .confirm').hide();
		setTimeout(function () {
			$scope.setCanvas(index, orientation);
		}, 1000);
	};
	
	$scope.setCanvas = function(index, orientation) {


		canvas.clear();
        var item = jQuery.extend(true, {}, cart[index]);
		var dims = _.find(cart[index].product.orientations, {name:orientation});

		var ratio = $scope.designerWidth/dims.width;
		$scope.selectedOrientationDimensions = {};
		$scope.selectedOrientationDimensions.width = ratio * dims.width;
        $scope.selectedOrientationDimensions.height = ratio * dims.height;
        $scope.selectedOrientationDimensions.printableWidth = ratio * dims.printable_width;
        $scope.selectedOrientationDimensions.printableHeight = ratio * dims.printable_height;
        $scope.selectedOrientationDimensions.printableOffsetX = ratio * dims.printable_offset_x;
        $scope.selectedOrientationDimensions.printableOffsetY = ratio * dims.printable_offset_y;
		
		canvas.setWidth($scope.selectedOrientationDimensions.printableWidth);
		canvas.setHeight($scope.selectedOrientationDimensions.printableHeight);

		canvas.loadFromJSON(item.canvases[orientation], canvas.renderAll.bind(canvas));
		canvas.renderAll();
		
		var dpi = 300;
		var printable_width = (dims.printable_width * dpi)/25.4;
		var printable_height = (dims.printable_height * dpi)/25.4;
		var unratio = dims.width / $scope.designerWidth;
		var multiplier = (printable_width/dims.printable_width) * unratio;
		
		var high_res_img = new Image;
		var params = {
			product: cart[index].product.slug,
			orientation: orientation,
			order_id:<?= $order->id ?>,
			index:index
		};
		high_res_img.onload = function(test){
			params.high_res_img = high_res_img.src;
			$scope.downloadImage(params);
		};
		
		setTimeout(function () {
			high_res_img.src = canvas.toDataURLWithMultiplier('png', multiplier, 1);		
		}, 3000);


	};

	$scope.isReady = false;
    $scope.init = function() {
		
		$scope.preloadFonts().then(function() {
			$scope.isReady = true;
		});
		
    };

    $scope.init();
}
</script>
	
@endblock