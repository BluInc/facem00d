<?php

/**
 * This sample app is provided to kickstart your experience using Facebook's
 * resources for developers.  This sample app provides examples of several
 * key concepts, including authentication, the Graph API, and FQL (Facebook
 * Query Language). Please visit the docs at 'developers.facebook.com/docs'
 * to learn more about the resources available to you
 */

// Provides access to app specific values such as your app id and app secret.
// Defined in 'AppInfo.php'
require_once('AppInfo.php');

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
  header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit();
}

// This provides access to helper functions defined in 'utils.php'
require_once('utils.php');


/*****************************************************************************
 *
 * The content below provides examples of how to fetch Facebook data using the
 * Graph API and FQL.  It uses the helper functions defined in 'utils.php' to
 * do so.  You should change this section so that it prepares all of the
 * information that you want to display to the user.
 *
 ****************************************************************************/

require_once('sdk/src/facebook.php');

$facebook = new Facebook(array(
  'appId'  => AppInfo::appID(),
  'secret' => AppInfo::appSecret(),
  'sharedSession' => true,
  'trustForwarded' => true,
));

$user_id = $facebook->getUser();
if ($user_id) {
  try {
    // Fetch the viewer's basic information
    $basic = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    // If the call fails we check if we still have a user. The user will be
    // cleared if the error is because of an invalid accesstoken
    if (!$facebook->getUser()) {
      header('Location: '. AppInfo::getUrl($_SERVER['REQUEST_URI']));
      exit();
    }
  }

 /* if(file_exists('./tmp/last_post.data')) {
    $last_post = unserialize(file_get_contents('./tmp/last_post.data'));
  } else {
    file_put_contents('./tmp/last_post.data', serialize(value));
  }
*/

  if(file_exists('./tmp/fb_home.data')) {
    $home = unserialize(file_get_contents('./tmp/fb_home.data'));
    if(file_exists('./tmp/last_post.data')) {
      $last_post = unserialize(file_get_contents('./tmp/last_post.data'));
      if(idx($home[0],'id') != $last_post) {
        $home = idx($facebook->api('/me/home?limit=100'), 'data', array());
        file_put_contents('./tmp/last_post.data', serialize(idx($home[0]),'id'));
      }
    } else {
      $home = idx($facebook->api('/me/home?limit=100'), 'data', array());
      file_put_contents('./tmp/last_post.data', serialize(idx($home[0]),'id'));
    }
  }

  if (!$home) { // cache doesn't exist or is older than 10 mins
    $home = idx($facebook->api('/me/home?limit=100'), 'data', array());
    file_put_contents('tmp/fb_home.data', serialize($home));
  }

      //$home = idx($facebook->api('/me/home?limit=100'), 'data', array());

      //echo $home[0];

  // Here is an example of a FQL call that fetches all of your friends that are
  // using this app
  /*$app_using_friends = $facebook->api(array(
    'method' => 'fql.query',
    'query' => 'SELECT uid, name FROM user WHERE uid IN(SELECT uid2 FROM friend WHERE uid1 = me()) AND is_app_user = 1'
  ));*/
}
// Fetch the basic info of the app that they are using
$app_info = $facebook->api('/'. AppInfo::appID());

$app_name = idx($app_info, 'name', '');


function assignFriend($status){
  $daturl = "https://api.sentigem.com/external/get-sentiment?api-key=75bb2830195e0ef2af7714e30bd337df7D-3dzCLGWprRax85XusgTYAJwVH1Bb0&text=";
  
  $total = $daturl . urlencode($status);
  
  $string = get_data($total);
  $json_a = json_decode($string,true);
  
  $sentiment = $json_a['polarity'];

  return $sentiment;
}

