<?php

require './vendor/autoload.php';
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;


$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();
$adapter = new Local(getenv('watch'));
$filesystem = new Filesystem($adapter);
$done = [];

while(1) {

    $anime = $filesystem->listContents('./');

    
    foreach($anime AS $video) {

        if (in_array($anime['basename'], $done))
            continue;

        echo "ENCODING: \033[0;32m".$video['basename']."\033[0m\n";

        $video_path = getenv('watch') . '/'. $video['path'];
        $out_path = getenv('watch_dest') .'/'. preg_replace('/\W+/', '_', $video['basename']) .'.mp4';
        $watermarkPath = getenv('watermark');

        $cmd = 'ffmpeg -i "'. $video_path.'" -c:v libx264 -preset faster -tune animation -crf 23 -profile:v high -level 4.1 -pix_fmt yuv420p -c:a aac -b:a 192k';
        $cmd .= ' -vf "ass=\''.str_replace(":", "\:", $watermarkPath).'\', subtitles=\''.str_replace(":", "\:", $video_path).'\'" "'.$out_path.'"';
        exec($cmd);


        echo "Uploading to video hosts\n";
        exec("./upload.bat ".$out_path." > /dev/null 2>&1 &");
        $done[] = $anime['basename'];
       /* $facebook_path = exec('php ./facebook.php '.$out_path);
        $rapidvideo_path = exec('php ./rapidvideo.php '.$out_path);

        //save log file
        $log = fopen("logs/{$video['basename']}.txt", 'a+');
        fwrite($log, $facebook_path ."\r\n");
        fwrite($log, $rapidvideo_path ."\r\n");
        fclose($log);
        unlink($video_path);*/

    }


    sleep(5);
}