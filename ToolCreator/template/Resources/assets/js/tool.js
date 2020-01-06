$(function(){
     var $body = $("body");

    $body.on("click", ".moduletpl-table-refresh", function(){
        // Melis js helper that reload Zone interface
        melisHelper.zoneReload("id_moduletpl_tool", "moduletpl_tool");
    });

    var curReq = null;

    $body.on("click", ".moduletpl-add-update-action", function(){
        $("#id_moduletpl_generic_modal_tool_container").modal("show");

        var url = "/melis/moduletpl/form";
        var id = $(this).parents("tr").attr('id');
        if (typeof id !== "undefined"){
            url += "/"+id;
        }

        curReq = $.get(url).done(function(res){
            $("#id_moduletpl_generic_modal_tool_container .modal-dialog").html(res);
        });
    });

    $body.on("hidden.bs.modal", "#id_moduletpl_generic_modal_tool_container", function () {
        curReq.abort();
        $("#id_moduletpl_generic_modal_tool_container .modal-dialog")
            .html(melisHelper.loadingHtml());
    });



    var submitForm  = function(form, id, btn){

        form.unbind("submit");

        form.on("submit", function(e) {

            e.preventDefault();

            btn.attr('disabled', true);

            var formData = new FormData(this);

#TCLANGDATAFORM

            var param = "";
            if (typeof id !== "undefined"){
                param = "/"+id;
            }

            $.ajax({
                type: 'POST',
                url: '/melis/moduletpl/save'+param,
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
            }).done(function (res) {

                var data = JSON.parse(res);

                if (data.success){
                    // hide modal
                    $("#id_moduletpl_generic_modal_tool_container").modal("hide");
                    $(".moduletpl-refresh-content").trigger("click");

                    // Pop-up Notification
                    melisHelper.melisOkNotification(data.title, data.message);
                }else{
                    // Melis js helper that pop-up input errors
                    melisHelper.melisMultiKoNotification(data.title, data.message, data.errors);
                }
                // Reload notification bell
                melisCore.flashMessenger();
                //  Melis js helper that highlight input errors
                melisHelper.highlightMultiErrors(data.success, data.errors, ".moduletpl-form-container");

                btn.attr('disabled', false);

            }).fail(function () {
                console.log(translations.tr_meliscore_error_message);
            });
        });

        form.submit();
    };


    $body.on("click", ".moduletpl-btn-save-action", function(){
        var btn = $(this);
        var id = $(this).data("id");
        submitForm($("form#moduletpl-form"), id, btn);
    });

    $body.on("click", ".moduletpl-delete-action", function(){

        var albumId = $(this).parents("tr").attr('id');

        melisCoreTool.confirm(
            translations.tr_meliscore_common_yes,
            translations.tr_meliscore_common_no,
            translations.delete_item,
            translations.delete_item_message,
            function () {
                $.post('/melis/moduletpl/delete/'+albumId).done(function(res){

                    var data = JSON.parse(res);

                    $(".moduletpl-refresh-content").trigger("click");
                    // Pop-up Notification
                    melisHelper.melisOkNotification(data.title, data.message);
                    // Reload notification bell
                    melisCore.flashMessenger();
                });
            });
    });
});