/* gets the data from a URL */
function get_data($url) {
  $ch = curl_init();
  $timeout = 5;
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  curl_setopt ($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}



?>
<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#" lang="en">
  <head>


    <script src="js/vendor/custom.modernizr.js"></script>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes" />

    <title>faceMood</title>
    <!--<link rel="stylesheet" href="stylesheets/screen.css" media="Screen" type="text/css" />
    <link rel="stylesheet" href="stylesheets/mobile.css" media="handheld, only screen and (max-width: 480px), only screen and (max-device-width: 480px)" type="text/css" />
    -->
    <link rel="stylesheet" href="css/foundation.css">
    <link rel="stylesheet" href="css/normalize.css">
    
    <style>
      div .splash { background-color: #0069D6; }
    </style>

    <!--[if IEMobile]>
    <link rel="stylesheet" href="mobile.css" media="screen" type="text/css"  />
    <![endif]-->

    <!-- These are Open Graph tags.  They add meta data to your  -->
    <!-- site that facebook uses when your content is shared     -->
    <!-- over facebook.  You should fill these tags in with      -->
    <!-- your data.  To learn more about Open Graph, visit       -->
    <!-- 'https://developers.facebook.com/docs/opengraph/'       -->
    <meta property="og:title" content="faceMood" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo AppInfo::getUrl(); ?>" />
    <meta property="og:image" content="<?php echo AppInfo::getUrl('/logo.png'); ?>" />
    <meta property="og:site_name" content="<?php echo he($app_name); ?>" />
    <meta property="og:description" content="My first app" />
    <meta property="fb:app_id" content="<?php echo AppInfo::appID(); ?>" />

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script type="text/javascript" src="/ui1/ui/jquery.ui.button.js"></script>
    

    

    <!--[if IE]>
      <script type="text/javascript">
        var tags = ['header', 'section'];
        while(tags.length)
          document.createElement(tags.pop());
      </script>
    <![endif]-->

  </head>
  <body style="background-color:#391256;">

    <div id="fb-root"></div>
    <script type="text/javascript">
      window.fbAsyncInit = function() {
        FB.init({
          appId      : '<?php echo AppInfo::appID(); ?>', // App ID
          channelUrl : '//<?php echo $_SERVER["HTTP_HOST"]; ?>/channel.html', // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          xfbml      : true // parse XFBML
        });

        // Listen to the auth.login which will be called when the user logs in
        // using the Login button
        FB.Event.subscribe('auth.login', function(response) {
          // We want to reload the page now so PHP can read the cookie that the
          // Javascript SDK sat. But we don't want to use
          // window.location.reload() because if this is in a canvas there was a
          // post made to this page and a reload will trigger a message to the
          // user asking if they want to send data again.
          window.location = window.location;
        });

        FB.Canvas.setAutoGrow();
      };

      // Load the SDK Asynchronously
      (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
	  
	  
	  
    </script>
    
      <?php if (isset($basic)) { ?>
    <div class="fixed"> 
      <nav class="top-bar">
        <ul class="title-area">
           <!-- Title Area -->
           <li class="name">
             <img style="margin-top:10;" src="logo.png" alt="FaceMood" height="25%" width="25%">
           </li>
        </ul>

        <section class="top-bar-section">
          <!-- Right Nav Section -->
          <ul style="margin-right:10px;" class="right">
            <li>
              <a onclick="fbLogout()" class="button success" href="#" >Logout</a>
            </li>
          </ul>
        </section>
      </nav>
     </div>
      
      <?php } else { ?>
  <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css">

    <style type="text/css">
/* Sticky footer styles
      -------------------------------------------------- */
      html,
      body {
        height: 100%;
        /* The html and body elements cannot have any padding or margin. */
      }

      /* Wrapper for page content to push down footer */
      #wrap {
        min-height: 100%;
        height: auto !important;
        height: 100%;
        /* Negative indent footer by it's height */
        margin: 0 auto -60px;
      }

      /* Set the fixed height of the footer here */
      #push,
      #footer {
        height: 60px;
      }
      #footer {
        background-color: #f5f5f5;
      }

      /* Lastly, apply responsive CSS fixes as necessary */
      @media (max-width: 767px) {
        #footer {
          margin-left: -20px;
          margin-right: -20px;
          padding-left: 20px;
          padding-right: 20px;
        }
      }



      /* Custom page CSS
      -------------------------------------------------- */
      /* Not required for template or sticky footer method. */

      .container {
        width: auto;
        max-width: 680px;
      }
      .container .credit {
        margin: 20px 0;
      }

      /* ------------------------------------------*/    
      .progress-bar {
          /*margin-top: 10px;*/
          width: 400px;
          display:none;
      }
      .popbox {
    display: none;
    position: absolute;
    z-index: 99999;
    width: 360px;
    padding: 10px;
    background: #EEEFEB;
    color: #000000;
    border: 1px solid #4D4F53;
    margin: 0px;
    -webkit-box-shadow: 0px 0px 5px 0px rgba(164, 164, 164, 1);
    box-shadow: 0px 0px 5px 0px rgba(164, 164, 164, 1);
}
.popbox h2
{
    background-color: #4D4F53;
    color:  #E3E5DD;
    font-size: 14px;
    display: block;
    width: 355;
    margin: -29px 0px 0px -9px;
    padding: 5px 10px;
}
    </style>
    <script>
    function loading(){
      $(".fb-login-button").fadeOut();
      $(".progress-bar").fadeIn();
      

    var progress = setInterval(function() {
    var $bar = $('.bar');

    if ($bar.width()>=400) {
        clearInterval(progress);
        $('.progress').removeClass('active');
    } else {
        $bar.width($bar.width()+40);
    }
}, 1);}
    $(function() {
    var moveLeft = 0;
    var moveDown = 0;
    $('a.popper').hover(function(e) {
   
        var target = '#' + ($(this).attr('data-popbox'));
         
        $(target).show();
        moveLeft = $(this).outerWidth();
        moveDown = ($(target).outerHeight() / 2);
    }, function() {
        var target = '#' + ($(this).attr('data-popbox'));
        $(target).hide();
    });
 
    $('a.popper').mousemove(function(e) {
        var target = '#' + ($(this).attr('data-popbox'));
         
        leftD = e.pageX + parseInt(moveLeft);
        maxRight = leftD + $(target).outerWidth();
        windowLeft = $(window).width() - 40;
        windowRight = 0;
        maxLeft = e.pageX - (parseInt(moveLeft) + $(target).outerWidth() + 20);
         
        if(maxRight > windowLeft && maxLeft > windowRight)
        {
            leftD = maxLeft;
        }
     
        topD = e.pageY - parseInt(moveDown);
        maxBottom = parseInt(e.pageY + parseInt(moveDown) + 20);
        windowBottom = parseInt(parseInt($(document).scrollTop()) + parseInt($(window).height()));
        maxTop = topD;
        windowTop = parseInt($(document).scrollTop());
        if(maxBottom > windowBottom)
        {
            topD = windowBottom - $(target).outerHeight() - 20;
        } else if(maxTop < windowTop){
            topD = windowTop + 20;
        }
     
        $(target).css('top', topD).css('left', leftD);
     
     
    });
 
});
    </script>
    <body style="background-color:#391256;">
      <div id="wrap">
      <div>
      <nav class="top-bar">
        <ul class="title-area">
           <!-- Title Area -->
           <li class="name">
             <img style="margin-top:10;" src="logo.png" alt="FaceMood" height="25%" width="25%">
           </li>
        </ul>
      </nav>
     </div>
     
      <div>
      <header id="welcome" style="background-color:#391256;">
        <div align="center">
          <br/><br/><br/>
          <img id="frontpage" src="logo.png" alt="FaceMood"><br/><br/>
          <div style="margin: 0 auto;" class="fb-login-button" size="xlarge" onlogin="location.reload();loading();" data-scope="user_likes,user_photos,read_stream,publish_actions"></div>
          <div class="progress-bar">
            <p style="font-family:georgia;color:lightgray;">Performing Sentiment Analysis...</p>
              <div class="progress progress-striped active">
                  <div class="bar" style="width: 0%;"></div>
              </div>
          </div><br/><br/><br/><br/>
          <div class="fb-like" data-href="https://facem00d.herokuapp.com/" data-width="250" data-colorscheme="dark" data-show-faces="true" data-send="true"></div>
        </div>

      </header>
      </div>
      <div id="pop1" class="popbox">
          <h2>About</h2>
          <p>FaceMood stands at the frontier of sentiment analysis.
        After you log in, we run our proprietary software to categorize your friends' status messages by how they're feeling.
        The results are quite magical. Like, Log In, Love.</p>
      </div>
      <div id="pop2" class="popbox">
          <h2>Us</h2>
          <p>Built by Kevin Mangan, Willy Vasquez, and Bowen Lu. All rights reserved.</p>
      </div>
      <div id="push"></div>
    </div>
    
      <div id="footer">
        <div class="container">
          <p class="muted credit">FaceMood 2013 · 
            <a href="#" class="popper" data-popbox="pop1">About</a> · 
            <a href="#" class="popper" data-popbox="pop2">Us</a></p>
        </div>
      </div>

      <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
