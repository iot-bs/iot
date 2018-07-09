count=`lsof -i:8090|grep CLOSE_WAIT|wc -l`
echo $count
if [ $count -gt 10 ]; then
ps -eaf |grep "CenterServer" | grep -v "grep"| awk '{print $2}'|xargs kill -9
sleep 2
ulimit -c unlimited
php /website/iot/centerserver/center.php start -d -h 0.0.0.0 -p 8090
echo "onclose is core dump !now is reset the worker-----" >> /website/iot/daemon/server.log;
echo $count >> /website/iot/daemon/server.log;
echo $(date +%Y-%m-%d_%H:%M:%S) >> /website/iot/daemon/server.log;
fi



