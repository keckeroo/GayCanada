Ext.define('GayCanada.store.regions.RegionFilter', {
    extend: 'Ext.data.Store', 
    model: 'GayCanada.model.regions.Region',

    autoLoad: true,

    proxy: {
       type: 'ajax',
       url: '/api/getRegions.php',
       reader: {
           type: 'json',
           root: 'apiRecords'
       }
    }
});