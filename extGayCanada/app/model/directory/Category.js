Ext.define('GayCanada.model.directory.Category', {
    extend: 'Ext.data.Model',

    fields: [
       { name: 'categoryid', mapping: 'CategoryID' },
       { name: 'shortname', mapping: 'Category_Name' },
       { name: 'description', mapping: 'Description_E' }
    ]
});