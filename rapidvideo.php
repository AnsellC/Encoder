<?php
require './vendor/autoload.php';
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$target_url = 'http://toys.playercdn.net/upload.rapidvideo.com/upload/index.php';

echo "Uploading to Rapidvideo: ". basename($argv[1]);



function do_post_request($url, $postdata, $files = null) 
{ 
    $data = ""; 
    $boundary = "---------------------".substr(md5(rand(0,32000)), 0, 10); 
    //Collect Postdata 
    foreach($postdata as $key => $val) 
    { 
        $data .= "--$boundary\n"; 
        $data .= "Content-Disposition: form-data; name=\"".$key."\"\n\n".$val."\n"; 
    } 
    $data .= "--$boundary\n"; 
    //Collect Filedata 
    foreach($files as $key => $file) 
    { 
        $fileContents = file_get_contents($file); 

        $data .= "Content-Disposition: form-data; name=\"{$key}\"; filename=\"".basename($file)."\"\n"; 
        $data .= "Content-Type: video/mp4\n"; 
        $data .= "Content-Transfer-Encoding: binary\n\n"; 
        $data .= $fileContents."\n"; 
        $data .= "--$boundary--\n"; 
    } 

    $params = array('http' => array( 
           'method' => 'POST', 
           'header' => 'Content-Type: multipart/form-data; boundary='.$boundary, 
           'content' => $data 
        )); 

   $ctx = stream_context_create($params); 
   $fp = fopen($url, 'rb', false, $ctx); 

   if (!$fp) { 
      throw new Exception("Problem with $url, $php_errormsg"); 
   } 

   $response = @stream_get_contents($fp); 
   if ($response === false) { 
      throw new Exception("Problem reading data from $url, $php_errormsg"); 
   } 
   return $response; 
} 

//set data (in this example from post) 

//sample data 
$postdata = array( 
    'user_id' => '132852',
); 

//sample image 
$files['files[]'] = $argv[1];

$response = do_post_request($target_url, $postdata, $files); 


$log = fopen("logs/". basename($argv[1]) .".txt", 'a+');

if (!empty($response) ) {

    $json = json_decode($response, true);
    fwrite($log, $json["files"][0]["url"] . "\r\n");

} else {

    fwrite($log, 'RAPIDVIDEO FAIL' . "\r\n");

}

fclose($log);

exit;











$client = new GuzzleHttp\Client();

$response = $client->request('POST', $target_url, [
    'expect' => false,
    'debug' => true,
    'multipart' => [
        [
            'name'     => 'user_id',
            'contents' => '132852'
        ],
        [
            'name'     => 'files[]',
            'contents' => fopen($argv[1], 'r')
        ]
    ]
]);

echo var_dump($response);
fgets(STDIN);

$file_name_with_full_path = realpath($argv[1]);

if (function_exists('curl_file_create'))
{ // php 5.5+
  $cFile = curl_file_create($file_name_with_full_path);
} else { // 
  $cFile = '@' . realpath($file_name_with_full_path);
}

var_dump($cFile);
$post = array('page_id' => '2', 'user_id' => '132852','files[]' => curl_file_create($argv[1]));
$ch = curl_init();
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_URL, $target_url);
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_REFERER, 'https://www.rapidvideo.com/?c=upload');
curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Expect:', 'Accept: application/json, text/javascript, */*; q=0.01']);
$result=curl_exec($ch);


$json = json_decode($result, true);
if($json["files"][0]["url"])
{
}
print_r($json);
echo "DONE";