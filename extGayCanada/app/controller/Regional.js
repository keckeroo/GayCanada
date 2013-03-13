Ext.define('GayCanada.controller.Regional', {
	extend: 'Ext.app.Controller',

	stores: [ 
		'regions.Cities' 
	],

	refs: [
		{ ref: 'canvas', selector: '#gcRegionalCanvas' },
		{ ref: 'combo', selector: '#gcRegionalCanvas toolbar combobox' }
	],

	init: function() {
		this.control({
		  	'#regionalMenu' : {
				menuitemclick: this.doMenuItemClick
			}
	   	});
	},

	doMenuItemClick: function(action, record, el, index, event) {
		var regionCode = record.raw.value,
			regionId = record.raw.regionid;

	  	this.setRegion(regionCode, regionId);
	},

	setRegion: function(regionCode, regionId) {
		var me = this,
			canvas = me.getCanvas(),
			store = me.getRegionsCitiesStore(),
			combo = me.getCombo();

 		canvas.getEl().setStyle({ 'background-image': 'url(/images/regional/' + regionCode + '.jpg)' });
 		canvas.getLayout().setActiveItem(0);
 		combo.reset();
 		combo.bindStore(store);	
 		console.log("Trying to load cities for " + regionCode);
 		store.load({
 			params: {
 				searchfield: 'province',
	 			searchvalue: regionCode,
	 			limit: 1000
	 		},
 			callback: function(records, operation, success) {
 				console.log(records.length);
 			},
 			scope: me
 		})
 		console.log("Regionid is " + regionId); 
	}

});
