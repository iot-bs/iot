#定时平滑测试主进程是否稳定
count=`ps -fe |grep "CenterServer" | grep -v "grep" | grep "master" | wc -l`
echo $count
if [ $count -eq 1 ]; then
ps -eaf |grep "CenterServer" | grep -v "grep"| grep "master"|awk '{print $2}'|xargs kill -USR1
echo "server is sercure check " >> /website/iot/daemon/server.log;
echo $(date +%Y-%m-%d_%H:%M:%S) >> /website/iot/daemon/server.log;
fi



