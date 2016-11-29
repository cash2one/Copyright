#!/bin/bash
awk '{ if (NF > 1 && ($(NF-1) > 0.55 || $(NF) ~ /非视频/)) {
	print $0, "\t", 0;
	}
else if (NF > 1 && ($(NF) ~ /电影|影视|视频|电视剧/ || $3 ~ /电影|影视|视频|电视剧|剧集/ )) {
	print $0, "\t", 1;
	}
else if (NF > 1 && $(NF) !~ /游戏|动漫|音乐|文学|小说|非视频/) {
	print $0, "\t", 1;
	}
else if (NF > 1 && $(NF-1) < 0.000000664) {
	print $0, "\t", 1;
	}
else print $0, "\t", 0;
}' FS="\t" OFS="\t" $1
