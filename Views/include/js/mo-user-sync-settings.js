jQuery(document).ready(function (e) {

    jQuery('#select-server').on('change', function () {
        let server = jQuery(this).find(':selected').text();
        serverCall(server);
    });

    function serverCall(server) {
        let formdata = new FormData();
        formdata.append('mo_server_type_ID', server);
        formdata.append('nonce', ajax_var.nonce);
        formdata.append('action', 'mo_server_type');

        ajax_request = jQuery.ajax({
            url: ajax_object_user_sync.ajax_url_user_sync,
            type: "POST",
            data: formdata,
            processData: false,
            contentType: false
        });

        ajax_request.done((response) => {
                jQuery('#append_div').empty().append(response.data).prop('hidden', false)
            }
        );
        ajax_request.fail((error) => {

        });
    }


})

function mo_user_sync_valid_query(f) {
    !(/^[a-zA-Z?,.\(\)\/@ 0-9]*$/).test(f.value) ? f.value = f.value.replace(
        /[^a-zA-Z?,.\(\)\/@ 0-9]/, '') : null;
}

window.onload = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const currentActiveTab = urlParams.get('attributeMapping');

    if (currentActiveTab) {
        if (currentActiveTab == "0") {
            document.querySelector("#remote-sync").classList.add("active")
            document.querySelector("#attr-mapping").classList.remove("active")
        } else if (currentActiveTab == "1") {
            document.querySelector("#remote-sync").classList.remove("active")
            document.querySelector("#attr-mapping").classList.add("active")
        }
    }

}
