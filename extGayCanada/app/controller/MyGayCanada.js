Ext.define('GayCanada.controller.MyGayCanada', {
	extend: 'Ext.app.Controller',

	refs: [{
		ref: 'cardpanel', selector: '#mygaycanada-cards'
	}],

	init: function() {
	   this.control({
		  '#myGayCanadaMenu' : {
			  menuitemclick: this.doMenuItemClick
		  }
	   });
	},

	doMenuItemClick: function(action, record, el, index, event) {
		var me = this,
			canvas = me.getCardpanel();

		canvas.getLayout().setActiveItem(record.raw.card);

//		alert('hi ' + action);
	}

});
