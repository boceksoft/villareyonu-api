<?php

# create request class
$request = new \Iyzipay\Request\RetrieveInstallmentInfoRequest();
$request->setLocale(\Iyzipay\Model\Locale::TR);
$request->setConversationId("123456789");
$request->setBinNumber(post("binNumber"));
$request->setPrice(post("price"));

# make request
$installmentInfo = \Iyzipay\Model\InstallmentInfo::retrieve($request, IyzipayBootstrap::options());
echo $installmentInfo->getRawResult();
# print result