<script src="bootstrap/js/bootstrap.js"></script>
    </body>
      <?php } ?>
      


   <?php
      if ($user_id) {
    ?>
  <script>
    function fbLogout() {
        FB.logout(function (response) {
            //Do what ever you want here when logged out like reloading the page
            window.location.reload();
        });
    }

    function appendNode(datResult, returnHTML) {
      $('#'+datResult+' .friends').append(returnHTML);
    }
	
	function submitComment(id) {
    alert('submitting comment');
		url = "https://graph.facebook.com/" + id + "/comments";
		content = $form.find('input[id="' + id + '"]').val();
		var posting = $.post(url, {"message":  content} );
		posting.done(function(data) {
			document.write(data);
		});
	}
  </script><style type="text/css">
       
    /* Start of Column CSS */
    #container3 {
      margin-top: 45px;
      clear:left;
      float:left;
      width:100%;
      overflow:hidden;
      background:#89ffa2; /* column 3 background colour */
    }
    #container2 {
      clear:left;
      float:left;
      width:100%;
      position:relative;
      right:33.333%;
      background:#fff689; /* column 2 background colour */
    }
    #container1 {
      float:left;
      width:100%;
      position:relative;
      right:33.33%;
      background:#ffa7a7; /* column 1 background colour */
    }
    #negative {
      float:left;
      width:29.33%;
      position:relative;
      left:68.67%;
      overflow:hidden;
    }
    #neutral {
      float:left;
      width:29.33%;
      position:relative;
      left:72.67%;
      overflow:hidden;
    }
    #positive {
      float:left;
      width:29.33%;
      position:relative;
      left:76.67%;
      overflow:hidden;
    }
  </style>        
  <div id="container3">
  <div id="container2">   
  <div class="container" id="container1">  
    <div class="small-2 large-4 columns" id="negative" >
          <br>
          <h3 style="font-family:georgia; text-align:center;">Cheer them up!</h3>
          <br>
          <div class="friends">
           
          </div>
         
    </div>
    
    <div class="small-4 large-4 columns" id="neutral" >
          <br>
          <h3 style="font-family:georgia; text-align:center;">Keep calm and carry on.</h3>
          <br>
          <div class="friends">
              
            </div>
    </div>
    
    <div class="small-6 large-4 columns" id="positive" >
            <br>
            <h3 style="font-family:georgia; text-align:center;">Join in the fun!</h3>
            <br>
            <div class="friends">
      
            </div>

    </div>
  </div> </div></div>
    

  <!-- Logic for Sentiment Analysis and jQuery Sorting -->
  <?php
  
    foreach ($home as $status) {
		// Extract the pieces of info we need from the requests above
		$message = idx($status, 'message');
		if( (idx($status, 'to') == null) &&//filter out posts to others walls
			(strlen($message) > 6) &&       //filter out short messages
				((!stristr(he($message), "happy birthday")) || (!stristr(he($message), "happy bday"))) ){ //filter out birthdays
					  $from = idx($status, 'from');
					  if(idx($from, 'category') == null) { //filter out Facebook Pages
						$id = idx($from, 'id');
						$name = idx($from, 'name');
						
						if(idx($status, 'link') == null){
							$post_id = idx($status, 'id');

							$returnHTML = '<div id="post' . he($post_id) . '" class="panel"><a href="https://www.facebook.com/' . he($id) . '" target="_top"><img src="https://graph.facebook.com/' . he($id) . '/picture?type=square" alt=" ' . he($name) . '">&nbsp;&nbsp;<strong>' .  he($name) . '</strong></a><br><br>' . he($message) . '<hr> <div class="row collapse"> <input id="' . he($post_id) . '" type="text" placeholder="Comment on their mood...">  <a href="#" onclick="submitComment(\'' . he($post_id) . '\')" class="button prefix">Post</a></div> </div>';
									  

						}else{
							$post_id = idx($status, 'id');
							$url = idx($status, 'link');                     

							$returnHTML = '<div class="panel"><a href="https://www.facebook.com/' . he($id) . '" target="_top"><img src="https://graph.facebook.com/' . he($id) . '/picture?type=square" alt=" ' . he($name) . '">&nbsp;&nbsp;<strong>' . he($name) . '</strong></a><br><br><a href="' . he($url) . '" target="_blank">' . he($message) . '</a><hr> <div class="row collapse"> <form> <input name="message" type="text" id="' . he($post_id) . '" placeholder="Comment on their mood..."> <input type="submit" value="Post" class="button prefix">  </div></div>';


						}
						
						$datResult = assignFriend($message); ?>

            <script>
              appendNode('<?php echo $datResult; ?>','<?php echo $returnHTML; ?>');
            </script>

            <?php
						
						
					}
				}
			}

  ?>

  <script>
  document.write('<script src=js/vendor/' +
  ('__proto__' in {} ? 'zepto' : 'jquery') +
  '.js><\/script>')
  </script>


 <!--  <script src="js/foundation.min.js"></script>
  <script>
    $(document).foundation();
  </script> -->
  <!-- End Footer -->

  <?php }?>



  </body>
</html>
