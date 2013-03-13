Ext.define('GayCanada.store.directory.CategoryList', {
    extend: 'Ext.data.Store',
    model: 'GayCanada.model.directory.Category',

    autoLoad: true,

    proxy: {
       type: 'ajax',
       url: '/api/getCategories.php',
       reader: {
          root: 'apiRecords',
          type: 'json'
       }
    }
});