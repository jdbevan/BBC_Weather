#!/bin/bash
BASE="/home/jon/BBC_Weather/"
DIRECTORY=`date "+%Y/%m/%d"`
TIMENOW=`date "+%H%M%S"`

# Create folder for today's files
if [ ! -d "$BASE$DIRECTORY" ]; then
	mkdir -p "$BASE$DIRECTORY"
fi

# Array of location IDS and the URL to get from
PLACES=(2634725 2641170 2648579 2654675 2643743 2653822 2655603 2641673 2639996)
URL="http://open.live.bbc.co.uk/weather/feeds/en/"
FILENAME="3dayforecast.rss"

# Download the rss files
for i in ${PLACES[@]}; do
	wget -nv -O "$BASE$DIRECTORY/${i}_${TIMENOW}_$FILENAME" "${URL}${i}/${FILENAME}" -a "$BASE$DIRECTORY/wget.log"
done
exit 0
