<?php
$wsdlurl = 'http://qw.knowle.cn/common/webService/service.php?wsdl';  //webService地址
$wsdl=new SoapClient($wsdlurl);
$siteKey ='qw.knowle.cn';
$sitePwd = 'e10adc3949ba59abbe56e057f20f883e';
$userName = 'test';
$userDisplayName = 'test';
$meetingTopic = 'qw';
$meetingPwd = 'roadforhacker';
$scheduledAllowStartTime = strtotime('now');
$scheduledStartTime = strtotime('+1 hour');
$scheduledEndTime = strtotime('+2 hour');
$scheduledAlertTime = strtotime('+3 hour');
$scheduledForceEndTime = strtotime('+3 hour');
$option = [
    'scheduledAllowStartTime' => $scheduledAllowStartTime,
    'scheduledStartTime' => $scheduledStartTime,
    'scheduledEndTime' => $scheduledEndTime,
    'scheduledAlertTime' => $scheduledAlertTime,
    'scheduledForceEndTime' => $scheduledForceEndTime
];
var_dump(date('Y-m-d H:i:s',$scheduledAllowStartTime));
var_dump(date('Y-m-d H:i:s',$scheduledStartTime));
var_dump(date('Y-m-d H:i:s',$scheduledEndTime));
var_dump(date('Y-m-d H:i:s',$scheduledAlertTime));
var_dump(date('Y-m-d H:i:s',$scheduledForceEndTime));

$option = json_encode($option);
//创建会议
$result = $wsdl->createConference($siteKey,$sitePwd,$userName,$userDisplayName,$meetingTopic,$meetingPwd,$option);
$object = simplexml_load_string($result);

$meetingId = $object->meetingId;
$fd = fopen('id.txt','w');
fwrite($fd,$meetingId);
fclose($fd);
$redis = new Redis();
$redis->connect('127.0.0.1',6379);
$redis->set('meetingid',$meetingId);
$joinOption = '{"isUrl":1}';
//加入会议
$result = $wsdl->joinConference($siteKey,$sitePwd,$meetingId,$meetingPwd,$userName,$userDisplayName,$joinOption);
$object = simplexml_load_string($result);
echo "<script>window.location.href='".$object->activeXUrl."'</script>";    //加会地址。
?>
