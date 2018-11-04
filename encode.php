<?php
include './vendor/autoload.php';
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$binPath = getenv('bin_path');
$watermarkPath = getenv('watermark');

if( !isset($argv[1]) OR !isset($argv[2]) ) {

    $source = getenv('source');
    $dest =  getenv('dest');

} else {
    $source = $argv[1];
    $dest = $argv[2];
}

echo $source;
$adapter = new Local($source);
$filesystem = new Filesystem($adapter);

$animes = $filesystem->listContents('./');

echo "Found ". count($animes). " anime...\n";


$i = 0;
$total_videos = 0;
foreach($animes AS $anime) {

    echo "Processing: \033[0;32m".$anime['path']."\033[0m\n";
    $files = $filesystem->listContents($anime['path']);
    $animes[$i]['videos'] = $files;
    $total_videos += count($files);

    $s = 'ffmpeg -i "'. $source .'/'. $files[0]['path'] .'" 2>&1 &';

    unset($out);
    exec($s, $out);
    foreach($out AS $line) {
       if (preg_match('/Stream #0/', $line))
        echo $line ."\n";
    }

    echo "Dub encode?(y/n) [default n]: ";
    $dub = strtolower(trim(fgets(STDIN)));

    if($dub == 'y') {

        $animes[$i]['encode_path'] = $anime['path'] .' DUB';

    } else {
        $animes[$i]['encode_path'] = $anime['path'];
    }

    echo "Manual or Auto?(a/m) [default m]: ";
    $choice = trim(fgets(STDIN));

    if ($choice != strtolower('a')) {
        echo "SELECT VIDEO: ";
        $animes[$i]['video_stream'] = strtolower(trim(fgets(STDIN)));

        echo "SELECT AUDIO: ";
        $animes[$i]['audio_stream'] = strtolower(trim(fgets(STDIN)));

        echo "Burn subs?(y/n) [default y]: ";
        $animes[$i]['subs'] = strtolower(trim(fgets(STDIN)));



    } else {
        //if auto add subs
        $anime[$i]['subs'] = 'y';
    }

    /*if($animes[$i]['subs'] == 'y') {
        echo "Subtitle Type?(ass/dvd) [default ass]: ";
    }*/

    echo "\n\n\n";
    
    $i++;
}

flush();
$i = 1;
$t = 1;
foreach($animes AS $anime) {

    $x = 1;


    if(!file_exists($dest . '/'. $anime['encode_path'])) {

        mkdir($dest . '/'. $anime['encode_path']);

    }
    
    foreach($anime['videos'] AS $video) {

        $video_path = $source .'/'. $video['path'];
        $out_path = $dest .'/'. $anime['encode_path'].'/'. $video['basename'] .'.mp4';

        if ( file_exists($out_path) ) {

            echo "SKIPPING (file exists): \033[0;32m".$video['basename']."\033[0m\n";
            continue;
        }

        echo "ENCODING: \033[0;32m".$video['basename']."\033[0m {$t} of {$total_videos}\n";

        $cmd = 'ffmpeg -hide_banner -loglevel warning -i "'. $video_path.'"';

        if( isset($anime['video_stream']) AND isset($anime['audio_stream']) ) {

            $cmd .= " -map 0:{$anime['video_stream']} -map 0:{$anime['audio_stream']}";

        }
        $cmd .= ' -c:v libx264 -preset faster -tune animation -crf 23 -profile:v high -level 4.1 -pix_fmt yuv420p -c:a aac -b:a 192k';


        if (isset($anime['subs']) AND $anime['subs'] == 'y') {

            $cmd .= ' -vf "ass=\''.str_replace(":", "\:", $watermarkPath).'\', subtitles=\''.str_replace(":", "\:", $video_path).'\'"';

        } else {
            
            $cmd .= ' -vf "ass=\''.str_replace(":", "\:", $watermarkPath).'\'"';
        }
        
        $cmd.= ' "'.$out_path.'"';
      
        $x++;
        $t++;
        exec($cmd);

    }
   
    

   $i++;
}