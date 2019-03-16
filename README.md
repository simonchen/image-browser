## Simple Image Browser

A single PHP file and less then 300 lines of code of very simple image browser. It is intended to run with PHP Built-in web server but you can also use real web server such as Apache.

I develop this script because I need to quickly access pictures in my laptop using my phone so I can browse all the pictures via Wi-Fi and then download it.

## Requirements

You only need PHP binary to run this app.

## How to Install

The easiest is clone the project from github.

```
$ git clone git@github.com:rioastamal/image-browser.git
```

## How to Run

Make sure you have valid directory with images inside. We will make the images directory as the document root.

First go to the application directory. We will use our `index.php` also as the router script.

```
$ cd image-browser
$ php -S 0.0.0.0:8080 -t /path/to/your/images/dir ./index.php
```

Now open your browser and point to http://localhost:8080/.

## Generating Thumbnails

This step is **optional** but it will increase the performance especially if your have a lot of images and each image is few MBs in size.

You need ImageMagick installed on your system. We will use `mogrify` command to create thumbnail in batch.

```
$ cd /path/to/your/images/dir
$ mkdir .thumbs
$ mogrify -format jpg -path .thumbs/ -thumbnail 400x400 *.JPG
```

You can tune the parameter based on your preference. The name `.thumbs` is mandatory because this script will try to look that directory when searching for thumbnail.

## Author

This script is written by Rio Astamal \<rio@rioastamal.net>

## License

This script is open source licensed under [MIT license](http://opensource.org/licenses/MIT).