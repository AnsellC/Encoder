<?php
require './vendor/autoload.php';
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$fb = new \Facebook\Facebook([
    'app_id' => getenv('FACEBOOK_APP_ID'),
    'app_secret' => getenv('FACEBOOK_APP_SECRET'),
    'default_graph_version' => 'v3.1',
]);

$start = time();
$data = [
    'title' => 'funny video '. $start,
    'source' => $fb->videoToUpload($argv[1]),
    'published' => false,
];

try {

    $response = $fb->post('/'. getenv('FACEBOOK_PAGE_ID') .'/videos', $data, getenv('FACEBOOK_ACCESS_TOKEN'));

} catch(Facebook\Exceptions\FacebookResponseException $e) {

    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {

    echo 'Facebook SDK returned an error: ' . $e->getMessage()." --- retrying\n";
    exit;
}

echo 'https://www.facebook.com/'. getenv('FACEBOOK_PAGE_ID') .'/videos/'. json_decode($response->getBody())->id . "/";