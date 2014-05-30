/**
 * GoMage ProCart Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2014 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.1
 * @since        Class available since Release 2.0
 */

function glcChangeQtySettings(){
    if ($('gomage_procart_qty_settings')){
        if ($('gomage_procart_qty_settings').visible()){
            $$('.glc-qty_settings-child-head, .glc-qty_settings-child').each(function(e){
                e.show();
            });
        }else{
            $$('.glc-qty_settings-child-head, .glc-qty_settings-child').each(function(e){
                e.hide();
            });
        }
    }
}

function glcChangeConfirmWindow(){
    if ($('gomage_procart_confirm_window')){
        if ($('gomage_procart_confirm_window').visible()){
            $$('.glc-confirm_window-child-head, .glc-confirm_window-child').each(function(e){
                e.show();
            });
        }else{
            $$('.glc-confirm_window-child-head, .glc-confirm_window-child').each(function(e){
                e.hide();
            });
        }
    }
}

Event.observe(document, 'dom:loaded', function() {
    //qty_settings
    ['gomage_procart_skin_qty_button_design-head', 'gomage_procart_skin_detract_qty_button_design-head'].each(function(e){
            if ($(e)){
                $(e).up('div').addClassName('glc-qty_settings-child-head');
            }
        });
    ['gomage_procart_skin_qty_button_design', 'gomage_procart_skin_detract_qty_button_design'].each(function(e){
            if ($(e)){
                $(e).addClassName('glc-qty_settings-child');
            }
        });
    glcChangeQtySettings();

    Event.observe($('gomage_procart_qty_settings-head'),'click', function(){
        glcChangeQtySettings();
    });

    //confirm_window
    ['gomage_procart_confirm_wind-head', 'gomage_procart_continue_btn_design-head','gomage_procart_cart_button_design-head'].each(function(e){
        if ($(e)){
            $(e).up('div').addClassName('glc-confirm_window-child-head');
        }
    });
    ['gomage_procart_confirm_wind', 'gomage_procart_continue_btn_design', 'gomage_procart_cart_button_design'].each(function(e){
        if ($(e)){
            $(e).addClassName('glc-confirm_window-child');
        }
    });
    glcChangeConfirmWindow();

    Event.observe($('gomage_procart_confirm_window-head'),'click', function(){
        glcChangeConfirmWindow();
    });


});