Ext.define('GayCanada.model.boards.Boards', {
	extend: 'Ext.data.Model',

	fields: [
	    { name: 'boardid',     mapping: 'BoardID' },   
    	{ name: 'boardname',   mapping: 'BoardName' },  
    	{ name: 'boardtype',   mapping: 'Posting_Privileges' },
    	{ name: 'category',    mapping: 'Category' },   
    	{ name: 'description', mapping: 'Heading' },
    	{ name: 'topics',      mapping: 'Discussions' },
    	{ name: 'postings',    mapping: 'Messages' },
    	{ name: 'lastpost',    mapping: 'Last_Message' },
    	{ name: 'moderator',   mapping: 'Post_Master' }
	]
});