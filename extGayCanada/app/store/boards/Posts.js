Ext.define('GayCanada.store.boards.Posts', {
	extend: 'Ext.data.Store',
	model: 'GayCanada.model.boards.Posts',

	proxy: {
		type: 'ajax',
        url: '/ica/postings.CRUD.php',

		reader: {
			type: 'json',
			root: 'records',
        	totalProperty: 'totalCount'
		}
	}	
});