#!/usr/bin/env bash


apt-get update
apt-get install -y python-pip python-m2crypto

locale-gen en_US.UTF-8

export LC_ALL=C
wget https://bootstrap.pypa.io/get-pip.py
python ./get-pip.py
hash -r
pip install shadowsocks

if [ ! -d "/etc/shadowsocks" ];then
mkdir /etc/shadowsocks
fi


block="
{
 \"server\":\"ip\",
 \"local_address\":\"127.0.0.1\",
 \"local_port\":1080,
  \"port_password\":{
        \"6660\":\"jc91715\",
        \"6661\":\"jc91715\",
        \"6662\":\"jc91715\",
        \"6663\":\"jc91715\",
        \"6664\":\"jc91715\"


 },
 \"timeout\":600,
 \"method\":\"aes-256-cfb\",
 \"fast_open\": false
}
"
if [ ! -f "/etc/shadowsocks/config.json" ];then
touch /etc/shadowsocks/config.json
fi

echo $block > "/etc/shadowsocks/config.json"
ssserver -c /etc/shadowsocks/config.json -d start
