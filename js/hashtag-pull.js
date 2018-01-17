/*
hashtag-pull - v - 2018-01-15
Pulls twitter, vine, and instagram posts with a certain hashtag.
Lovingly coded by Jess Frazelle  - http://frazelledazzell.com/ 
*/
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
        url: "ajax-feed.php?hashtag="+data,
        success: function (response) {
            console.log("closing the insta window");
            console.log(response) ;
            //removeFiles();
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert("Status: " + textStatus);
            alert("Error: " + errorThrown);
        }
    });
}


$(document).ready(function(){
    //get_feed();

    console.log("are we here ");

    $("#taghash").click(function() {
        data = $("#hashtag").val();
        $.ajax({
            url: "ajax-feed.php?hashtag=" + data + '&function=insta',
            success: function (response) {
                console.log("closing the insta window");
                var myArray = JSON.parse(response);

                var item = JSON.parse(myArray[0]);
                console.log(item.caption.text);


                setTweet(0,item);

            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert("Status: " + textStatus);
                alert("Error: " + errorThrown);
            }
        });

    });


    var refreshFeed = setInterval(function(){
        get_feed();
    }, 300000);
});


function setTweet(id, tweet) {
    console.log(tweet);
    var name = (tweet.user.full_name.length < 17) ? tweet.name : tweet.name.substr(0, 15) + "..";

    var text = tweet.caption.text;

    console.log(text);

    /* if there is an uri in the text, complement it with a 'href' */
    var regex = /[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?/gi;
    var uri = text.match(regex);

    if (uri != 1) {
        for (var i in uri)
            text = text.replace(uri[i],
                "<a href='" + uri[i] + "'>" + uri[i] + "</a>");
    }

    /* make hashtags clickable */
    var regex = /[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?/gi;
    var uri = text.match(regex);

    var content = '<div class="text">\
		<div>' + text + '</div>\
	</div>\
	\
	<div class="info">\
		<img class="author" src="' + tweet.user.profile_picture + '" alt="" align="left" />\
		<span class="name"><a href="http://instagram.com/' +
        tweet.user.username + '">@' + tweet.user.username + '</a></span>\
	</div>';
    var tid = "tweet"+id;

    $( ".container" ).append( "<div class=\"tweet\" id=\""+tid+"\">"+content +"</div>" );

    //$('.tweet').html(content);
}
