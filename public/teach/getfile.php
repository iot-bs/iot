<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/5/14 0014
 * Time: 16:06
 */
$wsdlurl = 'http://qw.knowle.cn/common/webService/service.php?wsdl';  //webService地址
$wsdl=new SoapClient($wsdlurl);
$siteKey ='qw.knowle.cn';
$sitePwd = 'e10adc3949ba59abbe56e057f20f883e';
$userName = 'test';
$userDisplayName = 'test';
$meetingTopic = 'qw';
$meetingPwd = 'roadforhacker';
$option = '{"meetingDuration":3600}';

//创建会议
$result = $wsdl->getMeetingRecordFileState($siteKey,$sitePwd,'526431553');
print_r($result);

?>
