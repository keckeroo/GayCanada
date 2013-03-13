Ext.define('GayCanada.store.directory.Reviews', {
	extend: 'Ext.data.Store',
	model: 'GayCanada.model.directory.Review',

    proxy: {
       type: 'ajax',
       url: '/ica/reviewCRUD.php',     
       
       reader: {
           type: 'json',
           totalProperty: 'totalCount',   
           root: 'records'
       }
    }
});