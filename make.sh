#!/usr/bin/env bash
rm lightweight-youtube-channel-widget.zip
rm -r build
mkdir build
cp --parents youtube-channel.php languages/*.{mo,po,pot} inc/widget.php \
  assets/*/* readme.txt build
cd build
zip -r ../lightweight-youtube-channel-widget.zip *
cd ..
