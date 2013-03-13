Ext.define('GayCanada.model.directory.Review', {
	extend: 'Ext.data.Model',

	fields: [
	    { name: 'recordkey', 	mapping: 'recordkey' },
    	{ name: 'entryid', 		mapping: 'EntryID' },
    	{ name: 'account', 		mapping: 'Account' },
    	{ name: 'userid', 		mapping: 'UserID' },
    	{ name: 'entered', 		mapping: 'Date_Entered' },
    	{ name: 'title', 		mapping: 'Title' },
    	{ name: 'comments', 	mapping: 'Comments' },
    	{ name: 'rating', 		mapping: 'Rating' },
    	{ name: 'status', 		mapping: 'SystemStatus' },
    	{ name: 'approvedby', 	mapping: 'ApprovedBy' },
    	{ name: 'approveddate', mapping: 'ApprovedDate' }
	]
});