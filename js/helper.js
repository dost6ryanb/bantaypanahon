//helper.js
var xhrPoolHelper = (function($) {
    $.xhrPool = [];
    $(document).ajaxSend(function(e, jqXHR, options){
        $.xhrPool.push(jqXHR);
    });
    $(document).ajaxComplete(function(e, jqXHR, options) {
        var index = $.xhrPool.indexOf(jqXHR);
        if (index > -1) {
            $.xhrPool.splice(index, 1);
        }
    });

    return {
        abortAll: function() {
            $.each($.xhrPool, function(idx, jqXHR) {
                jqXHR.abort();
            });
            $.xhrPool = [];
        }
    }
});
