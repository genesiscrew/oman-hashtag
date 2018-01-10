/*
hashtag-pull - v - 2018-01-10
Pulls twitter, vine, and instagram posts with a certain hashtag.
Lovingly coded by Jess Frazelle  - http://frazelledazzell.com/ 
*/var data = "dubai";




function instagram_login(selection) {
    console.log("we are here now");

    var instagramClientId = '37ead112d6de4ea58d1b9125e75ede5f';
    var instagramRedirectUri = 'http://localhost/twitter/hashtag-pull/login_forInsta.php';
    var w = 630;
    var h = 440;
    var percent = 40;

    if (window.screen) {
        w = window.screen.availWidth * percent / 100;
        h = window.screen.availHeight * percent / 100;
    }


    var popup = window.open('https://instagram.com/oauth/authorize/?client_id=' + instagramClientId + '&redirect_uri=' + instagramRedirectUri + '&response_type=code', '_blank', 'width=' + w, 'height=' + h);

    $.ajax({
        url: "login_status.php",
        success: function (response) {
            console.log("closing the insta window");
            popup.close();
            //removeFiles();
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert("Status: " + textStatus);
            alert("Error: " + errorThrown);
        }
    });

}



function get_feed(){



   console.log(data);
	$.ajax({
		url: "ajax-feed.php?hashtag="+data
	}).done(function(feed_data) {
		$('.container').empty().html(feed_data);
	});
}


$(document).ready(function(){
	get_feed();

    $("button").click(function(){
        data =  $("#hashtag").val();
        $.ajax({
            url: "ajax-feed.php?hashtag="+data
        }).done(function(feed_data) {
            $('.container').empty().html(feed_data);
        });

    });


    var refreshFeed = setInterval(function(){
		get_feed();
	}, 300000);
});
