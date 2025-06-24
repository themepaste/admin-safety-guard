function wptpsa_post(data_object, success_method, data_type){
    data_type = data_type == undefined ? 'json' : data_type;
    jQuery.post({
        url: ajaxurl,
        dataType: data_type,
        data: data_object,
        success: success_method,
        error: function(xhr){
            console.log(xhr);
        }
    });
}

jQuery(document).ready(function($){
    $('#upload-btn').click(function(e) {
        e.preventDefault();
        var image = wp.media({ 
            title: 'Upload Image',
            // mutiple: true if you want to upload multiple files at once
            multiple: false
        }).open()
        .on('select', function(e){
            // This will return the selected image from the Media Uploader, the result is an object
            var uploaded_image = image.state().get('selection').first();
            // We convert uploaded_image to a JSON object to make accessing it easier
            // Output to the console uploaded_image
            // console.log(uploaded_image);
            var image_url = uploaded_image.toJSON().url;
            $('#attached_image').attr('src', image_url);
            // Let's assign the url value to the input field
            $('#attached_file').val(image_url);
        });
    });
});
