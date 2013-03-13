Ext.define('GayCanada.store.directory.Cities', {
    extend: 'Ext.data.Store', 
    model: 'GayCanada.model.regional.City',

    autoLoad: false,

    proxy: {
       type: 'ajax',
       url: '/api/geoCities.php',

       reader: {
           type: 'json',
           root: 'apiRecords'
       }
    }
});