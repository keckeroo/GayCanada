Ext.define('GayCanada.model.directory.Attributes', {
	extend: 'Ext.data.Model',

	fields: [
	    { name: 'categoryid', 	mapping: 'CategoryID' },
    	{ name: 'groupid', 		mapping: 'GroupID' },
    	{ name: 'sortorder', 	mapping: 'sortOrder' },
    	{ name: 'xtype', 		mapping: 'xtype' },
    	{ name: 'fieldLabel', 	mapping: 'fieldLabel' },
    	{ name: 'name', 		mapping: 'name' },
    	{ name: 'store', 		mapping: 'store' }
	]
});