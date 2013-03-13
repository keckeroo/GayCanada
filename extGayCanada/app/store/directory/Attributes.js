Ext.define('GayCanada.store.directory.Attributes', {
	extend: 'Ext.data.Store',
	model: 'GayCanada.model.directory.Attributes',

    autoLoad: true,

    proxy: {
       type: 'ajax',
       url: '/ica/attribute.CRUD.php',

       reader: {
          root: 'apiRecords',
          type: 'json'
       }
    }	
});