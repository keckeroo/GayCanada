Ext.define('GayCanada.store.boards.Boards', {
	extend: 'Ext.data.Store',
	model: 'GayCanada.model.boards.Boards',

	autoLoad: true,

	sortInfo: { field: 'category', direction: 'ASC' },

	groupField: 'category',   

	proxy: {
		type: 'ajax',
		url: '/api/boards.CRUD.php',

		reader: {
			type: 'json',
			root: 'records'
		}
	}
})