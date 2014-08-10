Google Voice PHP API
====================

An API to interact with Google Voice using PHP.

Currently the API can send and receive SMS messages and delete messages. Feel free to implement new functionality and send me your changes so I can incorporate them into this library!

getUnreadSMS returns an array of JSON objects. Each object has the following attributes, example
values included:

	$msg->id = c3716aa447a19c7e2e7347f443dd29091401ae13
	$msg->phoneNumber = +15555555555
	$msg->displayNumber = (555) 555-5555
	$msg->startTime = 1359918736555
	$msg->displayStartDateTime = 2/3/13 5:55 PM
	$msg->displayStartTime = 5:55 PM
	$msg->relativeStartTime = 5 hours ago
	$msg->note = 
	$msg->isRead = true
	$msg->isSpam = false
	$msg->isTrash = false
	$msg->star: = alse
	$msg->messageText = Hello, cellphone.
	$msg->labels = [sms,all]
	$msg->type = 11
	$msg->children = 

Disclaimer
==========

This code is provided for educational purposes only. This code may stop
working at any time if Google changes their login mechanism or their web
pages. You agree that by downloading this code you agree to use it solely
at your own risk.
