Ext.define('GayCanada.model.boards.Topics', {
	extend: 'Ext.data.Model',

	fields: [
	    { name: 'boardid', 		mapping: 'BoardID' },
    	{ name: 'topicid', 		mapping: 'DiscussionID' },
    	{ name: 'subject', 		mapping: 'Subject' },
    	{ name: 'author', 		mapping: 'Created_By' },
    	{ name: 'postings', 	mapping: 'Messages' },
    	{ name: 'topictype', 	mapping: 'Posting_Privileges' },
    	{ name: 'lastmessage', 	mapping: 'Last_Message' },
    	{ name: 'lastposter', 	mapping: 'Last_Poster' },
    	{ name: 'status', 		mapping: 'Locked' }
	]
})