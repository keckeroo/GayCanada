Ext.define('GayCanada.model.system.Items', {
	extend: 'Ext.data.Model',

	fields: [
	    { name: 'sid', 			mapping: 'SID' },
    	{ name: 'catalogid', 	mapping: 'CatID' },
    	{ name: 'itemsku', 		mapping: 'ItemSKU' },
    	{ name: 'name', 		mapping: 'ItemName' },
    	{ name: 'price', 		mapping: 'UnitCost' },
    	{ name: 'discount', 	mapping: 'UnitDiscount' },
    	{ name: 'numdays', 		mapping: 'NumDays' },
    	{ name: 'servicetype', 	mapping: 'Service_Type' },
    	{ name: 'productshow', 	mapping: 'Product_Show' },
    	{ name: 'features', 	mapping: 'Features' },
    	{ name: 'enabled', 		mapping: 'Active' }
	]
});