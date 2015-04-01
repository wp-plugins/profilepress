/**
 * Javascript code for Profile fontend
 * @since 2.0
 * @package Profile
 * @author Rahul Aryan
 * @license GPL 2+
 */
(function($) {
    /* on start */
    $(function() {
        /* create document */
        Profile.site = new Profile.site();
        /* need to call init manually with $ */
        Profile.site.initialize();
    });
    /* namespace */
    window.Profile = {};
    Profile.site = function() {};
    Profile.site.prototype = {
        /** Initalize the class */
        initialize: function() {
            ProfileSite = this;
            this.ajax_id = new Object();
            this.loading = new Object();
            this.errors;
            this.ajaxData;
            this.afterAjaxComplete();
            this.appendFormError();
            this.appendMessageBox();
            this.pp_ajax_form();
            this.edit_pp_field();
            this.cancel_update_field();
            this.uploadForm();
        },
        doAjax: function(query, success, context, before, abort) {
            /** Shorthand method for calling ajax */
            context = typeof context !== 'undefined' ? context : false;
            success = typeof success !== 'undefined' ? success : false;
            before = typeof before !== 'undefined' ? before : false;
            abort = typeof abort !== 'undefined' ? abort : false;
            var action = ppGetValueFromStr(query, 'pp_ajax_action');
            if (abort && (typeof ProfileSite.ajax_id[action] !== 'undefined')) {
                ProfileSite.ajax_id[action].abort();
            }
            ProfileSite.showLoading();
            var req = $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: query,
                beforeSend: before,
                success: success,
                dataType: 'json',
                context: context,
            });
            ProfileSite.ajax_id[action] = req;
            return req;
        },
        /**
         * Process to run after completing an ajax request
         * @return {void}
         * @since 2.0
         */
        afterAjaxComplete: function() {
            $(document).ajaxComplete(function(event, data, settings) {
                ProfileSite.hideLoading();
                if (typeof data !== 'undefined' && typeof data.responseJSON !== 'undefined' && typeof data.responseJSON.pp_responce !== 'undefined') {
                    var data = data.responseJSON;
                    if (typeof data.message !== 'undefined') {
                        var type = typeof data.message_type === 'undefined' ? 'success' : data.message_type;
                        ProfileSite.addMessage(data.message, type);
                    }
                    $(document).trigger('pp_after_ajax', data);
                    if (typeof data.do !=='undefined') {
                        var dos = data.do .
                        match(/\S+/g);
                        $.each(dos, function(i, action) {
                            var action = $.trim(action);
                            if (typeof ProfileSite[action] === 'function') ProfileSite[action](data);
                        });
                    }
                    if (typeof data.view !== 'undefined') {
                        $.each(data.view, function(i, view) {
                            $('[data-view="' + i + '"]').text(view);
                            if (view !== 0) $('[data-view="' + i + '"]').removeClass('pp-view-count-0');
                        });
                    }
                }
            });
        },
        uniqueId: function() {
            return $('.pp-uid').length;
        },
        showLoading: function() {
            var uid = this.uniqueId();
            var el = $('<div class="pp-loading-icon pp-uid" id="apuid-' + uid + '"><i class="apicon-sync"><i></div>');
            $('body').append(el);
            return '#apuid-' + uid;
        },
        hideLoading: function() {
            $('.pp-loading-icon').hide();
        },
        pp_ajax_form: function() {
            $('body').delegate('[data-action="pp_ajax_form"]', 'submit', function() {
                if (typeof tinyMCE !== 'undefined') tinyMCE.triggerSave();
                ProfileSite.doAjax(ppAjaxData($(this).formSerialize()), function(data) {}, this);
                return false;
            })
        },
        appendFormError: function() {
            $(document).on('pp_after_ajax', function(e, data) {
                if (typeof data.errors !== 'undefined') {
                    ProfileSite.clearFormErrors(data.form);
                    $.each(data.errors, function(i, message) {
                        var parent = $('#' + data.form).find('#' + i).closest('.pp-form-fields');
                        parent.addClass('pp-have-error');
                        ProfileSite.helpBlock(parent, message);
                    });
                }
            });
        },
        helpBlock: function(elm, message) {
            /* remove existing help block */
            if ($(elm).find('.pp-form-error-message').length > 0) $(elm).find('.pp-form-error-message').remove();
            $(elm).append('<p class="pp-form-error-message">' + message + '</p>');
        },
        clearFormErrors: function(form) {
            var elm = $('#' + form).find('.pp-have-error');
            elm.find('.pp-form-error-message').remove();
            elm.removeClass('pp-have-error');
        },
        appendMessageBox: function() {
            if ($('#pp-notify').length == '0') $('body').append('<div id="pp-notify"></div>');
        },
        addMessage: function(message, type) {
            var icon = pplang[type];
            $('<div class="pp-notify-item ' + type + '"><i class="' + icon + '"></i>' + message + '</div>').appendTo('#pp-notify').animate({
                'margin-left': 0
            }, 500).delay(5000).fadeOut(200);
        },
        redirect: function(data) {
            console.log(typeof data.redirect_to !== 'undefined');
            if (typeof data.redirect_to !== 'undefined') window.location.replace(data.redirect_to);
        },
        append: function(data) {
            if (typeof data.container !== 'undefined') $(data.container).append(data.html);
        },
        replace: function(data) {
            if (typeof data.container !== 'undefined') $(data.container).html(data.html);
        },
        addClass: function(data) {
            if (typeof data.container !== 'undefined') $(data.container).addClass(data.class);
        },
        removeClass: function(data) {
            if (typeof data.container !== 'undefined') $(data.container).removeClass(data.class);
        },
        updateAvatar: function(data) {
            if (typeof data.container !== 'undefined') $(data.container).attr('src', $(data.html).attr('src'));
        },
        edit_pp_field: function() {
            $('body').delegate('[data-action="edit_pp_field"]', 'click', function(e) {
                e.preventDefault();
                var q = $(this).attr('data-query');
                ProfileSite.doAjax(ppAjaxData(q), function(data) {
                    $(this).hide();
                }, this);
            });
        },
        cancel_update_field: function() {
            $('body').delegate('[data-action="cancel_update_field"]', 'click', function(e) {
                e.preventDefault();
                var q = $(this).attr('data-query');
                ProfileSite.doAjax(ppAjaxData(q));
            });
        },
        uploadForm: function() {
            var self = this;
            $('[data-action="pp_upload_form"]').change(function() {
                $(this).closest('form').submit();
            });
            $('[data-action="pp_upload_form"]').submit(function() {
                $(this).ajaxSubmit({
                    url: ajaxurl,
                    dataType: 'json'
                })
                return false
            });
        },
        cancel_update_field: function() {
            $('body').delegate('[data-action="favorite"]', 'click', function(e) {
                e.preventDefault();
                var q = $(this).attr('data-query');
                ProfileSite.doAjax(ppAjaxData(q));
            });
        },
    }
})(jQuery);

function ppAjaxData(param) {
    param = param + '&action=pp_ajax';
    return param;
}

function ppGetValueFromStr(q, name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(q);
    return results == null ? false : decodeURIComponent(results[1].replace(/\+/g, " "));
}