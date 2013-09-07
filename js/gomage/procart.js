 /**
 * GoMage ProCart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.1
 * @since        Class available since Release 1.0
 */

Event.observe(window, 'load', function() {
		GoMageProCartCreate();	
	}
);

function GoMageProCartCreate(){
	if(typeof(gomage_procart_config) == 'object'){
		GomageProcartConfig = new GomageProcartConfigClass(gomage_procart_config);
	}
}

var gpc_preparedGomageSalesDeals = false;
var gpc_SalesDealsData = {};

GomageProcartConfigClass = Class.create();
GomageProcartConfigClass.prototype = {
	
	config: null,	
	qty_template: null,
	qty_deals_template: null,
	qty_cart_template: null,
	qty_product_template: null,
	overlay: null,
	loadinfo: null,	
	js_scripts: null,
	configurable_qty: null,
	grouped_qty: null,
	add_to_cart_onclick_str: null,
	product_list: null,
	slide_control: undefined,
	auto_hide_window: null,
	confirmation_window_type: null,
	addition_product_list_ids: null, 
	
	initialize: function(config){
	
		this.config = config;		
		if (this.config.enable != '1') return;
				
		this.add_to_cart_onclick_str = new Array();
		this.addition_product_list_ids = new Array();
		this.product_list = {};
		
		this.qty_template = gomage_procart_qty_template;
		this.qty_deals_template = gomage_procart_qty_deals_template;
		this.qty_cart_template = gomage_procart_qty_cart_template;
		this.qty_product_template = gomage_procart_qty_product_template;
		
		if($$('div.category-products').length > 0 && this.config.change_qty_category_page == '1'){
			
			if (typeof(gomage_procart_product_list) != 'undefined'){
				this.product_list = gomage_procart_product_list;
			}
			
			var elements = $$('div.category-products')[0].getElementsByClassName('btn-cart');
			for(var i=0; i<elements.length; i++){
				
				var onclick_str = elements[i].attributes["onclick"].nodeValue;
				onclick_str = onclick_str.toString().match(/\'.*?\'/);
				onclick_str = onclick_str[0].replace(/\'/g, '');								
				var product_id = ProcartGetUrlParam(onclick_str, 'gpc_prod_id');
				if (!product_id) continue;
												
				var qty_div = $(document.createElement('div'));
				qty_div.addClassName('gpc_qty_edit');
				
				var verification_qty = false;
				if (onclick_str.search('checkout/cart/add') != -1){
					verification_qty = true;
				}	
				
				qty_div.innerHTML = this.qty_template.replace(/#gpc_prod_id/g, product_id).replace(/#verification_qty/g, verification_qty);
				new Insertion.After(elements[i], qty_div);
				
				
				if (typeof(this.product_list[product_id])=='undefined'){
					this.addition_product_list_ids.push(product_id);
					$('gpc_prod_id_' + product_id).value = 1;
				}else{
					$('gpc_prod_id_' + product_id).value = this.product_list[product_id].min_qty;
				}	
				
				elements[i].onclick = function() {					 				     
				    GomageProcartConfig.addtoCart(this);
				};
				elements[i].id = 'gcp_add_to_cart_' + this.add_to_cart_onclick_str.length;
				this.add_to_cart_onclick_str[this.add_to_cart_onclick_str.length] = onclick_str;				 
				
			}				 			
		}
		
		this.prepareCartItem(undefined);
		this.prepareProductPage();
		this.prepareWishlist();
		this.prepareCompare();
		
		this.prepareCrosssell();
		this.prepareGomageSalesDeals();
				
		this.overlay = $('gomage-cartpro-overlay');				
		if(!this.overlay){				
			var element = $$('body')[0];			
			this.overlay = $(document.createElement('div'));
			this.overlay.id = 'gomage-cartpro-overlay';
			document.body.appendChild(this.overlay);
				
			var offsets = element.cumulativeOffset();
			this.overlay.setStyle({
				'top'	    : offsets[1] + 'px',
				'left'	    : offsets[0] + 'px',
				'width'	    : element.offsetWidth + 'px',
				'height'	: element.offsetHeight + 'px',
				'position'  : 'absolute',
				'display'   : 'block',
				'zIndex'	: '2000'				
			});
			
			if (this.config.background_view == '1'){
				this.overlay.setStyle({
					'opacity'  : '0.6',
					'background' : '#000000'
				});	
			}
			
			this.loadinfo = $(document.createElement('div'));		
			if(this.config.loadimagealign == 'bottom')			
				this.loadinfo.innerHTML = this.config.gpc_loadinfo_text+'<img src="'+this.config.loadimage+'" alt="" class="align-'+this.config.loadimagealign+'"/>';			
			else				
				this.loadinfo.innerHTML = '<img src="'+this.config.loadimage+'" alt="" class="align-'+this.config.loadimagealign+'"/>'+this.config.gpc_loadinfo_text;				
					
			this.loadinfo.id = "gomage-cartpro-loadinfo";
			this.loadinfo.className = "gpc-loadinfo";			
			document.body.appendChild(this.loadinfo);
			
			this.overlay.onclick = function() {
				if ($('gpc_confirmation_window').visible()){
					GomageProcartConfig.overlay.hide();
					$('gpc_confirmation_window').hide();
				}
			};						
		}
		if (this.overlay && this.overlay.visible()) this.overlay.hide();
		if (this.loadinfo && this.loadinfo.visible()) this.loadinfo.hide();
		
		if(this.addition_product_list_ids.length){
			var params = {product_ids: this.addition_product_list_ids.join(',')};
			
			var request = new Ajax.Request(this.config.addition_product_list_url,
		          {
		              method:'post',
		              parameters:params,		                
		              onSuccess: function(transport){
							eval('var response = '+transport.responseText);		
							if (response.product_list){
								for (var product_id in response.product_list){
									this.product_list[product_id] = response.product_list[product_id];
								}
							}				
							
					  }.bind(this),				  
					  onFailure: function(){						  
					  }.bind(this)
		          }
		      );
		}
		
			
	},
	
	prepareGomageSalesDeals: function(){
		
		var blocks = $$('div.gomage-sd');
				
		var _SalesDealsData = {};
		
		if($$('div.gomage-sd').length && this.config.change_qty_category_page == '1'){
			
			for(var bi=0; bi<blocks.length; bi++){
			
				var elements = blocks[bi].getElementsByClassName('btn-cart');
				for(var i=0; i<elements.length; i++){
					
					var deals_id = $(elements[i]).up('li.item').id;
					
					if (gpc_preparedGomageSalesDeals){
						for(var key in gpc_SalesDealsData){
							if (gpc_SalesDealsData.hasOwnProperty(key)){
								if (key == elements[i].id){
									var onclick_str = gpc_SalesDealsData[key].click; 
								}							    
							}
						} 
					}else{
						var onclick_str = elements[i].attributes["onclick"].nodeValue;
					}
					
					var base_onclick_str = onclick_str;
										
					onclick_str = onclick_str.toString().match(/\'.*?\'/);
					onclick_str = onclick_str[0].replace(/\'/g, '');								
					var product_id = ProcartGetUrlParam(onclick_str, 'gpc_prod_id');
					if (!product_id) continue;
					
					var qty_div = $(document.createElement('div'));
					qty_div.addClassName('gpc_qty_edit');
					
					var verification_qty = false;
					if (onclick_str.search('checkout/cart/add') != -1){
						verification_qty = true;
					}	
					
					qty_div.innerHTML = this.qty_deals_template.replace(/#gpc_prod_id/g, product_id).replace(/#verification_qty/g, verification_qty).replace(/#deals_id/g, deals_id);
					
					if (!gpc_preparedGomageSalesDeals){
						new Insertion.After(elements[i], qty_div);					
						$('gpc_prod_id_' + deals_id).value = 1;
					}
					
					elements[i].onclick = function() {					 				     
					    GomageProcartConfig.addtoCart(this);
					};
					elements[i].id = 'gcp_add_to_cart_' + this.add_to_cart_onclick_str.length;
					
					_SalesDealsData[elements[i].id] = {click:base_onclick_str, deals:deals_id};
					
					this.add_to_cart_onclick_str[this.add_to_cart_onclick_str.length] = onclick_str;	
					
					if (typeof(this.product_list[product_id])=='undefined'){
						this.addition_product_list_ids.push(product_id);
					}
					
				}
			
			}
		}
		gpc_SalesDealsData = _SalesDealsData;
		gpc_preparedGomageSalesDeals = true;
		
	},
	
	prepareCrosssell: function(){
		if($('crosssell-products-list') && this.config.change_qty_crosssell_prods == '1'){
			var elements = $('crosssell-products-list').getElementsByClassName('btn-cart');
			for(var i=0; i<elements.length; i++){
				
				var onclick_str = elements[i].attributes["onclick"].nodeValue;
				onclick_str = onclick_str.toString().match(/\'.*?\'/);
				onclick_str = onclick_str[0].replace(/\'/g, '');								
				var product_id = ProcartGetUrlParam(onclick_str, 'gpc_prod_id');
				if (!product_id) continue;
				
				var qty_div = $(document.createElement('div'));
				qty_div.addClassName('gpc_qty_edit');
				
				var verification_qty = false;
				if (onclick_str.search('checkout/cart/add') != -1){
					verification_qty = true;
				}	
				
				qty_div.innerHTML = this.qty_template.replace(/#gpc_prod_id/g, product_id).replace(/#verification_qty/g, verification_qty);
				
				new Insertion.After(elements[i], qty_div);
				
				$('gpc_prod_id_' + product_id).value = 1;
				
				elements[i].onclick = function() {					 				     
				    GomageProcartConfig.addtoCart(this);
				};
				elements[i].id = 'gcp_add_to_cart_' + this.add_to_cart_onclick_str.length;
				this.add_to_cart_onclick_str[this.add_to_cart_onclick_str.length] = onclick_str;	
				
				if (typeof(this.product_list[product_id])=='undefined'){
					this.addition_product_list_ids.push(product_id);
				}
				
			}	
		}
	},
	
	prepareWishlist: function(){
		var elements = $$('a.link-wishlist');
		for(var i=0; i<elements.length; i++){
			elements[i].setAttribute("onclick", "");
			elements[i].observe('click', this.addToWishlist.bind(this));
		}	
	},
	
	addToWishlist: function(event) {
		event.stop();
		var element = Event.element(event);
		var url = element.href;		
		var params = {gpc_wishlist_add: 1};
	
		this.startLoadData();
		
		var request = new Ajax.Request(url,
	          {
	              method:'post',
	              parameters:params,		                
	              onSuccess: function(transport){
						eval('var response = '+transport.responseText);		
						this.endLoadData();
						if(response.redirect){
							window.location.href = response.redirect;
							return;
						}
						this.replaceTopLinks(response);
						this.replaceSidebar('block-wishlist', response.wishlist);
						this.showConfirmationWindow(response, 'wishlist');
						
				  }.bind(this),				  
				  onFailure: function(){
					  this.endLoadData();
					  alert('Error add to Wishlist');
				  }.bind(this)
	          }
	      );    	
	},	
	
	deleteWishlistItem: function(url){
		
		var params = {};
		
		this.startLoadData();
		
		var request = new Ajax.Request(url,
	          {
	              method:'post',
	              parameters:params,		                
	              onSuccess: function(transport){
						eval('var response = '+transport.responseText);		
						this.endLoadData();
						if(response.message){
							alert(response.message);
						}else{
							this.replaceTopLinks(response);
							this.replaceSidebar('block-wishlist', response.wishlist);
						}												
				  }.bind(this),				  
				  onFailure: function(){
					  this.endLoadData();
					  alert('Error add to Wishlist');
				  }.bind(this)
	          }
	      );    	
		
	}, 
	
	prepareCompare: function(){
		var elements = $$('a.link-compare');
		for(var i=0; i<elements.length; i++){
			elements[i].setAttribute("onclick", "");
			elements[i].observe('click', this.addToCompare.bind(this));
		}
	},
	
	addToCompare: function(event) {
		event.stop();
		var element = Event.element(event);
		var url = element.href;		
		var params = {gpc_compare_add: 1};
	
		this.startLoadData();
		
		var request = new Ajax.Request(url,
	          {
	              method:'post',
	              parameters:params,		                
	              onSuccess: function(transport){
						eval('var response = '+transport.responseText);		
						this.endLoadData();
						if(response.compare_products){							
							this.replaceSidebar('block-compare', response.compare_products);
						}
						this.showConfirmationWindow(response, 'compare');
						
				  }.bind(this),				  
				  onFailure: function(){
					  this.endLoadData();
					  alert('Error add to Compare');
				  }.bind(this)
	          }
	      );    	
	},
	
	deleteCompareItem: function(url){
		
		var params = {};
		
		this.startLoadData();
		
		var request = new Ajax.Request(url,
	          {
	              method:'post',
	              parameters:params,		                
	              onSuccess: function(transport){
						eval('var response = '+transport.responseText);		
						this.endLoadData();
						this.endLoadData();
						if(response.compare_products){							
							this.replaceSidebar('block-compare', response.compare_products);
						}											
				  }.bind(this),				  
				  onFailure: function(){
					  this.endLoadData();
					  alert('Error add to Wishlist');
				  }.bind(this)
	          }
	      );    	
		
	}, 
	
	prepareCartItem: function(update_item_id){
		
		if (this.config.change_qty_cart_page != '1') return;
		
		if ($('shopping-cart-table') && $('shopping-cart-table').select('input.qty').length > 0){
			var elements = $('shopping-cart-table').select('input.qty');
			
			for(var i=0; i<elements.length; i++){
				var item_id = elements[i].name;
				item_id = item_id.replace(/\D/g, '');
				
				if (update_item_id != undefined && item_id != update_item_id){
					continue;
				}	
				
				var td = elements[i].up('td');								
				elements[i].id = 'gpc_cart_item_' + item_id;
				
				var item_html = td.innerHTML;
				var td_html = this.qty_cart_template.replace(/#gpc_item_id/g, item_id).replace(/#gpc_input_cart_qty/g, item_html);
				
				td.innerHTML = td_html;
			}	
		}
	},
	
	addtoCartProduct: function(){
		
		var product_id = $$('input[name="product"]').first().value;
		
		if ($('qty')){
			var qty = $('qty').value;
			qty = parseInt(qty);
			if (!qty){
				$('qty').value = 1;
				qty = 1;
			}
			
			if (qty < this.product_list[product_id].min_qty){
				alert('The minimum quantity allowed for purchase is ' + this.product_list[product_id].min_qty + '.');
				return;
			}		
			if (qty > this.product_list[product_id].max_qty){
				alert('The maximum quantity allowed for purchase is ' + this.product_list[product_id].max_qty + '.');
				return;
			}
		}else if (this.product_list[product_id].is_grouped == '1' && $('super-product-table')){
			var elements = $('super-product-table').getElementsByClassName('qty');
			if (elements.length > 0){
				var all_zero = true;
				for(var i=0; i<elements.length; i++){
					if (parseInt(elements[i].value) > 0){
						all_zero = false;
						break;
					}
				}
				if (all_zero){
					alert('Please specify the quantity of product(s).');
					return;
				}
			}
		}
		
		if (this.config.add_effect == '2') { 
			this.slide_control = $('image');
			this.effectSlidetoCart(this.slide_control);
			this.slide_control = undefined;
		} else if (this.config.add_effect == '1'){
			this.startLoadData();
		}
				
		$('product_addtocart_form').request({
			onSuccess: this.onSuccesAddtoCart.bind(this), 		                	
            onFailure: this.onFailureAddtoCart.bind(this)
	    });
	},
	
	prepareProductPage: function(){	
		
		if ($('product_addtocart_form') && typeof(productAddToCartForm) != 'undefined'){
			var gpc_add = document.createElement("input");
			gpc_add.type = "hidden";
			gpc_add.name = "gpc_add";
			gpc_add.value = "1";
			$('product_addtocart_form').appendChild(gpc_add);
			$('product_addtocart_form').onsubmit = function(){
			    return false;
			};
			productAddToCartForm.submit = function(){
				if (productAddToCartForm.validator.validate()){
					GomageProcartConfig.addtoCartProduct();					
				}
			}

            if (typeof(gomage_procart_product_list) != 'undefined'){
			    this.product_list = gomage_procart_product_list;
    		}else{
    			var product_id = $$('input[name="product"]').first().value;
    			if (product_id){
    				this.addition_product_list_ids.push(product_id);
    			}
    		}
		}

		if (this.config.change_qty_product_page != '1') return;

		if ($('qty')){
			var product_id = $$('input[name="product"]').first().value; 
						
			var qty_div = $(document.createElement('div'));
			qty_div.addClassName('gpc_qty_edit');
			new Insertion.After($('qty'), qty_div);
			qty_div.appendChild($('qty'));
			
			var qty_html = qty_div.innerHTML;			
			qty_div.innerHTML = this.qty_product_template.replace(/#product_id/g, product_id).replace(/#gpc_input_product_qty/g, qty_html);
		}
		if ($('super-product-table')){
			var elements = $('super-product-table').select('input.qty');
			for(var i=0; i<elements.length; i++){
				
				var product_id = elements[i].name;
				product_id = product_id.replace(/\D/g, '');
				
				var td = elements[i].up('td');								
				elements[i].id = 'gpc_prod_grouped_' + product_id;
				
				var item_html = td.innerHTML;				
				var td_html = this.qty_product_template.replace(/#product_id/g, product_id).replace(/#gpc_input_product_qty/g, item_html);				
				td.innerHTML = td_html;
				
				if (typeof(this.product_list[product_id])=='undefined'){
					this.addition_product_list_ids.push(product_id);
				}
				
			}				
		}
	},
	
	addtoCart: function(control){
		var control_id = control.id;
		control_id = control_id.replace(/\D/g, '');
		var onclick_str = this.add_to_cart_onclick_str[control_id]; 						
		var product_id = ProcartGetUrlParam(onclick_str, 'gpc_prod_id');
		if (!product_id) return;
		
		var deals_id = '';
		
		if (gpc_SalesDealsData.hasOwnProperty(control.id)){
			deals_id = gpc_SalesDealsData[control.id].deals; 
		}			
		
		var qty = $('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value;
		qty = parseInt(qty);
		if (!qty){
			$('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value = 1;
			qty = 1;
		}
		
		if (qty < this.product_list[product_id].min_qty){
			alert('The minimum quantity allowed for purchase is ' + this.product_list[product_id].min_qty + '.');
			return;
		}		
		if (qty > this.product_list[product_id].max_qty){
			alert('The maximum quantity allowed for purchase is ' + this.product_list[product_id].max_qty + '.');
			return;
		}
		
		this.slide_control = control;
		
		if (onclick_str.search('checkout/cart/add') != -1){
			if (this.product_list[product_id].is_simple == '1'){				
				this.effectSlidetoCart(control);
				this.slide_control = undefined;
			}
			if (this.product_list[product_id].is_simple == '0'){
				this.startLoadData();
			}
			this.addtoCartSimple(onclick_str, product_id, deals_id);
		}	
		else if (onclick_str.search('options=cart')){			
			this.showConfigurableParams(onclick_str, product_id, deals_id);
		}	
		
	},	
	
	showConfigurableParams: function(url, product_id, deals_id){
				 
		this.startLoadData();		
		
		var qty = $('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value;
		qty = parseInt(qty);
		if (!qty){
			$('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value = 1;
			qty = 1;
		}
		
		var params = {qty: qty,
					  gpc_show_configurable: 1};
		
		var request = new Ajax.Request(url,
	            {
	                method:'post',
	                parameters:params,		                
	                onSuccess: this.onSuccesShowConfigurable.bind(this), 		                	
	                onFailure: this.onFailureShowConfigurable.bind(this)
	            }
	        );    	
	},	
	
	onSuccesShowConfigurable: function(transport){			
		eval('var response = '+transport.responseText);		
		this.endLoadData();
		if(response.success){
			this.js_scripts = response.form.extractScripts();
			this.configurable_qty = response.qty;
			var win = new GcpWindow('gcp_configurable_add_to_cart', 
					{className: "gomage_cp",
					 additionClass: "gpc-confw-buttons-" + GomageProcartConfig.config.cart_button_color,
				     title: 'Add to Cart', 
				     width: GomageProcartConfig.config.window_width, 	
				     top: '50%',
				     destroyOnClose: true,
				     closeOnEsc: false,
				     showEffectOptions: {afterFinish: function(){
												for (var i=0; i<GomageProcartConfig.js_scripts.length; i++){																
											        if (typeof(GomageProcartConfig.js_scripts[i]) != 'undefined'){        	        	
											        	globalEval(GomageProcartConfig.js_scripts[i]);                	
											        }
											    }
												$('qty').value = GomageProcartConfig.configurable_qty;
												if ($('overlay_modal')){
													$('overlay_modal').onclick = function() {					 				     
														var win = GcpWindows.getWindow('gcp_configurable_add_to_cart');
														win.close();
													};
												}	
											}
										}
			}); 
			win.getContent().innerHTML = response.form.stripScripts();			
			win.showCenter(parseInt(this.config.background_view));									
		}	
		else{
			if (response.redirect){
				window.location.href = response.redirect; 
			}else if (response.message){
				alert(response.message);
			}else{
				alert('Error add to cart.');
			}	
		}			            			
	},
	
	onFailureShowConfigurable: function(transport){
		this.endLoadData();
		alert('Failure add to cart.');
	},
	
	addtoCartConfigurable: function(form){
		if (this.config.add_effect == '1'){ 
			this.startLoadData();
		}
		var elements = form.getElements('input, select, textarea');		
		var params = {};		
		for(var i = 0;i < elements.length;i++){
			if((elements[i].type == 'checkbox' || elements[i].type == 'radio') && !elements[i].checked){
				continue;
			}				
			if (elements[i].disabled){
				continue;
			}				
			params[elements[i].name] = elements[i].value;
		}	
		var request = new Ajax.Request(form.action,
	            {
	                method:'post',
	                parameters:params,		                
	                onSuccess: this.onSuccesAddtoCart.bind(this), 		                	
	                onFailure: this.onFailureAddtoCart.bind(this)
	            }
	        );   
	},	
	
	addtoCartSimple: function(url, product_id, deals_id){
		
		if (this.config.add_effect == '1'){ 
			this.startLoadData();
		}
		
		var qty = $('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value;
		qty = parseInt(qty);
		if (!qty){
			$('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value = 1;
			qty = 1;
		}
		
		var params = {qty: qty,
					  gpc_add: 1};
		
		var request = new Ajax.Request(url,
			            {
			                method:'post',
			                parameters:params,		                
			                onSuccess: this.onSuccesAddtoCart.bind(this), 		                	
			                onFailure: this.onFailureAddtoCart.bind(this)
			            }
			        );    	
	},	
	
	onSuccesAddtoCart: function(transport){
		
		eval('var response = '+transport.responseText);
		this.endLoadData();
		var win = GcpWindows.getWindow('gcp_configurable_add_to_cart');
		if (win){
			win.close();
		}	
		if(response.success){
			if (response.is_grouped){
				this.showGroupedParams(response);
				return;
			}
			if (response.is_configurable){				
				this.showConfigurableParams(response.url, response.product_id, response.deals_id);
				return;
			}
			if (response.product_id){
				this.product_list[response.product_id].max_qty = this.product_list[response.product_id].max_qty*1 - response.qty*1;
				if (this.product_list[response.product_id].max_qty*1 < this.product_list[response.product_id].min_qty*1){
					this.product_list[response.product_id].min_qty = this.product_list[response.product_id].max_qty; 
				}
			}
			if (this.slide_control != undefined){
				this.effectSlidetoCart(this.slide_control);
				this.slide_control = undefined;
			}
			this.showConfirmationWindow(response, 'cart');
			this.replaceSidebar('block-cart', response.cart);
			this.replaceTopLinks(response);
			if(response.base_cart && $('shopping-cart-table')){				
				var tbody = $('shopping-cart-table').down('tbody');
				var tempElement = document.createElement('div');		    
			    tempElement.innerHTML = '<table><tbody>' + response.base_cart + '</tbody></table>';			    			    
			    el = tempElement.getElementsByTagName('tbody');		    
			    if (el.length > 0){
			        content = el[0];
			        tbody.parentNode.replaceChild(content, tbody);
			    }
				decorateTable('shopping-cart-table');
				this.prepareCartItem(undefined);
			}
			this.updateCartBlocks(response);
		}	
		else{
			if (response.redirect){
				window.location.href = response.redirect; 
			}else if (response.message){
				alert(response.message);
			}else{
				alert('Error add to cart.');
			}	 
		}			            			
	},
	
	replaceSidebar: function(block_class, content){
		
		var block = $$('div.' + block_class)[0];			
		if (block && content){				
			var js_scripts = content.extractScripts();
						
			if (content && content.toElement){
		    	content = content.toElement();			    	
		    }else if (!Object.isElement(content)){			    	
			    content = Object.toHTML(content);
			    var tempElement = document.createElement('div');
			    content.evalScripts.bind(content).defer();
			    content = content.stripScripts();
			    tempElement.innerHTML = content;
			    el =  getElementsByClassName(block_class, tempElement);
			    if (el.length > 0){
			        content = el[0];
			    }
			    else{
			       return;
			    }
		    }								
			block.parentNode.replaceChild(content, block);				
			for (var i=0; i< js_scripts.length; i++){																
		        if (typeof(js_scripts[i]) != 'undefined'){        	        	
		        	globalEval(js_scripts[i]);                	
		        }
		    }
			if(typeof truncateOptions == 'function') {
				truncateOptions();
			}
		}
	},
	
	replaceTopLinks: function(response){

		this._replaceTopLink('top-link-cart', response);
		this._replaceTopLink('top-link-wishlist', response);
		
	}, 
	
	_replaceTopLink: function(link_class, response){
		
		var link = $$('ul.links a.' + link_class)[0];		
		if (link && response.top_links){				
			
			var content = response.top_links;			
			if (content && content.toElement){
		    	content = content.toElement();			    	
		    }else if (!Object.isElement(content)){			    	
			    content = Object.toHTML(content);
			    var tempElement = document.createElement('div');			    
			    tempElement.innerHTML = content;
			    el =  getElementsByClassName(link_class, tempElement);
			    if (el.length > 0){
			        content = el[0];
			    }
			    else{
			       return;
			    }
		    }								
			link.parentNode.replaceChild(content, link);							
		}
	}, 
	
	onFailureAddtoCart: function(transport){
		this.endLoadData();
		alert('Failure add to cart.');
	},
	
	qtyUp: function(product_id, verification_qty, deals_id){
		
		var qty = $('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value*1 + 1; 
		
		if (qty > this.product_list[product_id].max_qty){
			alert('The maximum quantity allowed for purchase is ' + this.product_list[product_id].max_qty + '.');
			return;
		}
		
		if (!verification_qty){
			$('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value = $('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value*1 + 1;
			return;
		}
		this.startLoadData();
		var params = {product_id: product_id,
		 		      qty: qty,
		 		      deals_id: deals_id};
		
		var request = new Ajax.Request(this.config.changeqty_url,
			            {
			                method:'post',
			                parameters:params,		                
			                onSuccess: this.onSuccesChangeQty.bind(this), 		                	
			                onFailure: this.onFailureChangeQty.bind(this)
			            }
			        );    				
	},
	
	qtyDown: function(product_id, verification_qty, deals_id){
		
		var qty = $('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value*1 - 1;
		
		if (qty < this.product_list[product_id].min_qty){
			alert('The minimum quantity allowed for purchase is ' + this.product_list[product_id].min_qty + '.');
			return;
		}			
		if (!verification_qty){
			$('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value = qty;
			return;
		}	
		this.startLoadData();
		var params = {product_id: product_id,
	 		      	  qty: qty,
	 		      	  deals_id: deals_id};
	
		var request = new Ajax.Request(this.config.changeqty_url,
			            {
			                method:'post',
			                parameters:params,		                
			                onSuccess: this.onSuccesChangeQty.bind(this), 		                	
			                onFailure: this.onFailureChangeQty.bind(this)
			            }
			        ); 
	},
	
	onSuccesChangeQty: function(transport){									
		eval('var response = '+transport.responseText);
		this.endLoadData();
		if(response.error){
			alert(response.message); 
		}	
		else{
			$('gpc_prod_id_' + (response.deals_id ? response.deals_id : response.product_id)).value = response.qty; 
		}			            			
	},
	
	onFailureChangeQty: function(transport){
		this.endLoadData();
		alert('Failure change qty.');
	},
	
	startLoadData: function(){		
		this.overlay.show();
		this.loadinfo.show();				
	},
	
	endLoadData: function(){
		if (this.overlay)
			this.overlay.hide();
		if (this.loadinfo)
			this.loadinfo.hide();
	},
	
	effectSlidetoCart: function(control){
		
		if (this.config.add_effect != '2') return;
		if (this.slide_control == undefined) return;
		
		if (control.id == 'image')
			var img = control;
		else	
			var img = $(control).up('li.item').down('img');
		var cart = $$('div.block-cart')[0];
		
		
		if (img && cart){			
			var img_offsets = img.cumulativeOffset();
			var cart_offsets = cart.cumulativeOffset();
			var animate_img =  img.cloneNode(true);
			animate_img.id = 'glg_animate_img';
			document.body.appendChild(animate_img);			 
			animate_img.setStyle({'position': 'absolute', 
								  'top': img_offsets[1] + 'px', 
								  'left': img_offsets[0] + 'px'});
			
			new Effect.Parallel(			
			    [						    			
			     	new Effect.Fade('glg_animate_img', {sync: true, to: 0.3}),
			     	new Effect.MoveBy('glg_animate_img', cart_offsets[1]-img_offsets[1], cart_offsets[0]-img_offsets[0], {sync: true})			     	 
			    ],			
			    {duration: 2,
			     afterFinish: function(){
			    				$('glg_animate_img').remove();	
			    			  }			    	
			    }			
			);
		}						
	},
	
	qtyUpSidebar: function(item_id){
		
		this.startLoadData();
		var params = {item_id: item_id,
	 		      	  qty: $('gpc_sidebar_' + item_id).value*1 + 1,
	 		      	  sidebar: 1};
		
		var request = new Ajax.Request(this.config.changeqtycartitem_url,
			            {
			                method:'post',
			                parameters:params,		                
			                onSuccess: this.onSuccesChangeQtySidebar.bind(this), 		                	
			                onFailure: this.onFailureChangeQtySidebar.bind(this)
			            }
			        );    				
	},
	
	qtyDownSidebar: function(item_id){
		if ($('gpc_sidebar_' + item_id).value*1 == 1){
			alert('The minimum quantity allowed for purchase is 1.');
			return;
		}			
		this.startLoadData();
		var params = {item_id: item_id,
		      	  	  qty: $('gpc_sidebar_' + item_id).value*1 - 1,
		      	  	  sidebar: 1};
	
		var request = new Ajax.Request(this.config.changeqtycartitem_url,
			            {
			                method:'post',
			                parameters:params,		                
			                onSuccess: this.onSuccesChangeQtySidebar.bind(this), 		                	
			                onFailure: this.onFailureChangeQtySidebar.bind(this)
			            }
			        ); 
	},
	
	onSuccesChangeQtySidebar: function(transport){									
		eval('var response = '+transport.responseText);
		this.endLoadData();
		if(response.error){
			alert(response.message); 
		}	
		else{
			if (response.product_id && this.product_list[response.product_id] && response.max_qty){
				this.product_list[response.product_id].max_qty = response.max_qty;
			}
			this.replaceSidebar('block-cart', response.cart);
			this.replaceTopLinks(response);
		}			            			
	},
	
	onFailureChangeQtySidebar: function(transport){
		this.endLoadData();
		alert('Failure change qty.');
	},
	
	deleteItem: function(url){
		
		this.startLoadData();
		var params = {};
		if ($('shopping-cart-table'))
			params.gpc_cart_delete = 1;
		else
			params.gpc_sedebar_delete = 1;
	
		var request = new Ajax.Request(url,
	            {
	                method:'post',
	                parameters:params,		                
	                onSuccess: this.onSuccesDeleteItem.bind(this), 		                	
	                onFailure: this.onFailureDeleteItem.bind(this)
	            }
	        );
	},
	
	onSuccesDeleteItem: function(transport){									
		eval('var response = '+transport.responseText);
		this.endLoadData();
		if(response.error){
			alert(response.message); 
		}	
		else{	
			if (response.redirect){
				setLocation(response.redirect);
				return;
			}
			if (response.item_id){
				$('gpc_cart_item_' + response.item_id).up('td').up('tr').remove();
				this.updateCartBlocks(response);
			}
			this.replaceSidebar('block-cart', response.cart);
			this.replaceTopLinks(response);
			if (response.product_id && this.product_list[response.product_id] && response.max_qty){
				this.product_list[response.product_id].max_qty = response.max_qty;
			}
		}			            			
	},
	
	onFailureDeleteItem: function(transport){
		this.endLoadData();
		alert('Cannot remove the item.');
	},
	
	qtyCartUp: function(item_id){
		
		this.startLoadData();
		var params = {item_id: item_id,
	 		      	  qty: $('gpc_cart_item_' + item_id).value*1 + 1,
	 		      	  cart: 1};
		
		var request = new Ajax.Request(this.config.changeqtycartitem_url,
			            {
			                method:'post',
			                parameters:params,		                
			                onSuccess: this.onSuccesChangeQtyCart.bind(this), 		                	
			                onFailure: this.onFailureChangeQtyCart.bind(this)
			            }
			        );    				
	},
	
	qtyCartDown: function(item_id){
		if ($('gpc_cart_item_' + item_id).value*1 == 1){
			alert('The minimum quantity allowed for purchase is 1.');
			return;
		}			
		this.startLoadData();
		var params = {item_id: item_id,
		      	  	  qty: $('gpc_cart_item_' + item_id).value*1 - 1,
		      	  	  cart: 1};
	
		var request = new Ajax.Request(this.config.changeqtycartitem_url,
			            {
			                method:'post',
			                parameters:params,		                
			                onSuccess: this.onSuccesChangeQtyCart.bind(this), 		                	
			                onFailure: this.onFailureChangeQtyCart.bind(this)
			            }
			        ); 
	},
	
	onSuccesChangeQtyCart: function(transport){									
		eval('var response = '+transport.responseText);
		this.endLoadData();		
		if(response.error){
			if (response.update_attribute){				
				var elements = $('gpc_cart_item_' + response.item_id).up('td').up('tr').select('select');
				for(var i=0; i<elements.length; i++){
					var attribute_id = elements[i].getAttribute('class');
					attribute_id = attribute_id.replace(/\D/g, ''); 
					if (response.success_param.hasOwnProperty(attribute_id)){
						continue;
					}else if(attribute_id == response.update_attribute){
						elements[i].options.length = 0;
						elements[i].options[elements[i].options.length] = new Option(response.choosetext, '', false, false);
						
						for (key in response.attribute_data){
							elements[i].options[elements[i].options.length] = new Option(response.attribute_data[key], key, false, false);
						}

					}else{
						elements[i].options.length = 0;
						elements[i].options[elements[i].options.length] = new Option(response.choosetext, '', false, false);
					}
				}	
			}else{	
				this.replaceCartItems(response);
				alert(response.message); 
			}	
		}	
		else{
			this.replaceCartItems(response); 	
			this.updateCartBlocks(response);
			this.replaceTopLinks(response);
		}			            			
	},
	
	onFailureChangeQtyCart: function(transport){
		this.endLoadData();
		alert('Failure change qty.');
	},
	
	replaceCartItems: function(response){					
		if ($('shopping-cart-table') && response.items_html){
			
			var tbody = $('shopping-cart-table').down('tbody');
			var tempElement = document.createElement('div');		    
		    tempElement.innerHTML = '<table><tbody>' + response.items_html + '</tbody></table>';			    			    
		    el = tempElement.getElementsByTagName('tbody');		    
		    if (el.length > 0){
		        content = el[0];
		        tbody.parentNode.replaceChild(content, tbody);
		    }
			decorateTable('shopping-cart-table');
			this.prepareCartItem(undefined);						
		}
	},
	
	updateCartBlocks: function(response){		
		this._updateCartBlock($('shopping-cart-totals-table'), response.total);
		this._updateCartBlock($$('div.shipping')[0], response.shipping);
	},
	
	_updateCartBlock: function(block, content){					
		if (block && content){	
			
			var js_scripts = content.extractScripts();
			content = content.stripScripts();
									
			if (content && content.toElement){
		    	content = content.toElement();			    	
		    }else if (!Object.isElement(content)){			    	
			    content = Object.toHTML(content);
			    var tempElement = document.createElement('div');			    
			    tempElement.innerHTML = content;
			    el =  tempElement.getElementsByTagName('table');			    
			    if (el.length > 0){
			        content = el[0];
			    }else{
			    	content = tempElement.firstChild;
			    }
		    }								
			block.parentNode.replaceChild(content, block);
			
			for (var i=0; i< js_scripts.length; i++){																
		        if (typeof(js_scripts[i]) != 'undefined'){        	        	
		        	globalEval(js_scripts[i]);                	
		        }
		    }	
			
		}
	}, 
	
	attributeCartChange: function(control, product_id){		
		var item_id = $(control).up('td').up('tr').down('input.qty').name;
		item_id = item_id.replace(/\D/g, '');
		var super_attribute = {};
				
		if ($(control).up('td').up('tr').select('select').length > 0){
			var attributes = $(control).up('td').up('tr').select('select');
			for(var i=0; i<attributes.length; i++){				
				var attribute_id = $(attributes[i]).className;
				attribute_id = attribute_id.replace(/\D/g, '');
				super_attribute[attribute_id] = $(attributes[i]).value;
			}	
		}
	
		this.startLoadData();
								
		var params = {'id': item_id,
					  'product': product_id,
					  'super_attribute': Object.toJSON(super_attribute)};
	
		var request = new Ajax.Request(this.config.changeattributecart_url,
			            {
			                method:'post',
			                parameters: params,		                
			                onSuccess: this.onSuccesChangeQtyCart.bind(this), 		                	
			                onFailure: this.onFailureChangeQtyCart.bind(this)
			            }
			        ); 
	},
	
	showConfirmationWindow: function(response, type){
		if (this.config.show_window == '1'){
			this.confirmation_window_type = type;
			$$('span.gpc_cw_add_to').each(Element.hide);
			$('gpc_cw_add_to_' + type).show();
			if (parseInt(response.qty) >= 1){
				$('gpc_conf_win_qty').innerHTML = response.qty;
			}
			else{
				$('gpc_conf_win_qty').innerHTML = '';
			}
			$('gpc_conf_win_prod_name').innerHTML = response.prod_name;
			$('gpc_conf_win_was').hide();
			$('gpc_conf_win_were').hide();
			if (parseInt(response.qty) > 1)
				$('gpc_conf_win_were').show();
			else
				$('gpc_conf_win_was').show();
			
			for (key in gomage_procart_goto_data){
				if (key == this.confirmation_window_type){					
					$('gpc_confirm_window_checkout').up('button').setAttribute("onclick", gomage_procart_goto_data[key].onclick);
					if (Prototype.Browser.IE){						
						$('gpc_confirm_window_checkout').up('button').onclick = function() {																				    
						    for (_key in gomage_procart_goto_data){
								if (_key == GomageProcartConfig.confirmation_window_type){
									globalEval(gomage_procart_goto_data[_key].onclick);
								}
							}
						};
					}
					$('gpc_confirm_window_checkout').innerHTML = gomage_procart_goto_data[key].text;
				}
			}
			
			this.overlay.show();
			$('gpc_confirmation_window').show();
			
			var auto_hide_window = this.config.auto_hide_window;
			this.auto_hide_window = parseInt(auto_hide_window);
			if (this.auto_hide_window > 0){
				this.setTimeout();
			}				
		}	
	},
	
	setTimeout: function(){
		var text = '';
		var onclick = '';
		for (key in gomage_procart_goto_data){
			if (key == this.confirmation_window_type){
				text = gomage_procart_goto_data[key].text;
				onclick = gomage_procart_goto_data[key].onclick;
			}
		}
		if (this.auto_hide_window > 0 && $('gpc_confirmation_window').visible()) {
			if (this.config.redirect_to == '1')
				$('gpc_confirm_window_checkout').innerHTML = text + ' (' + this.auto_hide_window + ')';
			else	
				$('gpc_confirm_window_continue').innerHTML = gomage_procart_continue_text + ' (' + this.auto_hide_window + ')';
			window.setTimeout(function() { 				  
				GomageProcartConfig.setTimeout();
			}, 1000);
			this.auto_hide_window = this.auto_hide_window - 1;
		}else{			
			if (this.config.redirect_to == '1' && $('gpc_confirmation_window').visible()){				
				globalEval(onclick);
			}
			GomageProcartConfig.overlay.hide();
			$('gpc_confirmation_window').hide();
		}
	},
	
	qtyProductUp: function(prod_id){
		if (productAddToCartForm.validator.validate()){
			
			var product_id = $$('input[name="product"]').first().value;
			if($('qty')){
				var qty = $('qty').value*1 + 1;
				if (qty > this.product_list[product_id].max_qty){
					alert('The maximum quantity allowed for purchase is ' + this.product_list[product_id].max_qty + '.');
					return;
				}
			}else if($('gpc_prod_grouped_' + prod_id)){
				var qty = $('gpc_prod_grouped_' + prod_id).value*1 + 1;
				if (qty > this.product_list[prod_id].max_qty){
					alert('The maximum quantity allowed for purchase is ' + this.product_list[prod_id].max_qty + '.');
					return;
				}
				product_id = prod_id;
			}
			
			this.startLoadData();
			var params = {product_id: product_id,
			 		      qty: qty};
			
			var request = new Ajax.Request(this.config.changeproductqty_url,
				            {
				                method:'post',
				                parameters:params,		                
				                onSuccess: this.onSuccesChangeProductQty.bind(this), 		                	
				                onFailure: this.onFailureChangeProductQty.bind(this)
				            }
				        );    				
		}	
	},
	
	qtyProductDown: function(prod_id){
		if (productAddToCartForm.validator.validate()){
			
			var product_id = $$('input[name="product"]').first().value;
			if($('qty')){
				var qty = $('qty').value*1 - 1;			
				if (qty < this.product_list[product_id].min_qty){
					alert('The minimum quantity allowed for purchase is ' + this.product_list[product_id].min_qty + '.');
					return;
				}
			}else if($('gpc_prod_grouped_' + prod_id)){
				var qty = $('gpc_prod_grouped_' + prod_id).value*1 - 1;			
				if (qty < this.product_list[prod_id].min_qty){
					alert('The minimum quantity allowed for purchase is ' + this.product_list[prod_id].min_qty + '.');
					return;
				}
				product_id = prod_id;
			}	
			
			this.startLoadData();
			var params = {product_id: product_id,
						  qty: qty};
		
			var request = new Ajax.Request(this.config.changeproductqty_url,
				            {
				                method:'post',
				                parameters:params,		                
				                onSuccess: this.onSuccesChangeProductQty.bind(this), 		                	
				                onFailure: this.onFailureChangeProductQty.bind(this)
				            }
				        ); 
		}	
	},
	
	onSuccesChangeProductQty: function(transport){									
		eval('var response = '+transport.responseText);
		this.endLoadData();
		if(response.error){
			alert(response.message); 
		}	
		else{
			if($('qty')){
				$('qty').value = response.qty;
			}else if($('gpc_prod_grouped_' + response.product_id)){
				$('gpc_prod_grouped_' + response.product_id).value = response.qty;
			}	
		}			            			
	},
	
	onFailureChangeProductQty: function(transport){
		this.endLoadData();
		alert('Failure change qty.');
	},
	
	showGroupedParams: function(response){			
				
		this.js_scripts = response.form.extractScripts();
		this.grouped_qty = response.qty;
		var win = new GcpWindow('gcp_configurable_add_to_cart', 
				{className: "gomage_cp",
				 additionClass: "gpc-confw-buttons-" + GomageProcartConfig.config.cart_button_color,
			     title: 'Add to Cart', 
			     width: 400, 
			     top: '50%',
			     destroyOnClose: true,
			     closeOnEsc: false,
			     showEffectOptions: {afterFinish: function(){
											for (var i=0; i<GomageProcartConfig.js_scripts.length; i++){																
										        if (typeof(GomageProcartConfig.js_scripts[i]) != 'undefined'){        	        	
										        	globalEval(GomageProcartConfig.js_scripts[i]);                	
										        }
										    }
											$('super-product-table').select('input[type=text]').each(function(control){
												$(control).value = GomageProcartConfig.grouped_qty;
											}); 
											if ($('overlay_modal')){
												$('overlay_modal').onclick = function() {					 				     
													var _win = GcpWindows.getWindow('gcp_configurable_add_to_cart');
													if(_win) _win.close();
												};
											}
										}
									}
		}); 
		win.getContent().innerHTML = response.form.stripScripts();			
		win.showCenter(parseInt(this.config.background_view));											            			
	},
	
	addtoCartGrouped: function(form){
		 
		this.startLoadData();
		
		var elements = form.getElements('input, select, textarea');		
		var params = {};		
		for(var i = 0;i < elements.length;i++){
			if((elements[i].type == 'checkbox' || elements[i].type == 'radio') && !elements[i].checked){
				continue;
			}				
			if (elements[i].disabled){
				continue;
			}				
			params[elements[i].name] = elements[i].value;
		}	
		var request = new Ajax.Request(form.action,
	            {
	                method:'post',
	                parameters:params,		                
	                onSuccess: this.onSuccesAddtoCart.bind(this), 		                	
	                onFailure: this.onFailureAddtoCart.bind(this)
	            }
	        );   
	}	
		
};	

function ProcartGetUrlParam(url, name){	
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = "[\\?&]"+name+"=([^&#]*)";
  var regex = new RegExp( regexS );  
  var results = regex.exec( url );  
  if( results == null )
    return "";
  else
    return results[1];  
}

var globalEval = function globalEval(src){
    if (window.execScript) {
        window.execScript(src);
        return;
    }
    var fn = function() {
        window.eval.call(window,src);
    };
    fn();
};

function getElementsByClassName(classname, node){
    var a = [];
    var re = new RegExp('\\b' + classname + '\\b');
    var els = node.getElementsByTagName("*");
    for(var i=0,j=els.length; i<j; i++){
           if(re.test(els[i].className))a.push(els[i]);
    } 
    return a;
}