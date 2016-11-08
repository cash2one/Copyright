#!/bin/sh
PRODUCT_NAME="iknow"
APP_NAME="copyright"
rm -rf output
mkdir -p output/app/$APP_NAME
mkdir -p output/conf/app
mkdir -p output/webroot/$APP_NAME
mkdir -p output/php/phplib/$PRODUCT_NAME/api/$APP_NAME
cp -r actions controllers library models script Bootstrap.php output/app/$APP_NAME
cp -r conf/*  output/conf/app
cp -r index.php  output/webroot/$APP_NAME
cp -r api/* output/php/phplib/$PRODUCT_NAME/api/$APP_NAME
cd output
find ./ -name .svn -exec rm -rf {} \;
tar cvzf copyright.tar.gz app conf webroot php
rm -rf app conf webroot php