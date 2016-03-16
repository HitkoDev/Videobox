/// <reference path="jquery.d.ts" />

(function($: JQueryStatic) {

    $(window).on('load', function(evt: JQueryEventObject) {
        $('input.vb-prop-set').each(function(i: number, el: Element) {
            var
                id: string = $(el).attr('id'),
                values: any = $.parseJSON($(el).val()),
                list: JQuery = $('#list-' + id),

                fieldsContainer: JQuery = $('#fields-' + id),
                key: string = 'default';
            
            // function to set the value of varoius input elements
            function setVal(el: JQuery, value: any): void {
                switch (el.attr("type")) {
                    case "radio":
                    case "checkbox":
                        el.each(function() {
                            if ($(this).attr('value') == value) {
                                $(this).attr("checked", 1);
                            } else {
                                $(this).removeAttr('checked');
                            }
                        });
                        break;
                    case "text":
                    case "hidden":
                    default:
                        el.val(value);
                        break;
                }
            }
            
            // function to get the current value of input elements
            function getVal(name: string): string {
                var p = fieldsContainer.find('input, select, textarea').serializeArray();
                for (var i = 0; i < p.length; i++) if (p[i].name == name) return p[i].value;
                return '';
            }
            
            // set the current key
            function setKey(k: string): void {
                key = k;
                
                // create new set on blank or unknown key, clone the default values
                if (!key || !values[key]) {
                    key = '__new';
                    values[key] = $.extend({}, values['default']);
                    values[key]['__name'] = '';
                    values[key]['__key'] = '';
                }
                
                // set active item
                $(list).find('li.set-active').toggleClass('set-active', false);
                $(list).find('li[data-set="' + key + '"]').toggleClass('set-active', true);
                
                // set values according to the selected set
                $.each(values[key], function(k, val): void {
                    setVal($('*[name="' + k + '"]'), val);
                });
            }
            
            // bind listeners to the input items
            $.each(values[key], function(k: string, val: any): void {
                var field: JQuery = $('*[name="' + k + '"]');
                
                // set first value
                setVal(field, val);
                
                // bind listener
                field.on('change select click', function(evt: JQueryEventObject): boolean {

                    var newVal: string = getVal(k).trim();

                    if (k == '__key') {
                        // make sure keys are valid (no spaces or specail characters)
                        var nv = newVal.replace(/(^\W+)|(\W+$)/g, '').replace(/\W+/g, '-').toLowerCase();
                        
                        // don't allow blank keys
                        if (!nv) return false;
                        
                        // default key can't be changed
                        if (key == 'default') {
                            setVal(field, key);
                            return false;
                        }
                        
                        // fix invalid values
                        if (newVal != nv) {
                            newVal = nv;
                            setVal(field, nv);
                        }
                        
                        // if key didn't change, return
                        if (newVal == key) return true;
                        
                        // if key already exists, return
                        if (values[newVal]) return false;
                    }
                    
                    // find the list item belonging to the new key
                    var item = $(list).find('li[data-set="' + key + '"]');
                    
                    // create new item if neccesary
                    if (item.length < 1) {
                        item = $('<li data-set="' + key + '" class="set-active"><span class="set-name">' + (values[key]['__name'] ? values[key]['__name'] : key) + '</span><span class="icon-cancel btn btn-small"></span></li>');
                        item.insertBefore($(list).children(':last-child'));
                    }
                    
                    // update the key in the values array and the coresponding list time
                    if (k == '__key') {
                        values[newVal] = $.extend({}, values[key]);
                        values[key] = undefined;
                        key = newVal;
                        item.attr('data-set', newVal);
                        if (!values[key]['__name']) item.find('.set-name').html(key);
                    }
                    
                    // update the coresponding list item
                    if (k == '__name') {
                        item.find('.set-name').html(newVal ? newVal : key);
                    }
                    
                    // update the value in the array
                    values[key][k] = newVal;
                    setVal($(el), JSON.stringify(values));
                });
            });
            
            // load set on item click
            $(list).on('click', 'li .set-name', function(evt: JQueryEventObject) {
                var element = this;
                setKey($(element).parent().attr('data-set'));
            });
            
            // remove set on remove button
            $(list).on('click', 'li .icon-cancel', function(evt) {
                var element = this;
                var k = $(element).parent().attr('data-set');
                if (values[k]) values[k] = undefined;
                $(element).parent().remove();
                setVal($(el), JSON.stringify(values));
                if (k == key) {
                    setKey('default');
                }
            });

        });
    });

})(jQuery);