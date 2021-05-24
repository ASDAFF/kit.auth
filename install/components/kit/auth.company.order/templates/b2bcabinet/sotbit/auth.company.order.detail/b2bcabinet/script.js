BX.namespace('BX.Sale.PersonalOrderComponent');

(function() {

	'use strict';

	var LightTableFilter = (function(Arr) {

		var _input;

		function _onInputEvent(e) {
			_input = e.target;
			var tables = document.getElementsByClassName(_input.getAttribute('data-table'));
			Arr.forEach.call(tables, function(table) {
				Arr.forEach.call(table.tBodies, function(tbody) {
					Arr.forEach.call(tbody.rows, _filter);
				});
			});
		}

		function _filter(row) {
			var text = row.textContent.toLowerCase(), val = _input.value.toLowerCase();
			row.style.display = text.indexOf(val) === -1 ? 'none' : 'table-row';
		}

		return {
			init: function() {
				var inputs = document.getElementsByClassName('input-search');
				Arr.forEach.call(inputs, function(input) {
					input.oninput = _onInputEvent;
				});
			}
		};
	})(Array.prototype);

	document.addEventListener('readystatechange', function() {
		if (document.readyState === 'complete') {
			LightTableFilter.init();
		}
	});

	window.B2bOrderDetail = function(arParams) {
		// this.ExcelButtonId = arParams["ExcelButtonId"];
		this.ajaxUrl = arParams["ajaxUrl"];
		this.paymentList = arParams["paymentList"];
		this.changePayment = arParams["changePayment"];
		this.changePaymentWrapper = arParams["changePaymentWrapper"];
		this.templateName = arParams["TemplateName"];
		window.changePaymentTemplateName = this.templateName;
		// this.arResult = arParams["arResult"];
		// this.arParams = arParams["arParams"];
		// this.filter = arParams["filter"];
		// this.qnts = arParams["qnts"];
		// this.TemplateFolder = arParams["TemplateFolder"];
		// this.OrderId = arParams["OrderId"];
		// this.Headers = arParams["Headers"];
		// this.HeadersSum = arParams["HeadersSum"];
		this.destroy();
		this.init();
	}
	window.B2bOrderDetail.prototype.destroy = function() {

	}
	window.B2bOrderDetail.prototype.init = function() {
		$(document).on("click", this.changePayment, this, this.clickchangePayment);
	}

	window.B2bOrderDetail.prototype.clickchangePayment = function(e) {
		var data = e.data;
		BX.ajax(
			{
				method: 'POST',
				dataType: 'html',
				url: data.ajaxUrl,
				data:
					{
						sessid: BX.bitrix_sessid(),
						orderData: data.paymentList[$(this).attr('id')],
						templateName: window.changePaymentTemplateName
					},
				onsuccess: BX.proxy(function(result)
				{
					$(data.changePaymentWrapper).html(result);
					$(this).hide();
				},this),
				onfailure: BX.proxy(function()
				{
					return this;
				}, this)
			}, this
		);
	}

	/*BX.Sale.PersonalOrderComponent.PersonalOrderDetail = {
		init : function(params)
		{
			var linkMoreOrderInformation = document.getElementsByClassName('sale-order-detail-about-order-inner-container-name-read-more')[0];
			var linkLessOrderInformation = document.getElementsByClassName('sale-order-detail-about-order-inner-container-name-read-less')[0];
			var clientInformation = document.getElementsByClassName('sale-order-detail-about-order-inner-container-details')[0];
			var listShipmentWrapper = document.getElementsByClassName('sale-order-detail-payment-options-shipment');
			var listPaymentWrapper = document.getElementsByClassName('sale-order-detail-payment-options-methods');
			var shipmentTrackingId = document.getElementsByClassName('sale-order-detail-shipment-id');

			if (shipmentTrackingId[0])
			{
				Array.prototype.forEach.call(shipmentTrackingId, function(blockId)
				{
					var clipboard = blockId.parentNode.getElementsByClassName('sale-order-detail-shipment-id-icon')[0];
					if (clipboard)
					{
						BX.clipboard.bindCopyClick(clipboard, {text : blockId.innerHTML});
					}
				});
			}


			BX.bind(linkMoreOrderInformation, 'click', function()
			{
				clientInformation.style.display = 'inline-block';
				linkMoreOrderInformation.style.display = 'none';
				linkLessOrderInformation.style.display = 'inline-block';
			},this);
			BX.bind(linkLessOrderInformation, 'click', function()
			{
				clientInformation.style.display = 'none';
				linkMoreOrderInformation.style.display = 'inline-block';
				linkLessOrderInformation.style.display = 'none';
			},this);

			Array.prototype.forEach.call(listShipmentWrapper, function(shipmentWrapper)
			{
				var detailShipmentBlock = shipmentWrapper.getElementsByClassName('sale-order-detail-payment-options-shipment-composition-map')[0];
				var showInformation = shipmentWrapper.getElementsByClassName('sale-order-detail-show-link')[0];
				var hideInformation = shipmentWrapper.getElementsByClassName('sale-order-detail-hide-link')[0];

				BX.bindDelegate(shipmentWrapper, 'click', { 'class': 'sale-order-detail-show-link' }, BX.proxy(function()
				{
					showInformation.style.display = 'none';
					hideInformation.style.display = 'inline-block';
					detailShipmentBlock.style.display = 'block';
				}, this));
				BX.bindDelegate(shipmentWrapper, 'click', { 'class': 'sale-order-detail-hide-link' }, BX.proxy(function()
				{
					showInformation.style.display = 'inline-block';
					hideInformation.style.display = 'none';
					detailShipmentBlock.style.display = 'none';
				}, this));
			});

			Array.prototype.forEach.call(listPaymentWrapper, function(paymentWrapper)
			{
				var rowPayment = paymentWrapper.getElementsByClassName('sale-order-detail-payment-options-methods-info')[0];

				BX.bindDelegate(paymentWrapper, 'click', { 'class': 'active-button' }, BX.proxy(function()
				{
					BX.toggleClass(paymentWrapper, 'sale-order-detail-active-event');
				}, this));

				BX.bindDelegate(rowPayment, 'click', { 'class': 'sale-order-detail-payment-options-methods-info-change-link' }, BX.proxy(function(event)
				{
					event.preventDefault();

					var btn = rowPayment.parentNode.getElementsByClassName('sale-order-detail-payment-options-methods-button-container')[0];
					var linkReturn = rowPayment.parentNode.getElementsByClassName('sale-order-detail-payment-inner-row-template')[0];
					BX.ajax(
						{
							method: 'POST',
							dataType: 'html',
							url: params.url,
							data:
							{
								sessid: BX.bitrix_sessid(),
								orderData: params.paymentList[event.target.id],
								templateName : params.templateName
							},
							onsuccess: BX.proxy(function(result)
							{
								rowPayment.innerHTML = result;
								if (btn)
								{
									btn.parentNode.removeChild(btn);
								}
								linkReturn.style.display = "block";
								BX.bind(linkReturn, 'click', function()
								{
									window.location.reload();
								},this);
							},this),
							onfailure: BX.proxy(function()
							{
								return this;
							}, this)
						}, this
					);

				}, this));
			});
		}
	};*/

	function excelOut(id)
	{
		BX.showWait();
		var file = '';

		$.ajax({
			type: 'POST',
			async: false,
			url: '/include/ajax/personal_order_excel_export.php',
			data: {
				orderId:id,
				file:file
			},
			success: function(data) {
				file = data;
			},
		});

		var now = new Date();

		var dd = now.getDate();
		if (dd < 10) dd = '0' + dd;
		var mm = now.getMonth() + 1;
		if (mm < 10) mm = '0' + mm;
		var hh = now.getHours();
		if (hh < 10) hh = '0' + hh;
		var mimi = now.getMinutes();
		if (mimi < 10) mimi = '0' + mimi;
		var ss = now.getSeconds();
		if (ss < 10) ss = '0' + ss;

		var rand = 0 - 0.5 + Math.random() * (999999999 - 0 + 1)
		rand = Math.round(rand);

		var name = 'blank_' + now.getFullYear() + '_' + mm + '_' + dd + '_' + hh + '_' + mimi + '_' + ss + '_' + rand + '.xlsx';

		var link = document.createElement('a');
		link.setAttribute('href',file);
		link.setAttribute('download',name);
		var event = document.createEvent("MouseEvents");
		event.initMouseEvent(
			"click", true, false, window, 0, 0, 0, 0, 0
			, false, false, false, false, 0, null
		);
		link.dispatchEvent(event);
		BX.closeWait();
	};

	$(document).on("click", "#excel-button-export", function ()
	{
		var orderId = this.dataset.id;
		excelOut(orderId);
	});
})();