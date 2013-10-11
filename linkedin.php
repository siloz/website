<?php
	require_once('utils.php');
	require_once('config.php');
	$conn = mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);	
	mysql_select_db(DB_NAME, $conn);
	$db = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

$user_id = param_get('user_id');
$redirect = param_get('redirect');
$id = param_get('id');

$user_siloz = mysql_fetch_row(mysql_query("SELECT id FROM users WHERE user_id = '$user_id'"));

// Change these
define('API_KEY',      'c3y1y2dujkrd'                                          );
define('API_SECRET',   'ou5NsMTgEoMbothN'                                       );
define('REDIRECT_URI', ACTIVE_URL.'linkedin.php?user_id='.$user_id.'&redirect='.$redirect.'&'.$id);
define('SCOPE',        'r_basicprofile r_contactinfo'                        );
 
// You'll probably use a database
session_name('linkedin');
session_start();

// OAuth 2 Control Flow
if (isset($_GET['error'])) {
    // LinkedIn returned an error
    print $_GET['error'] . ': ' . $_GET['error_description'];
    exit;
} elseif (isset($_GET['code'])) {
    // User authorized your application
    if ($_SESSION['state'] == $_GET['state']) {
        // Get token so you can make API calls
        getAccessToken();
    } else {
        // CSRF attack? Or did you mix up your states?
        exit;
    }
} else {
    if ((empty($_SESSION['expires_at'])) || (time() > $_SESSION['expires_at'])) {
        // Token has expired, clear the state
        $_SESSION = array();
    }
}

$user = fetch('GET', '/v1/people/~:(id,firstName,lastName)');

if (!$user) {
       // Start authorization process
	getAuthorizationCode();
}

// Congratulations! You have a valid token. Now fetch your profile

$user = fetch('GET', '/v1/people/~:(id,firstName,lastName)');
$email = fetch('GET', '/v1/people/~/email-address');
$fname = $user->firstName;
$lname = $user->lastName;
$adr = urlencode(fetch('GET', '/v1/people/~/main-address'));

$photo_id = $user_siloz[0].".jpg";
$img_url = fetch('GET', '/v1/people/~/picture-url');
$img = 'uploads/members/'.$photo_id;
file_put_contents($img, file_get_contents($img_url));

	$json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=".$adr."&sensor=false");
	$loc = json_decode($json);

	if ($loc->status == 'OK') {
    		foreach ($loc->results[0]->address_components as $address) {
        		if (in_array("locality", $address->types)) {
            			$city = $address->long_name;
        		}
        		if (in_array("administrative_area_level_1", $address->types)) {
            			$state = $address->short_name;
        		}
        		if (in_array("postal_code", $address->types)) {
            			$zip_code = $address->short_name;
        		}
    		}

		$address = $loc->results[0]->formatted_address;
		$lat = $loc->results[0]->geometry->location->lat;
		$long = $loc->results[0]->geometry->location->lng;
	}

mysql_query("UPDATE users SET fname = '$fname', lname = '$lname', photo_file = '$photo_id' WHERE user_id = '$user_id'");

if ($zip_code != '') {
	mysql_query("UPDATE users SET address = '$address', city = '$city', state = '$state', zip_code = '$zip_code', latitude = '$lat', longitude = '$long' WHERE user_id = '$user_id'");
}

print "$user_id <br> $redirect <br> $user->id <br> $user->firstName <br> $user->lastName <br> $email <br> $link <br> <img src='$photo'> .";

if (!$redirect) { $redirect = "my_account"; }

header("Location: ".ACTIVE_URL."index.php?task=".$redirect."&connect=linkedin");
exit;
 
function getAuthorizationCode() {
    $params = array('response_type' => 'code',
                    'client_id' => API_KEY,
                    'scope' => SCOPE,
                    'state' => uniqid('', true), // unique long string
                    'redirect_uri' => REDIRECT_URI,
              );
 
    // Authentication request
    $url = 'https://www.linkedin.com/uas/oauth2/authorization?' . http_build_query($params);
     
    // Needed to identify request when it returns to us
    $_SESSION['state'] = $params['state'];
 
    // Redirect user to authenticate
    header("Location: $url");
    exit;
}
     
function getAccessToken() {
    $params = array('grant_type' => 'authorization_code',
                    'client_id' => API_KEY,
                    'client_secret' => API_SECRET,
                    'code' => $_GET['code'],
                    'redirect_uri' => REDIRECT_URI,
              );
     
    // Access Token request
    $url = 'https://www.linkedin.com/uas/oauth2/accessToken?' . http_build_query($params);
     
    // Tell streams to make a POST request
    $context = stream_context_create(
                    array('http' =>
                        array('method' => 'POST',
                        )
                    )
                );
 
    // Retrieve access token information
    $response = file_get_contents($url, false, $context);
 
    // Native PHP object, please
    $token = json_decode($response);
 
    // Store access token and expiration time
    $_SESSION['access_token'] = $token->access_token; // guard this!
    $_SESSION['expires_in']   = $token->expires_in; // relative time (in seconds)
    $_SESSION['expires_at']   = time() + $_SESSION['expires_in']; // absolute time
     
    return true;
}
 
function fetch($method, $resource, $body = '') {
    $params = array('oauth2_access_token' => $_SESSION['access_token'],
                    'format' => 'json',
              );
     
    // Need to use HTTPS
    $url = 'https://api.linkedin.com' . $resource . '?' . http_build_query($params);
    // Tell streams to make a (GET, POST, PUT, or DELETE) request
    $context = stream_context_create(
                    array('http' =>
                        array('method' => $method,
                        )
                    )
                );
 
 
    // Hocus Pocus
    $response = file_get_contents($url, false, $context);
 
    // Native PHP object, please
    return json_decode($response);
}
?>