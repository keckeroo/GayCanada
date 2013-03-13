Ext.Loader.setConfig({ 
	enabled: true,
	paths: {
	   'Ext.ux': 'ux'
	}
});

Ext.application({
	name: 	'GayCanada',

	requires: [
		'Ext.layout.container.Border',
		'Ext.grid.Panel',
		'Ext.grid.column.Template',
		'Ext.grid.feature.Grouping',
		'Ext.data.reader.Json',
		'Ext.tab.Panel',
		'Ext.form.Label',
		'Ext.toolbar.Spacer',
		'Ext.form.FieldSet',
		'Ext.form.field.ComboBox',
		'Ext.form.field.Date',
		'Ext.window.MessageBox'
	],

	models: [ 
		'boards.Boards',
		'boards.Topics',
		'directory.Entry', 
		'directory.Category', 
		'regions.Region', 
		'regions.City' 
	],

	stores: [ 
		'boards.Boards',
		'boards.Topics',
		'directory.EntryList', 
		'directory.CategoryList', 
		'regions.RegionFilter', 
		'regions.CityFilter' 
	],

	controllers: [ 
		'Main', 'Login', 'Directory', 'Regional', 'MyGayCanada' 
	],

//	views: [ 
//		'Main' 
//	],

    autoCreateViewport: true,

    launch: function() {
    	console.log("Launch");

    }
});
