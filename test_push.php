<html>
<head>
    <title>AndroidHive | Firebase Cloud Messaging</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="//www.gstatic.com/mobilesdk/160503_mobilesdk/logo/favicon.ico">
    <link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.6.0/pure-min.css">

    <style type="text/css">
        body{
        }
        div.container{
            width: 1000px;
            margin: 0 auto;
            position: relative;
        }
        legend{
            font-size: 30px;
            color: #555;
        }
        .btn_send{
            background: #00bcd4;
        }
        label{
            margin:10px 0px !important;
        }
        textarea{
            resize: none !important;
        }
        .fl_window{
            width: 400px;
            position: absolute;
            right: 0;
            top:100px;
        }
        pre, code {
            padding:10px 0px;
            box-sizing:border-box;
            -moz-box-sizing:border-box;
            webkit-box-sizing:border-box;
            display:block;
            white-space: pre-wrap;
            white-space: -moz-pre-wrap;
            white-space: -pre-wrap;
            white-space: -o-pre-wrap;
            word-wrap: break-word;
            width:100%; overflow-x:auto;
        }

    </style>
</head>
<body>
<?php
// Enabling error reporting
error_reporting(-1);
ini_set('display_errors', 'On');

require_once __DIR__ . '/firebase_notification.php';
require_once __DIR__ . '/push_notification.php';

$firebase = new firebase_notification();
$push = new push_notification();

// optional payload
$payload = array();
$payload['goto'] = 'noplace';

//topic
$topic = isset($_GET['topic']) ? $_GET['topic'] : '';

// notification title
$title = isset($_GET['title']) ? $_GET['title'] : '';

// notification message
$message = isset($_GET['message']) ? $_GET['message'] : '';

// push type - single user / topic
$push_type = isset($_GET['push_type']) ? $_GET['push_type'] : '';

// whether to include to image or not
$include_image = isset($_GET['include_image']) ? TRUE : FALSE;

$push->setTopic($topic);
$push->setTitle($title);
$push->setMessage($message);
if ($include_image) {
    $push->setImage('https://api.androidhive.info/images/minion.jpg');
} else {
    $push->setImage('');
}
$push->setIsBackground(FALSE);
$push->setPayload($payload);


$json = '';
$response = '';

if ($push_type == 'topic') {
    $json = $push->getPush();
    $response = $firebase->sendToTopic($push->getTopic(), $json);
} else if ($push_type == 'individual') {
    $json = $push->getPush();
    $regId = isset($_GET['regId']) ? $_GET['regId'] : '';
    $response = $firebase->send($regId, $json);
}
?>
<div class="container">
    <div class="fl_window">
        <div><img src="https://api.androidhive.info/images/firebase_logo.png" width="200" alt="Firebase"/></div>
        <br/>
        <?php if ($json != '') { ?>
            <label><b>Request:</b></label>
            <div class="json_preview">
                <pre><?php echo json_encode($json) ?></pre>
            </div>
        <?php } ?>
        <br/>
        <?php if ($response != '') { ?>
            <label><b>Response:</b></label>
            <div class="json_preview">
                <pre><?php echo json_encode($response) ?></pre>
            </div>
        <?php } ?>

    </div>

    <form class="pure-form pure-form-stacked" method="get">
        <fieldset>
            <legend>Send to Single Device</legend>

            <label for="redId">Firebase Reg Id</label>
            <input type="text" id="redId" name="regId" class="pure-input-1-2" placeholder="Enter firebase registration id">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" class="pure-input-1-2" placeholder="Enter title">

            <label for="message">Message</label>
            <textarea class="pure-input-1-2" rows="5" name="message" id="message" placeholder="Notification message!"></textarea>

            <label for="include_image" class="pure-checkbox">
                <input name="include_image" id="include_image" type="checkbox"> Include image
            </label>
            <input type="hidden" name="push_type" value="individual"/>
            <button type="submit" class="pure-button pure-button-primary btn_send">Send</button>
        </fieldset>
    </form>
    <br/><br/><br/><br/>

    <form class="pure-form pure-form-stacked" method="get">
        <fieldset>
            <legend>Send to Topic </legend>
            <label for="Topic">Topic</label>
            <input type="text" id="topic" name="topic" class="pure-input-1-2" placeholder="Enter topic"
            <label for="title1">Title</label>
            <input type="text" id="title1" name="title" class="pure-input-1-2" placeholder="Enter title">

            <label for="message1">Message</label>
            <textarea class="pure-input-1-2" name="message" id="message1" rows="5" placeholder="Notification message!"></textarea>

            <label for="include_image1" class="pure-checkbox">
                <input id="include_image1" name="include_image" type="checkbox"> Include image
            </label>
            <input type="hidden" name="push_type" value="topic"/>
            <button type="submit" class="pure-button pure-button-primary btn_send">Send to Topic</button>
        </fieldset>
    </form>
</div>
</body>
</html>