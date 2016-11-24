#!/bin/sh
PRODUCT_NAME="iknow"
APP_NAME="copyright"
rm -rf output
mkdir -p output/app/$APP_NAME
mkdir -p output/conf/app/$APP_NAME
mkdir -p output/webroot/$APP_NAME

#phplib相关，但是目前copyright还没有涉及到
#mkdir -p output/php/phplib/$PRODUCT_NAME/api/$APP_NAME

#data里面相关的样板文件
mkdir -p output/data/app/$APP_NAME/FullTask/sample
cp -r data/app/$APP_NAME/FullTask/sample/* output/data/app/$APP_NAME/FullTask/sample

#拷贝后端代码文件
cp -r actions controllers library models script Bootstrap.php output/app/$APP_NAME

#拷贝global配置文件
cp conf/global.conf  output/conf/app/$APP_NAME/global.conf

cp -r index.php  output/webroot/$APP_NAME

#没有api及phplib文件，注释掉
#cp -r api/* output/php/phplib/$PRODUCT_NAME/api/$APP_NAME

cd output
find ./ -name .svn -exec rm -rf {} \;
tar cvzf $APP_NAME.tar.gz app conf webroot data

rm -rf app conf webroot data php