Ext.define('GayCanada.store.directory.EntryList', {
    extend: 'Ext.data.Store',
    model: 'GayCanada.model.directory.Entry',

    pageSize: 25,
//    autoLoad: true,

    proxy: {
       type: 'ajax',
       url: '/api/getEntries.php',
       reader: {
          root: 'apiRecords'
       }
    }
});