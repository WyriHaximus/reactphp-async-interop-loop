#!/bin/bash
set -e
set -o pipefail

if [[ "$TRAVIS_PHP_VERSION" != "hhvm" &&
      "$TRAVIS_PHP_VERSION" != "hhvm-nightly" ]]; then

    # install "libevent" (used by 'event' and 'libevent' PHP extensions)
    sudo apt-get install -y libevent-dev

    # install 'event' PHP extension
    echo "yes" | pecl install event

    # install 'libevent' PHP extension (does not support php 7)
#    if [[ "$TRAVIS_PHP_VERSION" != "7.0" ]]; then
#        curl http://pecl.php.net/get/libevent-0.1.0.tgz | tar -xz
#        pushd libevent-0.1.0
#        phpize
#       ./configure
#       make
#       make install
#       popd
#       echo "extension=libevent.so" >> "$(php -r 'echo php_ini_loaded_file();')"
#    fi
    if [ "$TRAVIS_PHP_VERSION" != "5.5" ] && [ "$TRAVIS_PHP_VERSION" != "5.6" ] ; then
        git clone https://github.com/libuv/libuv;
        pushd libuv;
        git checkout $(git describe --tags);
        ./autogen.sh;
        ./configure --prefix=$(dirname `pwd`)/libuv-install;
        make;
        make install;
        popd;
        git clone https://github.com/bwoebi/php-uv.git;
        pushd php-uv;
        phpize;
        ./configure --with-uv=$(dirname `pwd`)/libuv-install;
        make;
        make install;
        popd;
        echo "extension=uv.so" >> "$(php -r 'echo php_ini_loaded_file();')";
    fi;

    # install 'libev' PHP extension (does not support php 7)
    if [[ "$TRAVIS_PHP_VERSION" != "7.0" ]]; then
        git clone --recursive https://github.com/m4rw3r/php-libev
        pushd php-libev
        phpize
        ./configure --with-libev
        make
        make install
        popd
        echo "extension=libev.so" >> "$(php -r 'echo php_ini_loaded_file();')"
    fi

fi
