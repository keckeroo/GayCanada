Ext.define('GayCanada.store.accounts.Photos', {
	extend: 'Ext.data.Store',
	model: 'GayCanada.store.accounts.Photos',

	proxy: {
		type: 'ajax',
        url: '/api/icaGetPhotos.php',

		reader: {
			type: 'json',
			root: 'records',
	        totalProperty: 'totalCount'
		}
	}	
});