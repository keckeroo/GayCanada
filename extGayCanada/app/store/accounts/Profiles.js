Ext.define('GayCanada.store.accounts.Profiles', {
	extend: 'Ext.data.Store',
	model: 'GayCanada.model.accounts.Profiles',

	proxy: {
		type: 'ajax',
        url: '/api/icaProfileInfo.php',

		reader: {
			type: 'json',
			root: 'records',
	        totalProperty: 'totalCount'
		}
	}	
})