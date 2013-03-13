Ext.define('GayCanada.store.system.ItemList', {
	extend: 'Ext.data.Store',
	model: 'GayCanada.model.system.Items',

	autoLoad: true,

	proxy: {
		type: 'ajax',
		url: '/ica/item.CRUD.php',

		reader: {
			root: 'apiRecords'
			totalProperty: 'totalCount',
		}
	}
});