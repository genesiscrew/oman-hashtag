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
var  myArray = [];

function addToDB(selection,data) {

 /*   $.ajax({
        url: "ajax-feed.php?hashtag=" + data + '&function=DB',
        success: function (response) {
            console.log("item added to DB");
            //removeFiles();
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert("Status: " + textStatus);
            alert("Error: " + errorThrown);
        }
    }); */

 console.log("about to post data");

    $.ajax({
        url: "ajax-feed.php?hashtag=" + data,
        type: 'post',
        dataType: 'json',
        success: function (data) {
            console.log("item added succesfully");
        },
        data: selection,
        error: function (XMLHttpRequest, textStatus, errorThrown) {
        alert("Status: " + textStatus);
        alert("Error: " + errorThrown);
    }

    })


}


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

    $(document).on('click','.addDB',function(){
        //addToDB(tweet,data)
        var tmp = $(this).attr('id').split("-");
        alert(tmp);

    });

    $("#taghash").click(function() {
        data = $("#hashtag").val();
        $.ajax({
            url: "ajax-feed.php?hashtag=" + data + '&function=insta',
            success: function (response) {
                console.log("closing the insta window");
                myArray = JSON.parse(response);


               var rowCounter = 0;
               var rowLength = 2;

                for (var e = 0; e < myArray.length; e+= rowLength) {

                    $( ".container").append( "<div class=\"row\" id=\"row"+rowCounter+"\"></div>" );
                    rowCounter++;


                }
                var rowCounter2 = 0;
                var rowCounter3 = 0;
               for (var i = 0; i < myArray.length; i++) {
                   var item = JSON.parse(myArray[i]);
                   if (rowCounter2 == rowLength+1) {
                       rowCounter3++;
                       rowCounter2= 0;
                   }
                   setTweet(rowCounter2,item,rowCounter3,data);

                   rowCounter2++;

               }


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


function setTweet(id, tweet,row,data) {
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
        \<button class="btn-success addDB" id="addDB-'+id+'">\n' +
        '\t\t\t\t\tAdd to DB\n' +
        '\t\t\t\t</button>\
	</div>';
    var tid = "tweet"+(id+1);
    console.log(".row"+row+"");

    $( "#row"+row+"" ).append( "<div class=\"tweet col-md-3\" id=\""+tid+"\">"+content +"</div>" );

    //$('.tweet').html(content);
}
