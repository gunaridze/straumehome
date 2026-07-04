jQuery(function($){
    $('.yam-repeater-field__add-btn').on('click', function(){
        let $repeater = $(this).closest('.yam-repeater-field'),
            $row = $repeater.find('.yam-repeater-field__row_tpl').eq(0).clone().removeClass('yam-repeater-field__row_tpl');

        $('.yam-repeater-field__rows').append($row);

        reindexRows($repeater);
    });

    $(document).on('click', '.yam-repeater-field__remove-btn',function(){
        let $btn = $(this),
            $repeater = $btn.closest('.yam-repeater-field'),
            $row = $btn.closest('.yam-repeater-field__row');

        $row.remove();
        reindexRows($repeater);
    });

    $(document).on('change keyup input click', '[data-input-type="number"]',function(){
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    $(document).on('click', '.yam-spoiler__btn', function(){
        var $btn = $(this),
            $spoiler = $btn.closest('.yam-spoiler'),
            $content = $spoiler.find('.yam-spoiler__content');

        $btn.toggleClass('active');

        if ($btn.hasClass('active')) {
            $content.addClass('active')
        } else {
            $content.removeClass('active')
        }
    });

    /*$(document).on('click', '.yam-notice .notice-dismiss', function(){
        var $btn = $(this),
            $message = $btn.closest('.yam-notice'),
            messageId = $message.data('id');

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                messageId: messageId,
                action: 'yam_dismiss_message',
            }
        });
    });
*/
    //functions

    function reindexRows($repeater){
        //reindex row inputs
        $repeater.find('.yam-repeater-field__row').not('.yam-repeater-field__row_tpl').each(function(index){
            const $row = $(this);

            $row.find('input.yam-repeater-field__input').each(function(){
                let $input = $(this),
                    name = $input.attr('data-name');

                $input.attr('name', name.replace(/\$/, index));
            });
        });
    }
});
