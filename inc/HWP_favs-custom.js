;
"use strict";
var doingAjax = false;
var $ = jQuery.noConflict();

var messages = HWP_Favs.messages;

$(document).ready(function() {
    $(".badge-delete-fav").on('click keydown', function(eve) {
        if (eve.keyCode != 13 && eve.type != 'click') {
            return;
        }
        if (!confirm(messages.areYouSure)) {
            return;
        }
        if (doingAjax) {
            return;
        }
        doingAjax = true;
        var that = $(this).find('.delete-fav');
        var postId = that.data("post-id");
        var userId = that.data("user-id");

        $.ajax({
            url: HWP_Favs.ajaxurl,
            method: 'POST',
            cache: false,
            data: {
                action: 'HWP_delete_fav',
                pId: postId,
                uId: userId
            },
            beforeSend: function() {
                that.parent().append("<i class='dashicons dashicons-clock wait-for-ajax'></i>");
                that.hide();
            },
            success: function(data, textStatus, jqXHR) {
                $(".bookmarked").html(data);
                that.closest(".fav-item").fadeOut(600, function() {
                    $(this).remove();
                    if ($(".fav-item").length === 0) {
                        $(".empty-list").html(messages.youDonotHaveFavs);
                        $(".list-favs").remove();
                    }
                });
            },
            complete: function() {
                doingAjax = false;
                $(".wait-for-ajax").remove();
                that.show();
            }
        });
    });

    $("#save-fav").on('click keydown', makeAjax);

});

function makeAjax(eve, inList) {
    if (eve.keyCode != 13 && eve.type != 'click') {
        return;
    }
    if (doingAjax) {
        return;
    }
    doingAjax = true;
    
    var that = $(this);
    var postId = that.data("post-id");
    var userId = that.data("user-id");

    var actionMethod;
    var titleMessages;
    if (!that.hasClass("active")) {
        actionMethod = 'HWP_save_fav';
        titleMessages = messages.removeItem;
    } else {
        actionMethod = 'HWP_delete_fav';
        titleMessages = messages.addItem
    }
    $.ajax({
        url: HWP_Favs.ajaxurl,
        method: 'POST',
        cache: false,
        data: {
            action: actionMethod,
            pId: postId,
            uId: userId
        },
        beforeSend: function() {
            that.parent().append("<i class='dashicons dashicons-clock wait-for-ajax'></i>");
            that.hide();
        },
        success: function(data, textStatus, jqXHR) {
            that.toggleClass("active");
            $(".bookmarked").html(data);
            that.attr('title', titleMessages);
        },
        complete: function() {
            doingAjax = false;
            $(".wait-for-ajax").remove();
            that.show();
        }
    });
}