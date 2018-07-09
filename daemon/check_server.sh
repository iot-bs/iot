count=`ps -fe |grep "CenterServer" | grep -v "grep" | grep "master" | wc -l`
echo $count
if [ $count -lt 1 ]; then
ps -eaf |grep "CenterServer" | grep -v "grep"| awk '{print $2}'|xargs kill -9
sleep 2
ulimit -c unlimited
php /website/iot/centerserver/center.php start -d -h 0.0.0.0 -p 8090
echo "server is down now is prepare to restart" >> /website/iot/daemon/server.log;
echo $(date +%Y-%m-%d_%H:%M:%S) >> /website/iot/daemon/server.log;
fi



