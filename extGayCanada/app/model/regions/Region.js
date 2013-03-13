Ext.define('GayCanada.model.regions.Region', {
	extend: 'Ext.data.Model',

	fields: [ 
		{ name: 'recordkey',  	mapping: 'recordkey' },
		{ name: 'regionid',   	mapping: 'Province_Code' },
		{ name: 'country',    	mapping: 'Country' },
		{ name: 'regionname', 	mapping: 'Name_English' },
		{ name: 'frenchname', 	mapping: 'Name_French' },
		{ name: 'population', 	mapping: 'Population' },
		{ name: 'capital',    	mapping: 'Capital' },
		{ name: 'motto',      	mapping: 'Motto' },
		{ name: 'flower',     	mapping: 'Flower' },
		{ name: 'description', 	mapping: 'Description' }, 
		{ name: 'area', 		mapping: 'Total_Area' },
		{ name: 'populationpercentage', mapping: 'Population_Percentage' },
		{ name: 'timezone', 	mapping: 'Time_Zone' },
		{ name: 'drinkingage', 	mapping: 'Drinking_Age' },
		{ name: 'salestax', 	mapping: 'Sales_Tax' },
		{ name: 'entries', 		mapping: 'Num_Entries' }
	]
});