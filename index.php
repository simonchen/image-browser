<?php
/**
 * Simple image browser
 *
 * @author Rio Astamal <rio@rioastamal.net>
 * @link https://github.com/rioastamal/image-browser
 * 
 * Appending the parameterized path to the source of the <img> // by simonchen [2023-3-28]
 */
use SplFileInfo as FileInfo;

class ImageBrowser
{
    /**
     * @var string
     */
    protected $docroot = null;

    /**
     * @var string
     */
    protected $css = <<<STYLE
html {
    height: 100%;
}
* {
    padding: 0;
    margin: 0;
}
body {
    font-family: "Arial", "Serif";
    width: 100%;
    color: #444;
    position: relative;
    min-height: 100%;
    box-sizing: border-box;
}
h1 {
    border-bottom: 1px solid #f1f1f1;
    margin-bottom: 0.5em;
    font-size: 1.5em;
    padding-left: 0.3em;
}
.image-wrapper {
    width: 100%;
    box-sizing: border-box;
    padding: 0 0.5em;
    margin-bottom: 0.2em;
}
img {
    float: left;
    margin: 5px;
}
.image-wrapper a {
    text-decoration:none;
}
.caption {
    font-size: 0.5em;
}
STYLE;

    /**
     * @var string
     */
    protected $html = <<<BODY
<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">
<meta name="google" content="notranslate">
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
{{STYLE}}
</style>
</head>

<body>
<h1>Image Browser</h1>
{{IMG_HTML}}
</body>
</html>
BODY;

    /**
     * @var string
     */
    protected $imgComponent = <<<IMG
<div class="image-wrapper">
    <a href="{{IMG_SRC}}"><img src="{{IMG_SRC_THUMB}}" alt="{{IMG_NAME}}"></a>
</div>
IMG;

    /**
     * @param string $docroot Path to directory where your images reside
     * @return void
     */
    public function __construct($docroot)
    {
        $this->docroot = $docroot;
    }

    /**
     * Static call to create object
     *
     * @param string $docroot docroot to directory where your images reside
     * @return RioAstamal\ImageBrowser\ImageBrowser
     */
    public static function create($docroot)
    {
        return new static($docroot);
    }

    /**
     * Produce image browser
     *
     * @return void
     */
    public function run()
    {
        $imgHtml = $this->generateImagesHtml($this->getPath());

        $html = $this->html;
        $html = str_replace('{{STYLE}}', $this->css, $html);
        $html = str_replace('{{IMG_HTML}}', $imgHtml, $html);

        echo $html;
    }

    /**
     * @param string $path Path to image directory
     * @return string
     */
    public function generateImagesHtml($path)
    {
        $extensions = ['png', 'jpg', 'gif', 'jpeg'];
        $html = '';
        $files = scandir($path);
	$cnt = 0;
        $limit = intval($_GET['limit']);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' ) {
                continue;
            }

            $fileInfo = new FileInfo($path . '/' . $file);
            $fileExtension = $fileInfo->getExtension();
            if (! in_array(strtolower($fileExtension), $extensions)) {
                continue;
            }

	    if (!empty($_GET['path'])){
                $basename = $_GET['path'] . '/' . $fileInfo->getBasename();
	    }else{
		$basename = $fileInfo->getBasename();
	    }
            $size = round($fileInfo->getSize() / 1024, 1);
            $sizeCaption = sprintf('Size: %skb', $size);
            $thumbImg = $basename . '?thumb';

            $html .= str_replace(
                ['{{IMG_SRC}}', '{{IMG_SRC_THUMB}}', '{{IMG_NAME}}'],
                [$basename, $thumbImg, $basename . ' - ' . $sizeCaption],
                $this->imgComponent
            );
	    
	    $cnt += 1;
            if ($limit > 0 && $cnt >= $limit){
                break;
        }

        if ($html === '') {
            $html = '<p>There is no image found, Please try to append parameter - path in URL (e.g, ?path=/dp/photo/twt/01)</p>';
        }

        return $html;
    }

    /**
     * @return string
     */
    protected function getPath()
    {
        if (!isset($_GET['path'])) {
            return $this->docroot;
        }

        if (empty($_GET['path'])) {
            return $this->docroot;
        }

        // sanitize
        $path = str_replace('..', '', $_GET['path']);
        $path = rtrim($path, '/');
        $path = $this->docroot . '/' . $path;

        return $path;
    }
}

// Router script
// -------------
// This router script is responsible to show the thumbnail file.
// If the image is loaded with ?thumb query string it will try
// to load from .thumbs/FILE_NAME.EXT
//
// If you use real web server e.g: Apache then you need to define your
// own rewrite rule in Apache conf or .htaccess file.
$path = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
if (PHP_SAPI === 'cli-server') {

    if ($_SERVER['SCRIPT_NAME'] === '/') {
        // Run the app to prevent 404
        $browser = new ImageBrowser($path);
        $browser->run();
        return;
    }

    $_SERVER['PHP_SELF'] = '/' . basename(__FILE__);
    if ($_SERVER['PHP_SELF'] !== $_SERVER['SCRIPT_NAME']) {
        $extensions = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'jpeg' => 'image/jpeg'
        ];
        $fileInfo = new FileInfo($_SERVER['SCRIPT_FILENAME']);
        $fileExtension = $fileInfo->getExtension();
        if (in_array(strtolower($fileExtension), array_keys($extensions))) {
            $imgfile = $path . $_SERVER['SCRIPT_NAME'];

            // Try to use thumbnail file if we have ?thumb in query string
            // We load from .thumbs/ directory
            if (isset($_GET['thumb'])) {
                $thumbFile = $path . '/.thumbs' . $_SERVER['SCRIPT_NAME'];

                if (file_exists($thumbFile)) {
                    $imgfile = $thumbFile;
                }
            }

            header('Content-Type: ' . $extensions[strtolower($fileExtension)]);
            header('Content-Length: ' . filesize($imgfile));
            readfile($imgfile);

            return;
        }

        return false;
    }
}

// Run the App
$browser = new ImageBrowser($path);
$browser->run();
