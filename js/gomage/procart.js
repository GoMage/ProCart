/**
 * GoMage ProCart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2016 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use
 * @version      Release: 2.2.0
 * @since        Class available since Release 1.0
 */

Event.observe(window, 'load', function () {
        GoMageProCartCreate();
    }
);

function GoMageProCartCreate() {
    if (typeof(gomage_procart_config) == 'object') {
        GomageProcartConfig = new GomageProcartConfigClass(gomage_procart_config);
    }

    setPLocation = function (url, setFocus) {
        if (setFocus) {
            window.opener.focus();
        }
        if ($('product_comparison')) {
            if (url.indexOf('?') > 0) {
                url += '&';
            } else {
                url += '?';
            }
            url += 'gpc_from_compare=1';
        }
        window.opener.location.href = url;
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
    configurable_min_qty: null,
    configurable_max_qty: null,
    configurable_current_simple: null,
    bundle_selection_hash: null,
    removeLayout: false,
    leaveCartBlock: false,
	infortis_ultimo: false,

    initialize: function (config) {

        this.config = config;
        if (this.config.enable != '1') return;

        if (this.add_to_cart_onclick_str == null) {
            this.add_to_cart_onclick_str = new Array();
        }
        if (this.addition_product_list_ids == null) {
            this.addition_product_list_ids = new Array();
        }

        if (this.product_list == null) {
            this.product_list = {};
        }
        if (this.bundle_selection_hash == null) {
            this.bundle_selection_hash = new Hash();
        }

        this.qty_template					= gomage_procart_qty_template;
        this.qty_deals_template				= gomage_procart_qty_deals_template;
        this.qty_cart_template				= gomage_procart_qty_cart_template;
        this.qty_product_template			= gomage_procart_qty_product_template;
        this.qty_crosssell_template			= gomage_procart_qty_crosssell_template;
        this.qty_upsell_template			= gomage_procart_qty_upsell_template;
        this.qty_category_popup_template	= gomage_procart_qty_category_popup_template;
		this.infortis_ultimo				= gomage_procart_infortis_ultimo;

        if ($$('div.category-products').length > 0) {

            if (typeof(gomage_procart_product_list) != 'undefined') {
                for (var key in gomage_procart_product_list) {
                    this.product_list[key] = gomage_procart_product_list[key];
                }
            }
            if (typeof(gomage_procart_bundle_selection_hash) != 'undefined') {
                for (var key in gomage_procart_bundle_selection_hash) {
                    this.bundle_selection_hash[key] = gomage_procart_bundle_selection_hash[key];
                }
            }
            var elements = $$('div.category-products button.btn-cart, div.category-products a.button');

            for (var i = 0; i < elements.length; i++) {

                var onclick_str = '';

                if (elements[i].attributes["onclick"]) {
                    onclick_str = elements[i].attributes["onclick"].nodeValue;
                    try {
                        onclick_str = onclick_str.toString().match(/\'.*?\'/);
                        onclick_str = onclick_str[0].replace(/\'/g, '');
                    } catch (e) {
                        continue;
                    }
                }
                else if (elements[i].attributes["href"]) {
                    for (var key in this.product_list) {
                        if (this.product_list[key].product_url == elements[i].attributes["href"].nodeValue) {
                            onclick_str = this.product_list[key].addtocart_url;
                        }
                    }
                }

                if (!onclick_str) {
                    continue;
                }

                var product_id = ProcartGetUrlParam(onclick_str, 'gpc_prod_id');
                if (!product_id) {
                    continue;
                }

                elements[i].onclick = function () {
                    GomageProcartConfig.addtoCart(this);
                    return false;
                };

                if ($('gpc_prod_id_' + product_id)) {
                    continue;
                }

                var qty_div = $(document.createElement('span'));
                qty_div.addClassName('gpc_qty_edit');

                var verification_qty = false;
                if (onclick_str.search('checkout/cart/add') != -1) {
                    verification_qty = true;
                }
                if (this.config.qty_editor_category_page != '1') {
                    qty_div.addClassName('hidden');
                }
                qty_div.innerHTML = this.qty_template.replace(/#gpc_prod_id/g, product_id).replace(/#verification_qty/g, verification_qty);
                new Insertion.After(elements[i], qty_div);

                if (typeof(this.product_list[product_id]) == 'undefined') {
                    this.addition_product_list_ids.push(product_id);
                    $('gpc_prod_id_' + product_id).value = 1;
                } else {
                    $('gpc_prod_id_' + product_id).value = this.product_list[product_id].increments;
                }

                elements[i].id = 'gcp_add_to_cart_' + this.add_to_cart_onclick_str.length;
                this.add_to_cart_onclick_str[this.add_to_cart_onclick_str.length] = onclick_str;

            }
        }

        this.prepareCartItem(undefined);
        this.prepareProductPage();
        this.prepareWishlist();
        this.prepareCompare();

        this.prepareCrosssell();
        this.prepareUpsell();
        this.prepareRelated();
        this.prepareGomageSalesDeals();

        this.overlay = $('gomage-cartpro-overlay');
        if (!this.overlay) {
            var element = $$('body')[0];
            this.overlay = $(document.createElement('div'));
            this.overlay.id = 'gomage-cartpro-overlay';
            document.body.appendChild(this.overlay);

            var offsets = element.cumulativeOffset();
            this.overlay.setStyle({
                'top': offsets[1] + 'px',
                'left': offsets[0] + 'px',
                'width': element.offsetWidth + 'px',
                'height': element.offsetHeight + 'px',
                'position': 'absolute',
                'display': 'block',
                'zIndex': '2000'
            });

            if (this.config.background_view == '1') {
                this.overlay.setStyle({
                    'opacity': '0.6',
                    'background': '#000000'
                });
            } else {
                this.overlay.setStyle({
                    'background': 'none'
                });
            }

            this.loadinfo = $(document.createElement('div'));
            if (this.config.loadimagealign == 'bottom')
                this.loadinfo.innerHTML = this.config.gpc_loadinfo_text + '<img src="' + this.config.loadimage + '" alt="" class="align-' + this.config.loadimagealign + '"/>';
            else
                this.loadinfo.innerHTML = '<img src="' + this.config.loadimage + '" alt="" class="align-' + this.config.loadimagealign + '"/>' + this.config.gpc_loadinfo_text;

            this.loadinfo.id = "gomage-cartpro-loadinfo";
            this.loadinfo.className = "gpc-loadinfo";
            document.body.appendChild(this.loadinfo);

            this.overlay.onclick = function () {
                if ($('gpc_confirmation_window').visible()) {
                    GomageProcartConfig.overlay.hide();
                    $('gpc_confirmation_window').hide();
                }
            };

        }
        if (this.overlay && this.overlay.visible()) this.overlay.hide();
        if (this.loadinfo && this.loadinfo.visible()) this.loadinfo.hide();

        this.addAdditionProduct();

    },

    addAdditionProduct: function () {
        if (this.addition_product_list_ids.length) {
            var params = {product_ids: this.addition_product_list_ids.join(',')};

            var request = new Ajax.Request(this.config.addition_product_list_url,
                {
                    method: 'post',
                    parameters: params,
                    onSuccess: function (transport) {
                        eval('var response = ' + transport.responseText);
                        if (response.product_list) {
                            for (var product_id in response.product_list) {
                                this.product_list[product_id] = response.product_list[product_id];
                            }
                        }
                        if (response.bundle_selection_hash) {
                            for (var key in response.bundle_selection_hash) {
                                this.bundle_selection_hash[key] = response.bundle_selection_hash[key];
                            }
                        }
                        this.addition_product_list_ids = new Array();

                        this.updateCrosssellQty();
                        this.updateAdditionalProducts();

                    }.bind(this),
                    onFailure: function () {
                    }.bind(this)
                }
            );
        }
    },

    updateAdditionalProducts: function () {
        if ($$('div.category-products').length > 0) {
            var elements = new Array();
            $$('div.category-products').each(function (element) {
                var buttons = element.getElementsByClassName('btn-cart');
                for (var i = 0; i < buttons.length; i++) {
                    elements.push(buttons[i]);
                }
            });
            for (var i = 0; i < elements.length; i++) {

                var onclick_str = elements[i].attributes["onclick"].nodeValue;

                try {
                    onclick_str = onclick_str.toString().match(/\'.*?\'/);
                    onclick_str = onclick_str[0].replace(/\'/g, '');
                } catch (e) {
                    continue;
                }
                var product_id = ProcartGetUrlParam(onclick_str, 'gpc_prod_id');

                if (typeof(this.product_list[product_id]) == 'undefined') {
                    this.addition_product_list_ids.push(product_id);
                    $('gpc_prod_id_' + product_id).value = 1;
                } else {
                    $('gpc_prod_id_' + product_id).value = this.product_list[product_id].increments;
                }
            }
        }
    },

    updateCrosssellQty: function () {

        if ($$('div.crosssell')[0]) {
            var elements = $$('div.crosssell')[0].getElementsByClassName('btn-cart');

            for (var i = 0; i < elements.length; i++) {
                var onclick_str = elements[i].attributes["onclick"].value;
                onclick_str = onclick_str.toString().match(/\'.*?\'/);
                onclick_str = onclick_str[0].replace(/\'/g, '');

                var re = new RegExp('\/' + this.config.name_url_encoded + '\/.*?\/', 'g');
                onclick_str = onclick_str.replace(re, '/');

                var product_id = ProcartGetUrlParam(onclick_str, 'gpc_prod_id');

                if (this.product_list[product_id]) {
                    $('gpc_prod_id_' + product_id).value = this.product_list[product_id].increments;
                }
            }
        }
    },

    prepareGomageSalesDeals: function () {

        var blocks = $$('div.gomage-sd');

        var _SalesDealsData = {};
        if ($$('div.gomage-sd').length) {

            for (var bi = 0; bi < blocks.length; bi++) {
                var elements = blocks[bi].getElementsByClassName('btn-cart');
                for (var i = 0; i < elements.length; i++) {

                    var deals_id = $(elements[i]).up('li.item').id;

                    if (gpc_preparedGomageSalesDeals) {
                        for (var key in gpc_SalesDealsData) {
                            if (gpc_SalesDealsData.hasOwnProperty(key)) {
                                if (key == elements[i].id) {
                                    var onclick_str = gpc_SalesDealsData[key].click;
                                }
                            }
                        }
                    } else {
                        var onclick_str = elements[i].attributes["onclick"].nodeValue;
                    }

                    var base_onclick_str = onclick_str;
                    onclick_str = onclick_str.toString().match(/\'.*?\'/);
                    onclick_str = onclick_str[0].replace(/\'/g, '');
                    var product_id = ProcartGetUrlParam(onclick_str, 'gpc_prod_id');
                    if (!product_id) {
                        product_id = ProcartGetUrlParam(onclick_str, 'product');
                        if (!product_id) {
                            continue;
                        }
                    }
                    if (this.config.qty_editor_category_page == '1') {
                        var qty_div = $(document.createElement('div'));
                        qty_div.addClassName('gpc_qty_edit');

                        var verification_qty = false;
                        if (onclick_str.search('checkout/cart/add') != -1) {
                            verification_qty = true;
                        }
                        qty_div.innerHTML = this.qty_deals_template.replace(/#gpc_prod_id/g, product_id).replace(/#verification_qty/g, verification_qty).replace(/#deals_id/g, deals_id);

                        if (!gpc_preparedGomageSalesDeals) {
                            new Insertion.After(elements[i], qty_div);
                            $('gpc_prod_id_' + deals_id).value = 1;
                        }
                    }

                    elements[i].onclick = function () {
                        GomageProcartConfig.addtoCart(this);
                    };
                    elements[i].id = 'gcp_add_to_cart_' + this.add_to_cart_onclick_str.length;

                    _SalesDealsData[elements[i].id] = {click: base_onclick_str, deals: deals_id};

                    this.add_to_cart_onclick_str[this.add_to_cart_onclick_str.length] = onclick_str;

                    if (typeof(this.product_list[product_id]) == 'undefined') {
                        this.addition_product_list_ids.push(product_id);
                    }

                }

            }
        }
        gpc_SalesDealsData = _SalesDealsData;
        gpc_preparedGomageSalesDeals = true;

    },

    prepareCrosssell: function () {

        if ($$('div.crosssell')[0]) {
            var elements = $$('div.crosssell')[0].getElementsByClassName('btn-cart');

            if (gomage_procart_product_list) {
                this.product_list = gomage_procart_product_list;
            }

            for (var i = 0; i < elements.length; i++) {
                var onclick_str = elements[i].attributes["onclick"].value;
                onclick_str = onclick_str.toString().match(/\'.*?\'/);
                onclick_str = onclick_str[0].replace(/\'/g, '');

                var re = new RegExp('\/' + this.config.name_url_encoded + '\/.*?\/', 'g');
                onclick_str = onclick_str.replace(re, '/');

                var product_id = ProcartGetUrlParam(onclick_str, 'gpc_prod_id');
                if (!product_id) continue;

                var qty_div = $(document.createElement('div'));
                qty_div.addClassName('gpc_qty_edit');

                var verification_qty = false;
                if (onclick_str.search('checkout/cart/add') != -1) {
                    verification_qty = true;
                }

                qty_div.innerHTML = this.qty_crosssell_template.replace(/#gpc_prod_id/g, product_id).replace(/#verification_qty/g, verification_qty);

                new Insertion.After(elements[i], qty_div);

                if (this.product_list[product_id]) {
                    $('gpc_prod_id_' + product_id).value = this.product_list[product_id].increments;
                }

                elements[i].onclick = function () {
                    GomageProcartConfig.addtoCart(this);
                };
                elements[i].id = 'gcp_add_to_cart_' + this.add_to_cart_onclick_str.length;
                this.add_to_cart_onclick_str[this.add_to_cart_onclick_str.length] = onclick_str;

                if (typeof(this.product_list[product_id]) == 'undefined') {
                    this.addition_product_list_ids.push(product_id);
                }
            }
        }
    },

    prepareUpsell: function () {
        if ($$('div.box-up-sell')[0]) {
            var elements = $$('div.box-up-sell')[0].getElementsByClassName('btn-cart');

            for (var i = 0; i < elements.length; i++) {

                var onclick_str = elements[i].attributes["onclick"].value;
                onclick_str = onclick_str.toString().match(/\'.*?\'/);
                onclick_str = onclick_str[0].replace(/\'/g, '');

                var re = new RegExp('\/' + this.config.name_url_encoded + '\/.*?\/', 'g');
                onclick_str = onclick_str.replace(re, '/');

                var product_id = ProcartGetUrlParam(onclick_str, 'gpc_prod_id');

                if (!product_id) continue;

                var qty_div = $(document.createElement('div'));
                qty_div.addClassName('gpc_qty_edit');

                var verification_qty = false;
                if (onclick_str.search('checkout/cart/add') != -1) {
                    verification_qty = true;
                }

                qty_div.innerHTML = this.qty_upsell_template.replace(/#gpc_prod_id/g, product_id).replace(/#verification_qty/g, verification_qty);

                new Insertion.After(elements[i], qty_div);

                if (this.product_list[product_id]) {
                    $('gpc_prod_id_' + product_id).value = this.product_list[product_id].increments;
                }

                elements[i].onclick = function () {
                    GomageProcartConfig.addtoCart(this);
                };
                elements[i].id = 'gcp_add_to_cart_' + this.add_to_cart_onclick_str.length;
                this.add_to_cart_onclick_str[this.add_to_cart_onclick_str.length] = onclick_str;

                if (typeof(this.product_list[product_id]) == 'undefined') {
                    this.addition_product_list_ids.push(product_id);
                }

            }
        }
    },

    prepareRelated: function () {
        var block = $$('div.block-related')[0];

        if (!block) {
            block = $$('div.box-related')[0];
        }

        if (block) {
            var elements = block.getElementsByClassName('btn-cart');

            for (var i = 0; i < elements.length; i++) {

                var onclick_str = elements[i].attributes["onclick"].value;
                onclick_str = onclick_str.toString().match(/\'.*?\'/);
                onclick_str = onclick_str[0].replace(/\'/g, '');

                var re = new RegExp('\/' + this.config.name_url_encoded + '\/.*?\/', 'g');
                onclick_str = onclick_str.replace(re, '/');

                var product_id = ProcartGetUrlParam(onclick_str, 'gpc_prod_id');

                if (!product_id) continue;

                var qty_div = $(document.createElement('div'));
                qty_div.addClassName('gpc_qty_edit');

                var verification_qty = false;
                if (onclick_str.search('checkout/cart/add') != -1) {
                    verification_qty = true;
                }

                elements[i].onclick = function () {
                    GomageProcartConfig.addtoCart(this);
                };
                elements[i].id = 'gcp_add_to_cart_' + this.add_to_cart_onclick_str.length;
                this.add_to_cart_onclick_str[this.add_to_cart_onclick_str.length] = onclick_str;

                if (typeof(this.product_list[product_id]) == 'undefined') {
                    this.addition_product_list_ids.push(product_id);
                }

            }
        }
    },

    prepareWishlist: function () {
        if ($('product_comparison')) return;

        var elements = $$('a.link-wishlist, a[href*="wishlist/index/updateItemOptions"].link-compare');
        for (var i = 0; i < elements.length; i++) {
            Event.stopObserving(elements[i], 'click');
            Event.observe(elements[i], 'click', this.addToWishlist.bind(this), false);
            elements[i].onclick = function () {
                return false;
            };
        }

        var elements = $$('#shopping-cart-table input[type="checkbox"]');
        for (var i = 0; i < elements.length; i++) {
            if (elements[i].name.indexOf('[wishlist]') > 0) {
                Event.stopObserving(elements[i], 'click');
                Event.observe(elements[i], 'click', this.addToWishlist.bind(this), false);
            }
        }
    },

    addToWishlist: function (event) {

        Event.stop(event);
        var element = Event.element(event);

        var params = {gpc_wishlist_add: 1};
        var url = '';
        if (element.href) {
            url = element.href.replace("https", "http");
        } else if (element.parentNode.href) {
			url = element.parentNode.href.replace("https", "http");
		} else {
            var item_id = element.name;
            item_id = item_id.replace(/\D/g, '');
            if (!item_id) {
                return;
            }
            params['item_id'] = item_id;
            url = this.config.movetowishlist_url;
        }

        if ($('shopping-cart-table')) {
            params['cart'] = 1;
        }
        if ($('qty')) {
            var qty = $('qty').value;
            qty = parseInt(qty);
            if (qty) {
                params['qty'] = qty;
            }
        }

        if (url.indexOf('updateItemOptions') > 0 && $('product_addtocart_form')) {
            var form_params = $('product_addtocart_form').serialize(true);
            for (var key in form_params) {
                if (form_params.hasOwnProperty(key) && (key != 'gpc_add')) {
                    params[key] = form_params[key];
                }
            }
        }

        this.startLoadData();

        var request = new Ajax.Request(url,
            {
                method: 'post',
                parameters: params,
                onSuccess: function (transport) {
                    eval('var response = ' + transport.responseText);
                    this.endLoadData();
                    if (response.redirect) {
                        window.location.href = response.redirect;
                        return;
                    }
                    this.replaceCartItems(response);
                    this.showConfirmationWindow(response, 'wishlist');
                    if ($('shopping-cart-table')) {
                        this.prepareWishlist();
                    }
                    var result = this.replaceTopLinks(response);
                    if (!result) {
                        this._replaceEnterpriseTopWishlist(response.top_links);
                    }
                    this.replaceSidebar('block-wishlist', response.wishlist);
                    this.updateCartBlocks(response);
                }.bind(this),
                onFailure: function () {
                    this.endLoadData();
                    alert('Error add to Wishlist');
                }.bind(this)
            }
        );
    },

    deleteWishlistItem: function (url) {

        var params = {};

        this.startLoadData();

        var request = new Ajax.Request(url,
            {
                method: 'post',
                parameters: params,
                onSuccess: function (transport) {
                    eval('var response = ' + transport.responseText);
                    this.endLoadData();
                    if (response.message) {
                        alert(response.message);
                    } else {
                        var result = this.replaceTopLinks(response);
                        if (!result) {
                            this._replaceEnterpriseTopWishlist(response.top_links);
                        }
                        this.replaceSidebar('block-wishlist', response.wishlist);
                    }
                }.bind(this),
                onFailure: function () {
                    this.endLoadData();
                    alert('Error add to Wishlist');
                }.bind(this)
            }
        );

    },

    prepareCompare: function () {
        var elements = $$('a[href*="product_compare/add"]');
        for (var i = 0; i < elements.length; i++) {
            Event.observe(elements[i], 'click', this.addToCompare.bind(this), false);
            elements[i].onclick = function () {
                return false;
            };
        }
    },

    addToCompare: function (event) {
        Event.stop(event);
        var element = Event.element(event);
        var url = '';
		
		if (element.href) {
            url = element.href;
        } else if (element.parentNode.href) {
			url = element.parentNode.href;
		}
		
        var params = {gpc_compare_add: 1};

        this.startLoadData();

        var request = new Ajax.Request(url,
            {
                method: 'post',
                parameters: params,
                onSuccess: function (transport) {
                    eval('var response = ' + transport.responseText);
                    this.endLoadData();
                    if (response.compare_products) {
                        this.replaceSidebar('block-compare', response.compare_products);
                    }
                    this.showConfirmationWindow(response, 'compare');

                }.bind(this),
                onFailure: function () {
                    this.endLoadData();
                    alert('Error add to Compare');
                }.bind(this)
            }
        );
    },

    deleteCompareItem: function (url) {

        var params = {};

        this.startLoadData();
        var uencRx = new RegExp("uenc/.+")
        var found = uencRx.exec($('compare-items').next('.actions').down('a').href);
        var oldUenc = found ? found[0] : '';
        var request = new Ajax.Request(url,
            {
                method: 'post',
                parameters: params,
                onSuccess: function (transport) {
                    eval('var response = ' + transport.responseText);
                    this.endLoadData();
                    this.endLoadData();
                    if (response.compare_products) {
                        this.replaceSidebar('block-compare', response.compare_products);
                        var remComRemAllComp = $('compare-items').next('.actions').down('a');
                        remComRemAllComp.href = remComRemAllComp.href.replace(uencRx, oldUenc);
                    }
                }.bind(this),
                onFailure: function () {
                    this.endLoadData();
                    alert('Error add to Wishlist');
                }.bind(this)
            }
        );
    },

    prepareCartItem: function (update_item_id) {

        if ($('shopping-cart-table') && $('shopping-cart-table').select('input.qty').length > 0) {
            var elements = $('shopping-cart-table').select('input.qty');
            for (var i = 0; i < elements.length; i++) {
                var item_id = elements[i].name;
                item_id = item_id.replace(/\D/g, '');

                if (update_item_id != undefined && item_id != update_item_id) {
                    continue;
                }

                var td = elements[i].up('td');
                elements[i].id = 'gpc_cart_item_' + item_id;

                var qty_cart = document.createElement('div').insert(elements[i]).innerHTML;

                var qty_cart = this.qty_cart_template.replace(/#gpc_item_id/g, item_id).replace(/#gpc_input_cart_qty/g, qty_cart);

                td.innerHTML = qty_cart + td.innerHTML;
            }
            this.prepareWishlist();
        }
    },

    getProductId: function () {
        var product = $$('input[name="product"]').detect(function (element) {
            if (!element.id) {
                return true;
            }
        });
        if (product) {
            return product.value;
        }

        if ($$('input[name="product"]').first()) {
            return $$('input[name="product"]').first().value;
        }
    },

    addtoCartProduct: function () {

        var product_id = this.getProductId();

        if ($('qty')) {
            var qty = $('qty').value;
            qty = parseInt(qty);
            if (!qty) {
                $('qty').value = 1;
                qty = 1;
            }

            if (qty < this.product_list[product_id].min_qty) {
                alert('The minimum quantity allowed for purchase is ' + this.product_list[product_id].min_qty + '.');
                return;
            }
            if (qty > this.product_list[product_id].max_qty) {
                alert('The maximum quantity allowed for purchase is ' + this.product_list[product_id].max_qty + '.');
                return;
            }
        } else if (this.product_list[product_id].is_grouped == '1' && $('super-product-table')) {
            var elements = $('super-product-table').getElementsByClassName('qty');
            if (elements.length > 0) {
                var all_zero = true;
                for (var i = 0; i < elements.length; i++) {
                    if (parseInt(elements[i].value) > 0) {
                        all_zero = false;
                        break;
                    }
                }
                if (all_zero) {
                    alert('Please specify the quantity of product(s).');
                    return;
                }
            }
        }

        if ($('customer-reviews') && (this.config.product_review_page == '1') &&
            (this.product_list[product_id].is_grouped == '0') && (this.product_list[product_id].is_simple == '0') && (this.product_list[product_id].is_giftcard == '0')) {

            this.showConfigurableParams(this.product_list[product_id].product_url, product_id);

        } else {
            if (this.config.add_effect == '2') {
                this.slide_control = $('image');

                if (this.slide_control == null) {
                    var slide_control = null;
                    
					if ($('main-image')) {
                        $('main-image').childElements().each(function (node) {
                            slide_control = node;
                        });
                    } else if ($('image-main')) {
						 slide_control = $('image-main');
					}
					
                    this.slide_control = slide_control;
                }

                var without_slide = (this.product_list[product_id].is_bundled) && (typeof(Enterprise) != "undefined");
                if (!without_slide) {
                    this.effectSlidetoCart(this.slide_control);
                }

                this.slide_control = undefined;
            } else if (this.config.add_effect == '1') {
                this.startLoadData();
            }

            $('product_addtocart_form').request({
                onSuccess: this.onSuccesAddtoCart.bind(this),
                onFailure: this.onFailureAddtoCart.bind(this)
            });
        }
    },

    prepareProductPage: function () {

        var product_id = this.getProductId();

        if ($('product_addtocart_form') && typeof(productAddToCartForm) != 'undefined') {

            productAddToCartForm = new VarienForm('product_addtocart_form');
            var gpc_add = document.createElement("input");
            gpc_add.type = "hidden";
            gpc_add.name = "gpc_add";
            gpc_add.value = 1;
            $('product_addtocart_form').appendChild(gpc_add);
            $('product_addtocart_form').onsubmit = function () {
                return false;
            };
            productAddToCartForm.submit = function () {
                if (productAddToCartForm.validator.validate()) {
                    GomageProcartConfig.addtoCartProduct();
                }
            }

            if (typeof(gomage_procart_product_list) != 'undefined') {
                this.product_list = gomage_procart_product_list;
            } else if (product_id) {
                this.addition_product_list_ids.push(product_id);
            }
        }

        if (this.config.ultimento_theme == '1') {
            $$('#qty[type="hidden"]').each(function (e) {
                e.remove();
            });
        }

        if ($('qty')) {
            this.decorateQtyWithButtons(product_id);
            if (!gomage_procart_configure_cart && product_id) {
                if (this.product_list[product_id].increments < this.product_list[product_id].min_qty) {
                    $('qty').value = this.product_list[product_id].min_qty;
                }
                else {
                    $('qty').value = this.product_list[product_id].increments;
                }
            }
        }
        if ($('super-product-table')) {
            this.decorateGroupedProductTableQtyWithButtons(this.qty_product_template);
        }
        this.fillConfigurableQtyWithMinValues();
    },

    addtoCart: function (control) {
		
        var control_id = control.id;
        control_id = control_id.replace(/\D/g, '');
        var onclick_str = this.add_to_cart_onclick_str[control_id];
        var product_id = ProcartGetUrlParam(onclick_str, 'gpc_prod_id');
        if (!product_id) return;

        var deals_id = '';

        if (gpc_SalesDealsData.hasOwnProperty(control.id)) {
            deals_id = gpc_SalesDealsData[control.id].deals;
        }

        var qty = 0;
        if ($('gpc_prod_id_' + (deals_id ? deals_id : product_id))) {
            qty = $('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value;
        } else {
            qty = this.product_list[product_id].min_qty;
        }

        qty = parseInt(qty);
        if (!qty) {
            if ($('gpc_prod_id_' + (deals_id ? deals_id : product_id))) {
                $('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value = 1;
            }
            qty = 1;
        }

        if (qty < this.product_list[product_id].min_qty) {
            alert('The minimum quantity allowed for purchase is ' + this.product_list[product_id].min_qty + '.');
            return;
        }
        if (qty > this.product_list[product_id].max_qty) {
            alert('The maximum quantity allowed for purchase is ' + this.product_list[product_id].max_qty + '.');
            return;
        }

        this.slide_control = control;

        if (onclick_str.search('checkout/cart/add') != -1) {
            if (this.product_list[product_id].is_simple == '1') {
                this.effectSlidetoCart(control);
                this.slide_control = undefined;
            }
            if (this.product_list[product_id].is_simple == '0') {
                this.startLoadData();
            }
            this.addtoCartSimple(onclick_str, product_id, deals_id);
        }
        else if (onclick_str.search('options=cart')) {
            this.showConfigurableParams(onclick_str, product_id, deals_id);
        }

    },

    showConfigurableParams: function (url, product_id, deals_id) {

        this.startLoadData();
        if ($('gpc_prod_id_' + (deals_id ? deals_id : product_id))) {
            var qty = $('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value;
        } else if ($('qty')) {
            var qty = $('qty').value;
        }
        else {
            var qty = 1;
        }

        qty = parseInt(qty);
        if (!qty) {
            if ($('gpc_prod_id_' + (deals_id ? deals_id : product_id))) {
                $('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value = 1;
            }
            qty = 1;
        }

        var params = {
            qty: qty,
            gpc_show_configurable: 1
        };

        var request = new Ajax.Request(url,
            {
                method: 'post',
                parameters: params,
                onSuccess: this.onSuccesShowConfigurable.bind(this),
                onFailure: this.onFailureShowConfigurable.bind(this)
            }
        );
    },

    onSuccesShowConfigurable: function (transport) {
        eval('var response = ' + transport.responseText);
        this.endLoadData();
        if (response.success) {
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
                    showEffectOptions: {afterFinish: function () {
                        if ((typeof(Enterprise) != 'undefined') && Enterprise.Bundle.oldReloadPrice) {
                            Product.Bundle.prototype.reloadPrice = Enterprise.Bundle.oldReloadPrice;
                        }
                        Event.stopObserving(document, "dom:loaded");
                        for (var i = 0; i < GomageProcartConfig.js_scripts.length; i++) {
                            if (typeof(GomageProcartConfig.js_scripts[i]) != 'undefined') {
                                globalEval(GomageProcartConfig.js_scripts[i]);
                            }
                        }
                        try {
                            Event.fire(document, "dom:loaded");
                        } catch (e) {
                        }
                        if (response.product_id) {
                            if (!$('product-price-' + response.product_id) && $('product-price-' + response.product_id + '_clone')) {
                                var product_price = $('product-price-' + response.product_id + '_clone').cloneNode(true);
                                product_price.id = 'product-price-' + response.product_id;
                                product_price.hide();
                                new Insertion.After($('product-price-' + response.product_id + '_clone'), product_price);
                            }
                        }
                        $('qty').value = GomageProcartConfig.configurable_qty;
                        if ($('overlay_modal_gcp')) {
                            $('overlay_modal_gcp').onclick = function () {
                                var win = GcpWindows.getWindow('gcp_configurable_add_to_cart');
                                win.close();
                            };
                        }
                        if (typeof(gomage_procart_product_list) != 'undefined') {
                            GomageProcartConfig.fillConfigurableQtyWithMinValues();
                        }
                        $('gcp_configurable_add_to_cart').down('form').writeAttribute('onsubmit', 'return false;');
                    }
                    },
                    onDestroy: function () {
                        GomageProcartConfig.reloadProductScripts();
                    }
                });
            win.getContent().innerHTML = response.form.stripScripts();
            win.showCenter(parseInt(this.config.background_view));
            this.decorateQtyWithButtons(
                $('gcp_configurable_add_to_cart_content').down('input[name="product"]').value,
                this.qty_category_popup_template
            );
        } else {
            if (response.redirect) {
                window.location.href = response.redirect;
            } else if (response.message) {
                alert(response.message);
            } else {
                alert('Error add to cart.');
            }
        }
    },

    onFailureShowConfigurable: function (transport) {
        this.endLoadData();
        alert('Failure add to cart.');
    },

    addtoCartConfigurable: function (form) {
        if (this.config.add_effect == '1') {
            this.startLoadData();
        }
        var elements = form.getElements('input, select, textarea');
        var params = {};
        for (var i = 0; i < elements.length; i++) {
            if ((elements[i].type == 'checkbox' || elements[i].type == 'radio') && !elements[i].checked) {
                continue;
            }
            if (elements[i].disabled) {
                continue;
            }
            var name = elements[i].name;
            if (name && name.substr(name.length - 2) == '[]') {
                if (typeof(params[elements[i].name]) == 'undefined') {
                    params[elements[i].name] = new Array();
                }
                params[elements[i].name].push(elements[i].value);
            } else {
                params[elements[i].name] = elements[i].value;
            }
        }
        if ($('shopping-cart-table')) {
            params['gpc_crosssell'] = 1;
        }
        var request = new Ajax.Request(form.action,
            {
                method: 'post',
                parameters: params,
                onSuccess: this.onSuccesAddtoCart.bind(this),
                onFailure: this.onFailureAddtoCart.bind(this)
            }
        );
    },

    addtoCartSimple: function (url, product_id, deals_id) {

        if (this.config.add_effect == '1') {
            this.startLoadData();
        }
        if ($('gpc_prod_id_' + (deals_id ? deals_id : product_id))) {
            var qty = $('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value;
        } else {
            var qty = this.product_list[product_id].min_qty;
        }
        qty = parseInt(qty);
        if (!qty) {
            if ($('gpc_prod_id_' + (deals_id ? deals_id : product_id))) {
                $('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value = 1;
            }
            qty = 1;
        }

        var params = {qty: qty,
            gpc_add: 1};
        if ($('shopping-cart-table')) {
            params['gpc_crosssell'] = 1;
        }

        var request = new Ajax.Request(url,
            {
                method: 'post',
                parameters: params,
                onSuccess: this.onSuccesAddtoCart.bind(this),
                onFailure: this.onFailureAddtoCart.bind(this)
            }
        );
    },

    onSuccesAddtoCart: function (transport) {
        eval('var response = ' + transport.responseText);
        this.endLoadData();
        if (response.success) {
            var win = GcpWindows.getWindow('gcp_configurable_add_to_cart');
            if (win) {
                win.close();
            }

            if (response.return_url) {
                window.location.href = response.return_url;
                return;
            }

            if (response.is_grouped) {
                this.showGroupedParams(response);
                return;
            }
            if (response.is_configurable) {
                this.showConfigurableParams(response.url, response.product_id, response.deals_id);
                return;
            }
            if (response.product_id) {
                this.product_list[response.product_id].max_qty = this.product_list[response.product_id].max_qty * 1 - response.qty * 1;
                if (this.product_list[response.product_id].max_qty * 1 < this.product_list[response.product_id].min_qty * 1) {
                    this.product_list[response.product_id].min_qty = this.product_list[response.product_id].max_qty;
                }
            }
            if (this.slide_control != undefined) {
                this.effectSlidetoCart(this.slide_control);
                this.slide_control = undefined;
            }
            this.replaceSidebar('block-cart', response.cart);
            //for enterprize DOM node
            this._replaceEnterpriseTopCart(response.cart);
            this.replaceTopLinks(response);
            if (response.base_cart && $('shopping-cart-table')) {
                this._updateCartCheckoutMethods('div.title-buttons', response.checkout_methods_top);
                this._updateCartCheckoutMethods('div.totals', response.checkout_methods_bottom);
                this.replaceRewardsPoints(response);
                this._removeCartPageNotifications();
                var tbody = $('shopping-cart-table').down('tbody');
                var tempElement = document.createElement('div');
                tempElement.innerHTML = '<table><tbody>' + response.base_cart + '</tbody></table>';
                el = tempElement.getElementsByTagName('tbody');
                if (el.length > 0) {
                    content = el[0];
                    tbody.parentNode.replaceChild(content, tbody);
                }
                decorateTable('shopping-cart-table');
                this.prepareCartItem(undefined);
            }
            this.updateCartBlocks(response);
            this.showConfirmationWindow(response, 'cart');
        } else {
            if (response.message) {
                alert(response.message);
				
				if (response.redirect) {
					window.location.href = response.redirect;
				}
            } else if (response.redirect) {
                window.location.href = response.redirect;
            } else {
                alert('Error add to cart.');
            }
        }
    },

    replaceSidebar: function (block_class, content_html) {	
		if (this.infortis_ultimo) {
			switch (block_class) {
				case 'block-cart':
					block_class = 'mini-cart';
				break;
				
				case 'block-compare':
					block_class = 'mini-compare';
				break;
			}
		}
			
        var blocks = $$('div.' + block_class);
       
	    for (var ii = 0; ii < blocks.length; ii++) {
            var block = blocks[ii];
            var content = content_html;
           
		    if (block && content) {
                var js_scripts = content.extractScripts();

                if (content && content.toElement) {
                    content = content.toElement();
                } else if (!Object.isElement(content)) {
                    content = Object.toHTML(content);
                    var tempElement = document.createElement('div');
                    content.evalScripts.bind(content).defer();
                    content = content.stripScripts();
                    tempElement.innerHTML = content;
                    el = getElementsByClassName(block_class, tempElement);
                    
					if (el.length > 0) {
                        content = el[0];
                    } else {
                        return;
                    }
                }
				
                block.parentNode.replaceChild(content, block);
               
			    for (var i = 0; i < js_scripts.length; i++) {
                    if (typeof(js_scripts[i]) != 'undefined') {
                        globalEval(js_scripts[i]);
                    }
                }
				
                if (typeof truncateOptions == 'function') {
                    truncateOptions();
                }
            }
        }

        if (block_class == 'block-cart') {
            var blocks = $$('div.' + block_class);
            
			for (var ii = 0; ii < blocks.length; ii++) {
                var win_content = blocks[ii].up('div.gomage_aap_content');
                
				if (win_content) {
                    var win_id = win_content.id;
                    win_id = win_id.replace(/\D/g, '');
                    
					if (typeof(GapWindows) == 'object') {
                        var win = GapWindows.getWindow('gomage-ads-window-' + win_id);
                        
						if (win) {
                            win.height = 0;
                            win.computeBounds();
                        }
                    }
                }
            }
        }
    },

    replaceTopLinks: function (response) {
        this._replaceUltCustomerNav(response);
        this._replaceTopLink('top-link-cart', response);
        this._replaceMiniCart(response);
        return this._replaceTopLink('top-link-wishlist', response);
    },

    _replaceMiniCart: function (response) {
        if (typeof(Minicart) != 'undefined' && response.minicart_content) {
            var Mini = new Minicart({});
            Mini.updateCartQty(response.total_qty);
            Mini.updateContentOnUpdate({content: response.minicart_content});
            if ($$('div.header-minicart a.no-count')[0]){
                $$('div.header-minicart a.no-count')[0].removeClassName('no-count');
            }
            if (typeof truncateOptions == 'function') {
                truncateOptions();
            }
        }
    },

    _replaceUltCustomerNav: function (response) {
        if (this.config.ultimento_theme == '1') {
            if ($('customer-nav') && response.ultcustomernav) {
                var js_scripts = response.ultcustomernav.extractScripts();
                $('customer-nav').replace(response.ultcustomernav.stripScripts());
                Event.stopObserving(document, "dom:loaded");
                for (var i = 0; i < js_scripts.length; i++) {
                    if (typeof(js_scripts[i]) != 'undefined') {
                        globalEval(js_scripts[i]);
                    }
                }
                try {
                    Event.fire(document, "dom:loaded");
                } catch (e) {
                }
            }
        }
    },

    _replaceTopLink: function (link_class, response) {

        var link = $$('ul.links a.' + link_class + ', div.links ul a.' + link_class)[0];
        if (link && response.top_links) {

            var content = response.top_links;
            if (content && content.toElement) {
                content = content.toElement();
            } else if (!Object.isElement(content)) {
                content = Object.toHTML(content);
                var tempElement = document.createElement('div');
                tempElement.innerHTML = content;
                el = getElementsByClassName(link_class, tempElement);
                if (el.length > 0) {
                    content = el[0];
                }
                else {
                    return false;
                }
            }
            link.parentNode.replaceChild(content, link);
            return true;
        } else {
            return false;
        }
    },

    onFailureAddtoCart: function (transport) {
        this.endLoadData();
        alert('Failure add to cart.');
    },

    qtyUp: function (product_id, verification_qty, deals_id) {

        var qty = $('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value * 1 + this.product_list[product_id].increments;

        if (qty > this.product_list[product_id].max_qty) {
            alert('The maximum quantity allowed for purchase is ' + this.product_list[product_id].max_qty + '.');
            return;
        }

        if (!verification_qty) {
            $('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value = $('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value * 1 + this.product_list[product_id].increments;
            return;
        }
        this.startLoadData();
        var params = {product_id: product_id,
            qty: qty,
            deals_id: deals_id};

        var request = new Ajax.Request(this.config.changeqty_url,
            {
                method: 'post',
                parameters: params,
                onSuccess: this.onSuccesChangeQty.bind(this),
                onFailure: this.onFailureChangeQty.bind(this)
            }
        );
    },

    qtyDown: function (product_id, verification_qty, deals_id) {

        var qty = $('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value * 1 - this.product_list[product_id].increments;

        if (qty < this.product_list[product_id].min_qty) {
            alert('The minimum quantity allowed for purchase is ' + this.product_list[product_id].min_qty + '.');
            return;
        }
        if (!verification_qty) {
            $('gpc_prod_id_' + (deals_id ? deals_id : product_id)).value = qty;
            return;
        }
        this.startLoadData();
        var params = {product_id: product_id,
            qty: qty,
            deals_id: deals_id};

        var request = new Ajax.Request(this.config.changeqty_url,
            {
                method: 'post',
                parameters: params,
                onSuccess: this.onSuccesChangeQty.bind(this),
                onFailure: this.onFailureChangeQty.bind(this)
            }
        );
    },

    onSuccesChangeQty: function (transport) {
        eval('var response = ' + transport.responseText);
        this.endLoadData();
        if (response.error) {
            alert(response.message);
        }
        else {
            if ($('gpc_prod_id_' + (response.deals_id ? response.deals_id : response.product_id))) {
                $('gpc_prod_id_' + (response.deals_id ? response.deals_id : response.product_id)).value = response.qty;
            }
        }
    },

    onFailureChangeQty: function (transport) {
        this.endLoadData();
        alert('Failure change qty.');
    },

    setOverlaySize: function () {
        var pageSize = GcpWindowUtilities.getPageSize(this.overlay.parentNode);
        this.overlay.setStyle({
            'height': pageSize.pageHeight + 'px'
        });
    },

    startLoadData: function () {
        this.setOverlaySize();
        this.overlay.show();
        this.loadinfo.show();
    },

    endLoadData: function () {
        if (this.overlay)
            this.overlay.hide();
        if (this.loadinfo)
            this.loadinfo.hide();
    },

    effectSlidetoCart: function (control) {
        if (!control) {
            return;
        }

        if (this.config.add_effect != '2') return;
        if (this.slide_control == undefined) return;

        if (control.id == 'image' || control.id == 'image-main') {
            var img = control;
        } else {
            if ($(control).up('li.item') && $(control).up('li.item').down('img')) {
                var img = $(control).up('li.item').down('img');
            }
            else if ($(control).up('a.product-image')) {
                var img = control;
            }
            else if ($(control).up('td') && $(control).up('td').down('img')) {
                var img = $(control).up('td').down('img');
            } else if ($(control).up('li') && $(control).up('li').down('img')) {
                var img = $(control).up('li').down('img');
            }
        }

        var cart = null;
        var carts = $$(this.config.slide_destination);

        if (carts.length) {
            for (var i = 0; i < carts.length; i++) {
                offset = carts[i].cumulativeOffset();
                if (offset[0] || offset[1]) {
                    cart = carts[i];
                    break;
                }
            }
        }

        if (img && cart) {
            var img_offsets = img.cumulativeOffset();
            var cart_offsets = cart.cumulativeOffset();
            var animate_img = img.cloneNode(true);
            animate_img.id = 'glg_animate_img';
            document.body.appendChild(animate_img);
            animate_img.setStyle({'position': 'absolute',
                'top': img_offsets[1] + 'px',
                'left': img_offsets[0] + 'px'});

            new Effect.Parallel(
                [
                    new Effect.Fade('glg_animate_img', {sync: true, to: 0.3}),
                    new Effect.MoveBy('glg_animate_img', cart_offsets[1] - img_offsets[1], cart_offsets[0] - img_offsets[0], {sync: true})
                ],
                {duration: 2,
                    afterFinish: function () {
                        $('glg_animate_img').remove();
                    }
                }
            );
        }
    },

    qtyUpSidebar: function (item_id) {

        this.startLoadData();
        var params = {item_id: item_id,
            qty: $('gpc_sidebar_' + item_id).value * 1,
            direction: 'up',
            sidebar: 1};
        if (typeof($('shopping-cart-table')) != 'undefined') {
            params['cart'] = 1;
        }

        var request = new Ajax.Request(this.config.changeqtycartitem_url,
            {
                method: 'post',
                parameters: params,
                onSuccess: this.onSuccesChangeQtySidebar.bind(this),
                onFailure: this.onFailureChangeQtySidebar.bind(this)
            }
        );
    },

    qtyDownSidebar: function (item_id) {
        if ($('gpc_sidebar_' + item_id).value * 1 == 1) {
            alert('The minimum quantity allowed for purchase is 1.');
            return;
        }
        this.startLoadData();
        var params = {item_id: item_id,
            qty: $('gpc_sidebar_' + item_id).value * 1,
            direction: 'down',
            sidebar: 1};
        if (typeof($('shopping-cart-table')) != 'undefined') {
            params['cart'] = 1;
        }

        var request = new Ajax.Request(this.config.changeqtycartitem_url,
            {
                method: 'post',
                parameters: params,
                onSuccess: this.onSuccesChangeQtySidebar.bind(this),
                onFailure: this.onFailureChangeQtySidebar.bind(this)
            }
        );
    },

    onSuccesChangeQtySidebar: function (transport) {
        eval('var response = ' + transport.responseText);
        this.endLoadData();
        if (response.error) {
            alert(response.message);
        }
        else {
            if (response.product_id && this.product_list[response.product_id] && response.max_qty) {
                this.product_list[response.product_id].max_qty = response.max_qty;
            }
            this.replaceSidebar('block-cart', response.cart);
            this._replaceEnterpriseTopCart(response.cart, true);
            this.replaceTopLinks(response);
            this.updateCartBlocks(response);
            if (typeof(response.items_html) != 'undefined') {
                this.replaceCartItems(response);
            }
            if (this.isLightCheckoutPage()) {
                checkout.submit(checkout.getFormData(), 'get_totals');
            }
        }
    },

    isLightCheckoutPage: function () {
        return $('gcheckout-onepage-review') && (typeof(checkout) != 'undefined');
    },

    onFailureChangeQtySidebar: function (transport) {
        this.endLoadData();
        alert('Failure change qty.');
    },

    deleteItem: function (url, layout) {

        this.startLoadData();
        var params = {};
        if ($('shopping-cart-table') || this.isLightCheckoutPage()) {
            params.gpc_cart_delete = 1;
            params.gpc_sedebar_delete = 1;
        } else {
            params.gpc_sedebar_delete = 1;
        }

        this.removeLayout = layout;
        if (this.removeLayout == 'cart_sidebar' && typeof(Enterprise) != 'undefined') {
            window.clearTimeout(Enterprise.TopCart.interval);
        }

        var request = new Ajax.Request(url,
            {
                method: 'post',
                parameters: params,
                onSuccess: this.onSuccesDeleteItem.bind(this),
                onFailure: this.onFailureDeleteItem.bind(this)
            }
        );
    },

    onSuccesDeleteItem: function (transport) {

        eval('var response = ' + transport.responseText);
        this.endLoadData();

        if (response.error) {
            if (response.message) {
                alert(response.message);
            } else {
                this._removeCartPageNotifications();
            }
        }
        else {
            if (response.redirect) {
                if ($('shopping-cart-table') && parseInt(this.config.disable_cart)) {
                    setLocation('/');
                    return;
                }
                setLocation(response.redirect);
                return;
            }
            if (response.item_id) {
                if ($('shopping-cart-table')) {
                    if ($('gpc_cart_item_' + response.item_id)) {
                        $('gpc_cart_item_' + response.item_id).up('td').up('tr').remove();
                    }
                    $$('#shopping-cart-table tbody tr').each(function (tr) {
                        tr.setAttribute('class', '');
                    });
                    decorateTable('shopping-cart-table');
                }
                this.updateCartBlocks(response);
            }

            this.replaceSidebar('block-cart', response.cart);
            this._replaceEnterpriseTopCart(response.cart, this.removeLayout == 'cart_sidebar');
            this.replaceTopLinks(response);
            if (response.checkout_methods_top && $('shopping-cart-table')) {
                this._updateCartCheckoutMethods('div.title-buttons', response.checkout_methods_top);
            }
            if (response.checkout_methods_bottom && $('shopping-cart-table')) {
                this._updateCartCheckoutMethods('div.totals', response.checkout_methods_bottom);
            }
            if (response.product_id && this.product_list[response.product_id] && response.max_qty) {
                this.product_list[response.product_id].max_qty = response.max_qty;
            }
            this.replaceRewardsPoints(response);
            if (this.isLightCheckoutPage()) {
                checkout.submit(checkout.getFormData(), 'get_totals');
            }
        }
    },

    onFailureDeleteItem: function (transport) {
        this.endLoadData();
        alert('Cannot remove the item.');
    },

    qtyCartUp: function (item_id) {

        this.startLoadData();
        var params = {item_id: item_id,
            qty: $('gpc_cart_item_' + item_id).value * 1,
            direction: 'up',
            cart: 1,
            sidebar: 1};

        var request = new Ajax.Request(this.config.changeqtycartitem_url,
            {
                method: 'post',
                parameters: params,
                onSuccess: this.onSuccesChangeQtyCart.bind(this),
                onFailure: this.onFailureChangeQtyCart.bind(this)
            }
        );
    },

    qtyCartDown: function (item_id) {
        if ($('gpc_cart_item_' + item_id).value * 1 == 1) {
            alert('The minimum quantity allowed for purchase is 1.');
            return;
        }
        this.startLoadData();
        var params = {item_id: item_id,
            qty: $('gpc_cart_item_' + item_id).value * 1,
            direction: 'down',
            cart: 1,
            sidebar: 1
        };

        var request = new Ajax.Request(this.config.changeqtycartitem_url,
            {
                method: 'post',
                parameters: params,
                onSuccess: this.onSuccesChangeQtyCart.bind(this),
                onFailure: this.onFailureChangeQtyCart.bind(this)
            }
        );
    },

    onSuccesChangeQtyCart: function (transport) {
        eval('var response = ' + transport.responseText);
        this.endLoadData();
        if (response.error) {
            if (response.message) {
                alert(response.message);
                delete(response.message);
            }
            if (response.update_attribute) {
                var elements = new Array();
                if ($('gpc_cart_item_' + response.item_id)) {
                    elements = elements.concat($('gpc_cart_item_' + response.item_id).up('td').up('tr').select('select'));
                }
                if ($('gpc_sidebar_' + response.item_id)) {
                    elements = elements.concat($('gpc_sidebar_' + response.item_id).up('div.product-details').select('select'));
                }
                if (elements.length) {
                    for (var i = 0; i < elements.length; i++) {
                        var attribute_id = elements[i].getAttribute('class');
                        attribute_id = attribute_id.replace(/\D/g, '');
                        if (response.success_param.hasOwnProperty(attribute_id)) {
                            continue;
                        } else if (attribute_id == response.update_attribute) {
                            elements[i].options.length = 0;
                            elements[i].options[elements[i].options.length] = new Option(response.choosetext, '', false, false);

                            for (key in response.attribute_data) {
                                elements[i].options[elements[i].options.length] = new Option(response.attribute_data[key].label, response.attribute_data[key].id, false, false);
                            }

                        } else {
                            elements[i].options.length = 0;
                            elements[i].options[elements[i].options.length] = new Option(response.choosetext, '', false, false);
                        }
                    }
                }
            } else {
                this.replaceSidebar('block-cart', response.cart);
                this.replaceCartItems(response);
            }
        } else {
            this.replaceSidebar('block-cart', response.cart);
            this._replaceEnterpriseTopCart(response.cart, this.leaveCartBlock);
            this.replaceCartItems(response);
            this.updateCartBlocks(response);
            this.replaceTopLinks(response);
            this.leaveCartBlock = false;
            if (this.isLightCheckoutPage()) {
                checkout.submit(checkout.getFormData(), 'get_totals');
            }
        }
        this._updateCartCheckoutMethods('div.title-buttons', response.checkout_methods_top);
        this._updateCartCheckoutMethods('div.totals', response.checkout_methods_bottom);
        this.replaceRewardsPoints(response);
        if (typeof(response.message) != 'undefined') {
            alert(response.message);
        } else {
            $$('ul.messages').first().remove();
            $$('p.item-msg').invoke('remove');
        }
    },

    onFailureChangeQtyCart: function (transport) {
        this.endLoadData();
        alert('Failure change qty.');
    },

    replaceCartItems: function (response) {
        if ($('shopping-cart-table') && response.items_html) {

            var tbody = $('shopping-cart-table').down('tbody');
            var tempElement = document.createElement('div');
            tempElement.innerHTML = '<table><tbody>' + response.items_html + '</tbody></table>';
            el = tempElement.getElementsByTagName('tbody');
            if (el.length > 0) {
                content = el[0];
                tbody.parentNode.replaceChild(content, tbody);
            }
            decorateTable('shopping-cart-table');
            this.prepareCartItem(undefined);
        }
    },

    updateCartBlocks: function (response) {
        this._updateCartBlock($('shopping-cart-totals-table'), response.total);
        this._updateCartBlock($$('div.shipping')[0], response.shipping);
        if ($$('div.crosssell')[0] && response.crosssell) {
            this._updateCrosssellBlock(response.crosssell);
            this.prepareWishlist();
            this.prepareCompare();
            this.prepareCrosssell();
            this.addAdditionProduct();
        }
    },

    _updateCartBlock: function (block, content) {
        if (block && content) {
            var js_scripts = content.extractScripts();
            content = content.stripScripts();

            if (content && content.toElement) {
                content = content.toElement();
            } else if (!Object.isElement(content)) {
                content = Object.toHTML(content);
                var tempElement = document.createElement('div');
                tempElement.innerHTML = content;
                el = tempElement.getElementsByTagName('table');
                if (el.length > 0) {
                    content = el[0];
                } else {
                    content = tempElement.firstChild;
                }
            }
            block.parentNode.replaceChild(content, block);

            for (var i = 0; i < js_scripts.length; i++) {
                if (typeof(js_scripts[i]) != 'undefined') {
                    globalEval(js_scripts[i]);
                }
            }

        }
    },

    attributeCartChange: function (control, product_id) {

        var super_attribute = {};
        var attributes = new Array();
        var item_id = 0;

        if ($(control).up('td')) {
            item_id = $(control).up('td').up('tr').down('input.qty').name;
            item_id = item_id.replace(/\D/g, '');
            if ($(control).up('td').up('tr').select('select').length > 0) {
                attributes = $(control).up('td').up('tr').select('select');
            }
        } else if ($(control).up('div.product-details')) {
            item_id = $(control).up('div.product-details').down('input.qty').id;
            item_id = item_id.replace(/\D/g, '');
            if ($(control).up('div.product-details').select('select').length > 0) {
                attributes = $(control).up('div.product-details').select('select');
            }
        } else {
            return;
        }

        for (var i = 0; i < attributes.length; i++) {
            var attribute_id = $(attributes[i]).className;
            attribute_id = attribute_id.replace(/\D/g, '');
            super_attribute[Object.keys(super_attribute).length] = {attribute_id: attribute_id, value: $(attributes[i]).value};
        }

        this.startLoadData();
        this.leaveCartBlock = typeof($(control).up('div.top-cart')) != 'undefined';
        if (this.leaveCartBlock && typeof(Enterprise) != 'undefined') {
            window.clearTimeout(Enterprise.TopCart.interval);
        }

        var params = {'id': item_id,
            'product': product_id,
            'super_attribute': Object.toJSON(super_attribute)};

        var request = new Ajax.Request(this.config.changeattributecart_url,
            {
                method: 'post',
                parameters: params,
                onSuccess: this.onSuccesChangeQtyCart.bind(this),
                onFailure: this.onFailureChangeQtyCart.bind(this)
            }
        );
    },

    showConfirmationWindow: function (response, type) {
        this.setOverlaySize();
        if (this.config.show_window == '1') {
            this.confirmation_window_type = type;
            $$('span.gpc_cw_add_to').each(Element.hide);
            $('gpc_cw_add_to_' + type).show();
            if (parseInt(response.qty) >= 1) {
                $('gpc_conf_win_qty').innerHTML = response.qty;
            }
            else {
                $('gpc_conf_win_qty').innerHTML = '';
            }
            $('gpc_conf_win_prod_name').innerHTML = response.prod_name;
            $('gpc_conf_win_was').hide();
            $('gpc_conf_win_were').hide();
            if (parseInt(response.qty) > 1)
                $('gpc_conf_win_were').show();
            else
                $('gpc_conf_win_was').show();

            for (key in gomage_procart_goto_data) {
                if (key == this.confirmation_window_type) {
                    $('gpc_confirm_window_checkout').up('button').setAttribute("onclick", gomage_procart_goto_data[key].onclick);
                    if (Prototype.Browser.IE) {
                        $('gpc_confirm_window_checkout').up('button').onclick = function () {
                            for (_key in gomage_procart_goto_data) {
                                if (_key == GomageProcartConfig.confirmation_window_type) {
                                    globalEval(gomage_procart_goto_data[_key].onclick);
                                }
                            }
                        };
                    }
                    $('gpc_confirm_window_checkout').innerHTML = gomage_procart_goto_data[key].text;
                }
            }
            this.setOverlaySize();
            if (response.message) {
                $('gpc_conf_win_prod_name').update(response.message);
                $('gpc_conf_win_was').hide();
                $('gpc_cw_add_to_wishlist').hide();
            }
            this.overlay.show();
            $('gpc_confirmation_window').show();

            var auto_hide_window = this.config.auto_hide_window;
            this.auto_hide_window = parseInt(auto_hide_window);
            if (this.auto_hide_window > 0) {
                this.setTimeout();
            }
        }
    },

    setTimeout: function () {
        var text = '';
        var onclick = '';
        for (key in gomage_procart_goto_data) {
            if (key == this.confirmation_window_type) {
                text = gomage_procart_goto_data[key].text;
                onclick = gomage_procart_goto_data[key].onclick;
            }
        }
        if (this.auto_hide_window > 0 && $('gpc_confirmation_window').visible()) {
            if (this.config.redirect_to == '1')
                $('gpc_confirm_window_checkout').innerHTML = text + ' (' + this.auto_hide_window + ')';
            else
                $('gpc_confirm_window_continue').innerHTML = gomage_procart_continue_text + ' (' + this.auto_hide_window + ')';
            window.setTimeout(function () {
                GomageProcartConfig.setTimeout();
            }, 1000);
            this.auto_hide_window = this.auto_hide_window - 1;
        } else {
            if (this.config.redirect_to == '1' && $('gpc_confirmation_window').visible()) {
                globalEval(onclick);
            }
            GomageProcartConfig.overlay.hide();
            $('gpc_confirmation_window').hide();
        }
    },

    qtyProductUp: function (prod_id) {

        if (productAddToCartForm.validator.validate()) {
            var product_id = this.getProductId();
            if ($('qty')) {
                if (typeof(spConfig) != 'undefined') { //if we are on configurable product page we need to check child product qty and not super
                    if (this.configurable_current_simple != null) {
                        product_id = this.configurable_current_simple;
                    }
                }

                if (this.product_list[product_id].parent_id && !this.product_list[this.product_list[product_id].parent_id].is_grouped) {
                    var qty = $('qty').value * 1 + this.product_list[this.product_list[product_id].parent_id].increments;
                }
                else {
                    var qty = $('qty').value * 1 + this.product_list[product_id].increments;
                }

                if (this.product_list[product_id]) {
                    if (qty > this.product_list[product_id].max_qty) {
                        alert('The maximum quantity allowed for purchase is ' + this.product_list[product_id].max_qty + '.');
                        return;
                    }
                }
            }

            if ($('gpc_prod_grouped_' + prod_id)) {

                var qty = $('gpc_prod_grouped_' + prod_id).value * 1 + this.product_list[prod_id].increments;
                if (qty > this.product_list[prod_id].max_qty) {
                    alert('The maximum quantity allowed for purchase is ' + this.product_list[prod_id].max_qty + '.');
                    return;
                }
                product_id = prod_id;
            }
            var bundle_elements = $$('*[name^="bundle_option"]');
            if (bundle_elements.size() > 0) {
                if (!this.isBundleChildrenAvailable(bundle_elements)) {
                    return;
                }
            }
            this.startLoadData();

            var parent_id = this.product_list[prod_id].parent_id;
            var params = {product_id: prod_id,
                qty: qty,
                parent_id: parent_id};
            var request = new Ajax.Request(this.config.changeproductqty_url,
                {
                    method: 'post',
                    parameters: params,
                    onSuccess: this.onSuccesChangeProductQty.bind(this),
                    onFailure: this.onFailureChangeProductQty.bind(this)
                }
            );
        }
    },

    qtyProductDown: function (prod_id) {

        if (productAddToCartForm.validator.validate()) {

            var product_id = this.getProductId();

            if ($('qty')) {
                if (typeof(spConfig) != 'undefined') { //if we are on configurable product page we need to check child product qty and not super
                    if (this.configurable_current_simple != null) {
                        product_id = this.configurable_current_simple;
                    }
                }

                if (this.product_list[product_id].parent_id && !this.product_list[this.product_list[product_id].parent_id].is_grouped) {
                    var qty = $('qty').value * 1 - this.product_list[this.product_list[product_id].parent_id].increments;
                }
                else {
                    var qty = $('qty').value * 1 - this.product_list[product_id].increments;
                }

                if (qty < this.product_list[product_id].min_qty) {
                    alert('The minimum quantity allowed for purchase is ' + this.product_list[product_id].min_qty + '.');
                    return;
                }
            }

            if ($('gpc_prod_grouped_' + prod_id)) {

                var qty = $('gpc_prod_grouped_' + prod_id).value * 1 - this.product_list[prod_id].increments;
                if (qty < this.product_list[prod_id].min_qty && !this.product_list[prod_id].parent_id) {
                    alert('The minimum quantity allowed for purchase is ' + this.product_list[prod_id].min_qty + '.');
                    return;
                }
                product_id = prod_id;
            }

            this.startLoadData();

            var parent_id = this.product_list[prod_id].parent_id;
            var params = {product_id: prod_id,
                qty: qty,
                parent_id: parent_id};

            var request = new Ajax.Request(this.config.changeproductqty_url,
                {
                    method: 'post',
                    parameters: params,
                    onSuccess: this.onSuccesChangeProductQty.bind(this),
                    onFailure: this.onFailureChangeProductQty.bind(this)
                }
            );
        }
    },

    onSuccesChangeProductQty: function (transport) {

        eval('var response = ' + transport.responseText);
        this.endLoadData();
        if (response.error) {
            alert(response.message);
        }
        else {
            if ($('gpc_prod_grouped_' + response.product_id)) {
                $('gpc_prod_grouped_' + response.product_id).value = response.qty;

            } else if ($('qty')) {
                $('qty').value = response.qty;
            }
        }
    },

    onFailureChangeProductQty: function (transport) {
        this.endLoadData();
        alert('Failure change qty.');
    },

    showGroupedParams: function (response) {

        this.js_scripts = response.form.extractScripts();
        this.grouped_qty = response.qty;
        var win = new GcpWindow('gcp_configurable_add_to_cart',
            {className: "gomage_cp",
                additionClass: "gpc-confw-buttons-" + GomageProcartConfig.config.cart_button_color,
                title: 'Add to Cart',
                width: GomageProcartConfig.config.window_width,
                top: '50%',
                destroyOnClose: true,
                closeOnEsc: false,
                showEffectOptions: {afterFinish: function () {
                    for (var i = 0; i < GomageProcartConfig.js_scripts.length; i++) {
                        if (typeof(GomageProcartConfig.js_scripts[i]) != 'undefined') {
                            globalEval(GomageProcartConfig.js_scripts[i]);
                        }
                    }
                    $('super-product-table').select('input[type=text]').each(function (control) {
                        $(control).value = $(control).value > GomageProcartConfig.grouped_qty
                            ? control.value : GomageProcartConfig.grouped_qty;
                    });
                    if ($('overlay_modal_gcp')) {
                        $('overlay_modal_gcp').onclick = function () {
                            var _win = GcpWindows.getWindow('gcp_configurable_add_to_cart');
                            if (_win) _win.close();
                        };
                    }
                    $('gcp_configurable_add_to_cart').down('form').writeAttribute('onsubmit', 'return false;');
                }
                },
                onDestroy: function () {
                    GomageProcartConfig.reloadProductScripts();
                }
            });
        win.getContent().innerHTML = response.form.stripScripts();
        win.showCenter(parseInt(this.config.background_view));
        if ($('super-product-table')) {
            this.decorateGroupedProductTableQtyWithButtons(this.qty_category_popup_template);
        }
    },

    addtoCartGrouped: function (form) {

        this.startLoadData();

        var elements = form.getElements('input, select, textarea');
        var params = {};
        for (var i = 0; i < elements.length; i++) {
            if ((elements[i].type == 'checkbox' || elements[i].type == 'radio') && !elements[i].checked) {
                continue;
            }
            if (elements[i].disabled) {
                continue;
            }
            params[elements[i].name] = elements[i].value;
        }
        if ($('shopping-cart-table')) {
            params['gpc_crosssell'] = 1;
        }
        var request = new Ajax.Request(form.action,
            {
                method: 'post',
                parameters: params,
                onSuccess: this.onSuccesAddtoCart.bind(this),
                onFailure: this.onFailureAddtoCart.bind(this)
            }
        );
    },

    decorateGroupedProductTableQtyWithButtons: function (template) {
        var elements = $('super-product-table').select('input.qty');
        for (var i = 0; i < elements.length; i++) {
            var product_id = elements[i].name.replace(/\D/g, '');
            elements[i].id = 'gpc_prod_grouped_' + product_id;
            this.decorateQtyWithButtons(product_id, template, elements[i].id);
            if (typeof(this.product_list[product_id]) == 'undefined') {
                this.addition_product_list_ids.push(product_id);
            }
        }
        this.fillGroupedQtyWithMinValues();
    },

    decorateQtyWithButtons: function (product_id, template, qty_node, class_name) {
        qty_node = typeof qty_node !== 'undefined' ? qty_node : 'qty';
        class_name = typeof class_name !== 'undefined' ? class_name : 'gpc_qty_edit';
        template = typeof template !== 'undefined' ? template : this.qty_product_template;
        if (!$(qty_node).up('.' + class_name)) {
            var qty_div = $(document.createElement('div'));
            qty_div.addClassName(class_name);
            new Insertion.After($(qty_node), qty_div);
            qty_div.appendChild($(qty_node));
            var qty_html = qty_div.innerHTML;
            qty_div.innerHTML = template.replace(/#product_id/g, product_id).replace(/#gpc_input_product_qty/g, qty_html);
        }
    },

    fillGroupedQtyWithMinValues: function () {
        $('super-product-table').adjacent('input[id^="gpc_prod_grouped_"]').each(function (item) {
            var childProductId = item.id.sub('gpc_prod_grouped_', '');
            if (typeof(this.product_list[childProductId]) != 'undefined') {
                var is_configure_grouped = (gomage_procart_configure_cart == '1');
                if (is_configure_grouped) {
                    var product_id = $$('.product-view input[name="product"]').first().value;
                    if (product_id && this.product_list[product_id]) {
                        is_configure_grouped = this.product_list[product_id].is_grouped == '1';
                    } else {
                        is_configure_grouped = false;
                    }
                }
                if (!is_configure_grouped) {
                    item.value = this.product_list[childProductId].increments;
                }
                config = this;
                item.observe('change', function () {
                    config.handleQtyMinMaxChange(
                        config.product_list[childProductId].min_qty,
                        config.product_list[childProductId].max_qty,
                        this
                    );
                });
            }
        }.bind(this));
    },

    fillConfigurableQtyWithMinValues: function () {
        if (typeof(spConfig) != 'undefined'
            && ($$('body').first().hasClassName('catalog-product-view')
            || $('gcp_configurable_add_to_cart'))) { //if we are on configurable product page
            $('qty').observe('change', function () {
                GomageProcartConfig.handleQtyMinMaxChange(
                    GomageProcartConfig.configurable_min_qty,
                    GomageProcartConfig.configurable_max_qty,
                    this);
            });
            var choosed_options = new Hash();
            spConfig.settings.each(function (setting) {
                setting.observe('change', function () {
                    var choosed_id = this.value;
                    var option_id = this.id.sub('attribute', '');
                    var res = spConfig.config.attributes[option_id].options.find(function (obj) {
                        return obj.id == choosed_id;
                    });
                    if (typeof(res) != 'undefined') {
                        choosed_options.set(option_id, res.products);
                    } else {
                        //Choose an Option...
                        choosed_options.unset(option_id);
                    }
                    if (spConfig.settings.size() == choosed_options.size()) {
                        var result = null;
                        choosed_options.values().each(function (items_array) {
                            if (result != null) {
                                result = result.intersect(items_array);
                            } else {
                                result = items_array;
                            }
                        });
                        if (typeof(result[0]) != 'undefined') {
                            GomageProcartConfig.configurable_current_simple = result[0];
                            GomageProcartConfig.configurable_min_qty = GomageProcartConfig.product_list[result[0]].min_qty;
                            GomageProcartConfig.configurable_max_qty = GomageProcartConfig.product_list[result[0]].max_qty;
                            GomageProcartConfig.handleQtyMinMaxChange(
                                GomageProcartConfig.configurable_min_qty,
                                GomageProcartConfig.configurable_max_qty,
                                $('qty')
                            )
                        }
                    } else {
                        GomageProcartConfig.configurable_min_qty = undefined;
                        GomageProcartConfig.configurable_max_qty = undefined;
                    }
                });
            });
        }
    },

    handleQtyMinMaxChange: function (min, max, element) {
        min = parseInt(min);
        max = parseInt(max);
        var value = parseInt(element.value);

        if (isNaN(min) || isNaN(max) || isNaN(value)) {
            return false;
        }

        if (element.value < min) {
            element.value = min;
        } else if (element.value > max) {
            element.value = max;
        }
    },

    isBundleChildrenAvailable: function (inputsArray) {
        var result = true;
        inputsArray.each(function (elem) {
            var selected_elem = null;
            var bundle_qty = parseInt($('qty').value);
            if (elem.match('select')) {
                elem.childElements().each(function (child) {
                    if (child.match(':selected')) {
                        selected_elem = elem;
                        return;
                    }
                });
            } else if (elem.match('input:checked')) {
                selected_elem = elem;
            }
            if (selected_elem) {
                var data = GomageProcartConfig.bundle_selection_hash[selected_elem.value];
                if (data) {
                    if (data.selection_can_change_qty) {
                        if (selected_elem.match('option')) {
                            selected_elem = selected_elem.up('select');
                        }
                        var arr = selected_elem.name.split('[');
                        arr[0] = arr[0] + '_qty';
                        var res = arr.join('[');
                        var qty_field = $$('input[name=' + res + ']')[0];
                        if (typeof(qty_field) != 'undefined') {
                            var sel_qty = parseInt($$('input[name=' + res + ']')[0].value);
                        } else {
                            sel_qty = 1;
                        }
                        if (sel_qty * (bundle_qty + 1) < data.min_qty) {
                            alert(data.name + ' ' + GomageProcartConfig.config.gpc_availability_min + '(' + data.min_qty + ')');
                            result = false;
                        } else if (sel_qty * (bundle_qty + 1) > data.max_qty) {
                            alert(data.name + ' ' + GomageProcartConfig.config.gpc_availability_max);
                            result = false;
                        }
                    }
                }
            }
        });
        return result;
    },

    _updateCartCheckoutMethods: function (elem_container, content) {
        if ($$(elem_container).first()) {
            var elem_methods = $$(elem_container).first().down('ul.checkout-types');
            if (typeof(elem_methods) != 'undefined') {
                this._updateCartBlock(elem_methods, content);
            } else {
                $$(elem_container).first().insert(content);
            }
        }
    },

    _updateCrosssellBlock: function (content) {
        if (typeof($$('div.crosssell')[0]) != 'undefined') {
            this._updateCartBlock($$('div.crosssell')[0], content);
        }
    },

    _removeCartPageNotifications: function () {
        var messages = $$('ul.messages,p.item-msg');
        if (messages.size() > 0) {
            messages.invoke('remove');
        }
    },

    _replaceEnterpriseTopCart: function (content, show) {
        if (typeof(Enterprise) != 'undefined') {
            window.clearTimeout(Enterprise.TopCart.interval);
        }
        var topCartNode = $$('div.top-cart').first();
        if (topCartNode) {
            topCartNode.replace(content);
        }

        truncateOptions();

        if (show) {
            if ($('topCartContent')) {
                $('topCartContent').style.display = 'block';
                $('topCartContent').up('div.top-cart').style.zIndex = '992';
                $('topCartContent').siblings().each(function (t) {
                    if (t.hasClassName('block-title')) {
                        t.addClassName('expanded');
                    }
                });
            }
        }
    },

    _replaceEnterpriseTopWishlist: function (content) {
        var topWishlist = $$('a[href$="wishlist/"]').detect(function (element) {
            if (element.up('div.quick-access')) {
                return true;
            }
        });
        if (topWishlist) {
            var topWishlist_parent = topWishlist.up();
            topWishlist_parent.replace(content);
        }
    },

    replaceRewardsPoints: function (response) {
        if (response.rewards_points) {
            var block = $$('.rewards-box-spend-minicart').first();
            var content = response.rewards_points;
            if (block) {
                var js_scripts = content.extractScripts();
                if (content && content.toElement) {
                    content = content.toElement();
                } else if (!Object.isElement(content)) {
                    content = Object.toHTML(content);
                    var tempElement = document.createElement('div');
                    content.evalScripts.bind(content).defer();
                    content = content.stripScripts();
                    tempElement.innerHTML = content;
                    el = getElementsByClassName('rewards-box-spend-minicart', tempElement);
                    if (el.length > 0) {
                        content = el[0];
                    }
                    else {
                        return;
                    }
                }
                block.parentNode.replaceChild(content, block);
                Event.stopObserving(document, "dom:loaded");
                for (var i = 0; i < js_scripts.length; i++) {
                    if (typeof(js_scripts[i]) != 'undefined') {
                        globalEval(js_scripts[i]);
                    }
                }
                try {
                    Event.fire(document, "dom:loaded");
                } catch (e) {
                }
            }
        }
    },

    reloadProductScripts: function () {
        if ((typeof(Enterprise) != 'undefined') && Enterprise.Bundle.oldReloadPrice) {
            Product.Bundle.prototype.reloadPrice = Enterprise.Bundle.oldReloadPrice;
        }
        if ($('product_addtocart_form') && (typeof(productAddToCartForm) != 'undefined')) {
            var content = $('product_addtocart_form').up('.col-main');
            if (content) {
                $$('.super-attribute-select').each(function (element) {
                    Event.stopObserving(element);
                    element.value = '';
                    element.childSettings = false;
                    element.prevSetting = false;
                    element.nextSetting = false;
                    element.disabled = false;
                });
                var js_scripts = content.innerHTML.extractScripts();
                for (var i = 0; i < js_scripts.length; i++) {
                    if (typeof(js_scripts[i]) != 'undefined') {
                        try {
                            globalEval(js_scripts[i]);
                        } catch (e) {
                        }
                    }
                }
                if (typeof(bundle) != 'undefined') {
                    var options = bundle.config.options;
                    for (key in options) {
                        if (options.hasOwnProperty(key)) {
                            var option = options[key];
                            if ($('bundle-option-' + key)) {
                                bundle.changeSelection($('bundle-option-' + key));
                            }
                            var selections = option.selections;
                            for (sel_key in selections) {
                                if (selections.hasOwnProperty(sel_key)) {
                                    if ($('bundle-option-' + key + '-' + sel_key)) {
                                        if ($('bundle-option-' + key + '-' + sel_key).checked) {
                                            bundle.changeSelection($('bundle-option-' + key + '-' + sel_key));
                                        }
                                    }
                                }
                            }

                        }
                    }
                }
            }
            productAddToCartForm = new VarienForm('product_addtocart_form');
            productAddToCartForm.submit = function () {
                if (productAddToCartForm.validator.validate()) {
                    GomageProcartConfig.addtoCartProduct();
                }
            }

        }
    }

};

function ProcartGetUrlParam(url, name) {
    name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
    var regexS = "[/\\?&]" + name + "[/=]([0-9]*)";
    var regex = new RegExp(regexS);
    var results = regex.exec(url);
    if (results == null)
        return "";
    else
        return results[1];
}

var globalEval = function globalEval(src) {
    if (window.execScript) {
        window.execScript(src);
        return;
    }
    var fn = function () {
        window.eval.call(window, src);
    };
    fn();
};

function getElementsByClassName(classname, node) {
    var a = [];
    var re = new RegExp('\\b' + classname + '\\b');
    var els = node.getElementsByTagName("*");
    for (var i = 0, j = els.length; i < j; i++) {
        if (re.test(els[i].className))a.push(els[i]);
    }
    return a;
}