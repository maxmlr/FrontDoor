<html>
<head>
    <title>Paulanerplatz 1</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <link rel="shortcut icon" href="/static/img/favicon.ico" type="image/x-icon" />
    <link rel="apple-touch-icon" href="/static/img/apple-touch-icon.png" />
    <link rel="apple-touch-icon" sizes="57x57" href="/static/img/apple-touch-icon-57x57.png" />
    <link rel="apple-touch-icon" sizes="72x72" href="/static/img/apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon" sizes="76x76" href="/static/img/apple-touch-icon-76x76.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="/static/img/apple-touch-icon-114x114.png" />
    <link rel="apple-touch-icon" sizes="120x120" href="/static/img/apple-touch-icon-120x120.png" />
    <link rel="apple-touch-icon" sizes="144x144" href="/static/img/apple-touch-icon-144x144.png" />
    <link rel="apple-touch-icon" sizes="152x152" href="/static/img/apple-touch-icon-152x152.png" />

    <link rel="stylesheet" href="/static/css/jquery.mobile-1.4.5.min.css" />
    <script src="/static/js/jquery-1.11.1.min.js"></script>
    <script src="/static/js/jquery.mobile-1.4.5.min.js"></script>

    <script src="/static/js/google.fastbutton.js"></script>
    <script src="/static/js/jquery.google.fastbutton.js"></script>

    <script>
	$( document ).bind( "mobileinit", function()
	{
	    $.mobile.allowCrossDomainPages = true;
	    $.mobile.touchOverflowEnabled = true;
	});
        var viewport = {
            width  : $(window).width(),
            height : $(window).height()
        };
    </script>

</head>
<body>

<div data-role="page" data-title="Paulanerplatz 1" id="main">
    <script type="text/javascript">
	$( document ).ready(function()
	{
                $( "#main" ).width(viewport["width"]);

   		$( ".num-key" ).fastClick(function(e)
		{
	        	$("#key").val(($('#key').val()) + (this.value));
	 	});

$("form").submit(function(event) 
{
    event.preventDefault();

    var form = $(this);
    var action = form.attr("action"),
        method = form.attr("method"),
        data   = form.serialize();

    $.ajax({
        url : action,
        type : method,
        data : data
    }).done(function (result) {
	if (result == "DENIED")
	{
	$("#user").attr("value",result);
            $("#user").css({
                "background": "red",
		"color": "white"
            });
	} else {
	$("#user").attr("value","Hi "+result+"!");
            $("#user").css({
                "background": "lightgreen"
            });
	}
    }).fail(function(result) {
	$("#user").attr("value","error ... :(");
    }).always(function(result) {
        form.trigger("reset");
	$("#message").fadeIn().delay(1000).fadeOut();
        window.setTimeout(function() {
            $("#user").css({
                "background": "white",
		"color": "black"
            });
        }, 2000);
    });
});

	});
    </script>

    <div data-role="header" data-theme="b">
        <h1>Paulanerplatz 1</h1>
    </div>

<div data-role="content">

<div style="width:200px;margin: 0 auto;">

<form action="php/process.php" method="post">

    <div data-role="controlgroup" data-type="horizontal" id="message" style="position:absolute;display:none;z-index:1;"/> 
        <input type="text" name="user" id="user" value="" autocomplete="off" style="width:185px;position:absolute;top:0px;text-align:center;font-weight:bolder;"/>
    </div>

    <div data-role="controlgroup" data-type="horizontal">
        <input type="password" name="key" id="key" pattern="[0-9]*" value="" autocomplete="off" style="width:185px;position:relative;"/>
    </div>

    <div class="ui-grid-b">

		<div class="ui-block-a"><input type="button" value="1" data-inline="true" class="num-key"/></div>
		<div class="ui-block-b"><input type="button" value="2" data-inline="true" class="num-key" /></div>
		<div class="ui-block-c"><input type="button" value="3" data-inline="true" class="num-key" /></div>

                <div class="ui-block-a"><input type="button" value="4" data-inline="true" class="num-key" /></div>
                <div class="ui-block-b"><input type="button" value="5" data-inline="true" class="num-key" /></div>
                <div class="ui-block-c"><input type="button" value="6" data-inline="true" class="num-key" /></div>

                <div class="ui-block-a"><input type="button" value="7" data-inline="true" class="num-key" /></div>
                <div class="ui-block-b"><input type="button" value="8" data-inline="true" class="num-key" /></div>
                <div class="ui-block-c"><input type="button" value="9" data-inline="true" class="num-key" /></div>

	        <div class="ui-block-a"><input type="reset" value="reset" data-inline="true" data-icon="refresh" data-iconpos="notext" /></div>
	        <div class="ui-block-b"><input type="button" value="0" data-inline="true" class="num-key" /></div>
                <div class="ui-block-c"><input type="submit" value="send" data-inline="true" data-icon="check"  data-iconpos="notext" /></div>

    </div>
</form>
<a href="static/downloads/app-release.apk" data-ajax="false">Download Android App</a>
</div>

  </div>
</div>

</body>
</html>
