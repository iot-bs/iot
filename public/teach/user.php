<?php
$url = 'http://chat.knowle.cn/flash/TBMeetingFlaClient.php?
ts=1477019875&d=199109292&m=1477019875n19150&t=cXFx&j=join&n=1&p=123456&meetingStartTime=
1477019700&lessonDurationTime=2700&Teachinfo=laosh&r=xuesheng&isSynchronization=1&receive
DataType=0&needJoinSystem=0&isCDN=1&IMUrl=chat.knowle.cn&mp=trunk.knowle.cn';
//时间c戳
$ts = time();
//课堂id
$d = '339502435';
//课堂主题;
$t = base64_encode('qw');
//加会类型
$j = 'join';
//是否需要密码
$n = '1';
//用户名
$m = 'xiaodo';
//课堂密码 创建直播的密码
$p = 'roadforhacker';
//课堂开始时间
$meetingStartTime = time();
//课堂持续时间
$lessonDurationTime = '2700';
//教师名称
$Teachinfo = 'xiaodo';
//用户显示名
$r = 'xiaodo';
//是否强制同步
$isSynchronization = '1';
//是否cdn模式
$isCDN = '1';
//im地址
$IMUrl = 'chat.knowle.cn';
$mp = 'qw.knowle.cn';
$needJoinSystem = '0';
$receiveDataType = '0';
$url = 'http://chat.knowle.cn/flash/TBMeetingFlaClient.php?ts='.$ts.'&d='.$d.'&m='.$m.'&t='.$t.'&j='.$j.'&n'.$n.'&p='.$p.'&meetingStartTime='.$meetingStartTime.'&lessonDurationTime='.$lessonDurationTime.'&Teachinfo='.$Teachinfo.'&r='.$r.'&isSynchronization='.$isSynchronization.'&receiveDataType='.$receiveDataType.'&needJoinSystem='.$needJoinSystem.'&isCDN='.$isCDN.'&IMUrl='.$IMUrl.'&mp='.$mp;
$redis = new Redis();
$redis->connect('127.0.0.1',6379);
$res = $redis->get('meetingid');
print_r($res);

print_r($url);

