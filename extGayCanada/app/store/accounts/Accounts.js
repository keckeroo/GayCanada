Ext.define('GayCanada.store.accounts.Accounts', {
	extend: 'Ext.data.Store',
	model: 'GayCanada.model.accounts.Accounts',

	proxy: {
		type: 'ajax',
        url: '/api/icaAccountInfo.php',

		reader: {
			type: 'json',
			root: 'records',
	        totalProperty: 'totalCount'
		}
	}
})