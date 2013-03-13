Ext.define('GayCanada.model.boards.Posts', {
	extend: 'Ext.data.model',

	fields: [
		{ name: 'recordkey', 	mapping: 'MessageID' },
   		{ name: 'messageid', 	mapping: 'MessageID' },
   		{ name: 'topicid', 		mapping: 'DiscussionID' },
   		{ name: 'account', 		mapping: 'Account' },
   		{ name: 'userid', 		mapping: 'UserID' },
   		{ name: 'posting', 		mapping: 'Message' },
   		{ name: 'posted', 		mapping: 'Date_Created' },
   		{ name: 'status', 		mapping: 'SystemStatus' },
   		{ name: 'approvedby', 	mapping: 'ApprovedBy' },
   		{ name: 'approveddate', mapping: 'ApprovedDate' },
   		{ name: 'flaggedby', 	mapping: 'FlaggedBy' },
   		{ name: 'flaggeddate', 	mapping: 'FlaggedDate' }

	]
});