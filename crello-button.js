function crelloSocialMediaCallback(url) {
    const postId = jQuery('#post_ID').val();
    const data = {
        action: 'uploadImage',
        crelloImageUrl: url,
        attachPostId: postId,
    };
    jQuery.post(ajaxurl, data, function(response) {
        send_to_editor(`<img src="${response}" style="height:50%; width:50%;">`)
    });
}