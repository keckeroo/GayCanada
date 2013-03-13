Ext.define('GayCanada.store.directory.AUS', {
	extend: 'Ext.data.Store',
	model: 'GayCanada.model.directory.AUS',

    proxy: {
    	type: 'ajax',
   		url: '/ica/aus.CRUD.php',

       	reader: {
           	totalProperty: 'totalCount',
	    	root: 'records',
          	type: 'json'
       }
    }
});