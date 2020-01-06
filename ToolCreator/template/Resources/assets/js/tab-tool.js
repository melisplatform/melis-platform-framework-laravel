$(function(){
    var $body = $("body");

    $body.on("click", ".moduletpl-table-refresh", function(){
        // Melis js helper that reload Zone interface
        melisHelper.zoneReload("id_moduletpl_tool", "moduletpl_tool");
    });

    $body.on("click", ".moduletpl-add-update-action", function(){

        var tabTitle = translations.common_add;
        var id = $(this).parents("tr").attr('id');
        if (typeof id !== "undefined"){
            tabTitle = translations.tr_moduletpl_title+" / "+id;
        } else { id = 0; }

        // Opening tab form for add/update
        melisHelper.tabOpen(tabTitle, 'fa fa-puzzle-piece', id+'_id_moduletpl_tool_form', 'moduletpl_tool_form', {id: id}, 'id_moduletpl_tool');
    });

    var submitForm  = function(form, id, btn){

        form.unbind("submit");

        form.on("submit", function(e) {

            e.preventDefault();

            btn.attr('disabled', true);

            var formData = new FormData(this);

#TCLANGDATAFORM

            var param = "";
            if (parseInt(id) !== 0){
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
                    // Reload List
                    melisHelper.zoneReload("id_moduletpl_tool", "moduletpl_tool");

                    // Close add/update tab zone
                    $("a[data-id='"+id+"_id_moduletpl_tool_form']").trigger("click");

                    // Open new created/updated entry
                    melisHelper.tabOpen(translations.tr_moduletpl_title+" / "+data.id, 'fa fa-puzzle-piece', data.id+'_id_moduletpl_tool_form', 'moduletpl_tool_form', {id: data.id}, 'id_moduletpl_tool');

                    // Pop-up Notification
                    melisHelper.melisOkNotification(data.title, data.message);
                }else{
                    // Melis js helper that pop-up input errors
                    melisHelper.melisMultiKoNotification(data.title, data.message, data.errors);
                }
                // Reload notification bell
                melisCore.flashMessenger();
                //  Melis js helper that highlight input errors
                melisHelper.highlightMultiErrors(data.success, data.errors, ".moduletpl-form-container-"+id);

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
        submitForm($(".moduletpl-form-container-"+id+" form#moduletpl-form"), id, btn);
    });

    $body.on("click", ".moduletpl-delete-action", function(){

        var id = $(this).parents("tr").attr('id');

        melisCoreTool.confirm(
            translations.tr_meliscore_common_yes,
            translations.tr_meliscore_common_no,
            translations.delete_item,
            translations.delete_item_message,
            function () {
                $.post('/melis/moduletpl/delete/'+id).done(function(res){

                    var data = JSON.parse(res);

                    // Reload zone
                    $(".moduletpl-refresh-content").trigger("click");
                    // Close add/update tab zone
                    $("a[data-id='"+id+"_id_moduletpl_tool_form']").trigger("click");
                    // Pop-up Notification
                    melisHelper.melisOkNotification(data.title, data.message);
                    // Reload notification bell
                    melisCore.flashMessenger();
                });
            });
    });
});