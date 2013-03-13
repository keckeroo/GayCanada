Ext.define('GayCanada.store.accounts.Messages', {
	extend: 'Ext.data.Store',
	model: 'GayCanada.model.accounts.Messages',

	proxy: {
		type: 'ajax',
		method: 'POST',
        url: '/api/icaGetMessages.php',

		reader: {
			type: 'json',
			root: 'records',
	        totalProperty: 'totalCount'
		}
	}
});