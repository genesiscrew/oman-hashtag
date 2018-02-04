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


 console.log("about to post data");
    console.log(selection);

    $.ajax({
        url: "ajax-feed.php?hashtag=" + data + '&function=addDB',
        headers : {'Content-Type':'application/json'},
        dataType:'text',
        type: 'post',
        success: function (data) {
            console.log("we are here");
            console.log(data);
        },
        data: selection,
        error: function (XMLHttpRequest, textStatus, errorThrown) {
        alert("Status: " + textStatus);
        alert("Error: " + errorThrown);
    }

    })


}

function deleteFromDB(selection,source_id) {


    console.log("about to delete data");
    console.log(selection);

    $.ajax({
        url: "ajax-feed.php?source_id=" + source_id + '&function=deleteFromDB',
        success: function (data) {
            console.log("we are here");
            console.log(data);
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
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert("Status: " + textStatus);
            alert("Error: " + errorThrown);
        }
    });
}


$(document).ready(function(){
    //get_feed();


    //get html input, then insert into site

    /*$( ".container").append( "<script src=\"//assets.juicer.io/embed.js\" type=\"text/javascript\"></script>\n" +
        "<link href=\"//assets.juicer.io/embed.css\" media=\"all\" rel=\"stylesheet\" type=\"text/css\" />\n" +
        "<ul class=\"juicer-feed\" data-feed-id=\"dubai\"><h1 class=\"referral\"><a href=\"https://www.juicer.io\">Powered by Juicer</a></h1></ul>" ); */

    console.log("we are here now");


  

    $(document).on('click','.addDB',function(){
        //

        var tmp = myArray[$(this).attr('id').split("-")[1]];

       addToDB(tmp,data);




    });


    $(document).on('click','.deleteDB',function(){
        //
        console.log($(this).attr('id'));
        var tmp = myArray[$(this).attr('id').split("-")[1]];
        console.log(tmp);
        var input =  myArray[$(this).attr('id').split("-")[1]].id;
        console.log("we us heres");

        deleteFromDB(tmp,input);




    });

    $("#taghash").click(function() {

        $(".twitter-feed").remove();
        data = $("#hashtag").val();
        $.ajax({
            dataType : "json",
            url: "ajax-feed.php?hashtag=" + data + '&function=twitter',
            success: function (response) {

                // myArray = JSON.stringify(response);

                console.log(response);

                myArray = response;


               var rowCounter = 0;
               var rowLength = 2;

                for (var e = 0; e < myArray.length; e+= rowLength) {

                    $( ".container").append( "<div class=\"row twitter-feed\" id=\"row"+rowCounter+"\"></div>" );
                    rowCounter++;


                }
                var rowCounter2 = 0;
                var rowCounter3 = 0;
               for (var i = 0; i < myArray.length; i++) {
                   var item = myArray[i];

                   if (rowCounter2 == rowLength+1) {
                       rowCounter3++;
                       rowCounter2= 0;
                   }
                   setTweet(i,item,rowCounter3,data);

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

/**
 * get html from user input then inject it into container
 * @param data
 */
function addInstagramHTML(data) {

    $( ".container").append( "" );


}

function setTweet(id, tweet,row,data) {

    var name = (tweet.user.name.length < 17) ? tweet.user.name : tweet.user.name.substr(0, 15) + "..";

    console.log(tweet.user.name);

    var text = tweet.text;

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
		<span class="name"><a href="http://twitter.com/' +
        tweet.user.name + '">@' + tweet.user.name + '</a></span>\
        \<button class="btn-success deleteDB" id="deleteDB-'+id+'"> Delete from DB</button>\
        \<button class="btn-success addDB" id="addDB-'+id+'">\n' +
        '\t\t\t\t\tAdd to DB\n' +
        '\t\t\t\t</button>\
	</div>';
    var tid = "tweet-"+(id+1);
    console.log(".row"+row+"");

    $( "#row"+row+"" ).append( "<div class=\"tweet col-md-3\" id=\""+tid+"\">"+content +"</div>" );

    //$('.tweet').html(content);
}
