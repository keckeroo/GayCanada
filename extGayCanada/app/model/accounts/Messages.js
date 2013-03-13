Ext.define('GayCanada.model.accounts.Messages', {
	extend: 'Ext.data.Model',

	fields: [
	    { name: 'mid', 		mapping: 'MID' },
    	{ name: 'recordkey', mapping: 'MID' },
    	{ name: 'status', 	mapping: 'Status' },
    	{ name: 'from', 	mapping: 'FromUser' },
    	{ name: 'subject', 	mapping: 'Subject' },
    	{ name: 'date', 	mapping: 'Date' },
    	{ name: 'folder', 	mapping: 'Mailbox' },
    	{ name: 'body', 	mapping: 'Message', convert: function(v, r) { return v.replace(/\n/g, '<br>'); } }
	]
});