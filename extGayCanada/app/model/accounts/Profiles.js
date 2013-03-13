Ext.define('GayCanada.model.accounts.Profiles', {
	extend: 'Ext.data.Model',

	fields: [
	    { name: 'account', 		mapping: 'Account' },
   		{ name: 'height', 		mapping: 'Height' },  
    	{ name: 'weight', 		mapping: 'Weight' },  
    	{ name: 'haircolour', 	mapping: 'HairColour' },
    	{ name: 'eyecolour', 	mapping: 'EyeColour' },  
    	{ name: 'orientation', 	mapping: 'Orientation' },
    	{ name: 'smoke', 		mapping: 'Smoke' },
    	{ name: 'drink', 		mapping: 'Drink' },
    	{ name: 'maritalstatus', mapping: 'MaritalStatus' },
    	{ name: 'ethnicity', 	mapping: 'Ethnicity' },
    	{ name: 'bodytype', 	mapping: 'BodyType' } 
	]
});