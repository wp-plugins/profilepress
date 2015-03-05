jQuery(document).ready(function($) {
    $('.pp_field_group_anchor').click(function(e) {
        e.preventDefault();
        $('.pp_field_group_anchor, .pp_field_group').removeClass('active');
        $(this).addClass('active');
        $($(this).attr('href')).addClass('active');
    });
    $(".pp-datepicker").each(function() {
        var $this = $(this);
        $this.datepicker({
            dateFormat: $this.data('format'),
            changeMonth: $this.data('month'),
            changeYear: $this.data('year'),
            yearRange: $this.data('yearrange'),
        });
    });
    $('.pp_field_meta_box #field_type').change(function(event) {
        $.ajax({
            type: 'GET',
            url: ajaxurl,
            data: {
                action: 'pp_field_type_options',
                'type': $(this).val(),
                'post_id': $(this).data('post_id')
            },
            success: function(data) {
                $('#pp_field_type_fields').html(data);
            },
            dataType: 'html',
            context: this,
        });
    }).change();
    $('body').delegate('[data-action="add_repeat_field"]', 'click', function(e) {
        e.preventDefault();
        var parent = $(this).parent().find('[data-cont="default_repat_field"]');
        var html = parent.clone();
        var count = $('.pp_repeat_field').length - 1;
        var main_c = $(this).closest('[data-cont="repeat_fields"]');
        $(html).removeAttr('data-cont').attr('id', 'field_options_' + count).show().find('input[name="key"]').attr('name', 'field_options[' + count + '][key]');
        $(html).find('input[name="value"]').attr('name', 'field_options[' + count + '][value]')
        $(this).before(html);
    });
    $('body').delegate('[data-action="toggle"]', 'click', function(e) {
        var show = $(this).data('show');
        var hide = $(this).data('hide');
        $(show).toggle(200);
        $(hide).toggle(200);
    });
    jQuery('#options_form').submit(function() {
        var checkboxes = jQuery.param(jQuery(this).find('input:checkbox:not(:checked)').map(function() {
            return {
                name: this.name,
                value: this.checked ? this.value : '0'
            };
        }));
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: jQuery(this).formSerialize() + '&' + checkboxes,
            context: this,
            dataType: 'json',
            success: function(data) {
                if (data['status']) {
                    var html = jQuery(data['html']);
                    jQuery('.wrap').prepend(html);
                    jQuery('html, body').animate({
                        scrollTop: 0
                    }, 300);
                    jQuery('.wrap .updated').delay(500).slideDown(300);
                    html.delay(5000).slideUp(300);
                }
            }
        });
        return false;
    });
});