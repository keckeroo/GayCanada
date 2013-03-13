Ext.define('GayCanada.store.regions.Cities', {
    extend: 'Ext.data.Store', 
    model: 'GayCanada.model.regions.City',

    autoLoad: false,

    proxy: {
       type: 'ajax',
       url: '/api/getCities.php',
       
       reader: {
           type: 'json',
           root: 'records'
       }
    }
});