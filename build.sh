#!/bin/sh
PRODUCT_NAME="iknow"
APP_NAME="copyright"
rm -rf output
mkdir -p output/app/$APP_NAME
mkdir -p output/conf/app
mkdir -p output/webroot/$APP_NAME
mkdir -p output/php/phplib/$PRODUCT_NAME/api/$APP_NAME

#拷贝代码文件
cp -r actions controllers library models script Bootstrap.php output/app/$APP_NAME

#拷贝配置文件
cp -r conf/$APP_NAME  output/conf/app/
#拷贝webroot index 文件
cp -r index.php  output/webroot/$APP_NAME

cd output
find ./ -name .svn -exec rm -rf {} \;
tar cvzf $APP_NAME.tar.gz app conf webroot php
rm -rf app conf webroot php