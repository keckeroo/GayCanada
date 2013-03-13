Ext.define('GayCanada.view.mygaycanada.Main', {
	extend: 'Ext.Panel',
	alias: [ 'widget.mygaycanadaTab' ],

	requires: [ 
	  'GayCanada.view.MenuTree',
	  'GayCanada.view.mygaycanada.Console',
	  'GayCanada.view.mygaycanada.Account',
	  'GayCanada.view.mygaycanada.Password',
	  'GayCanada.view.mygaycanada.Profile',
	  'GayCanada.view.mygaycanada.Photos',
	  'GayCanada.view.mygaycanada.Travel',
	  'GayCanada.view.mygaycanada.Privacy'
	],

	title: 'My GayCanada',
	border: false,
	layout: 'border',

	items: [{
	   region: 'west',
	   width: 130,
	   id: 'myGayCanadaMenu',
	   xtype: 'menutree',
	   data: [{ 
		  text: "My Account", expanded: true, collapsible: false, 
		  items: [
			 { text: 'Console', 			action: 'showCard', card: 0, select: true},
			 { text: 'Account Info',		action: 'showCard', card: 1 },
			 { text: 'Password Settings', 	action: 'showCard', card: 2 },
			 { text: 'Photo Manager', 		action: 'showCard', card: 3 },
			 { text: 'Privacy Settings', 	action: 'showCard', card: 4 },
			 { text: 'Profile Details', 	action: 'showCard', card: 5 },
			 { text: 'Travel Plans', 		action: 'showCard', card: 6 }
		  ] 
	   }, {
		  text: "My Mail", 
		  items: [
			 { text: 'Inbox' },
			 { text: 'Sent' },
			 { text: 'Trash' },
			 { text: 'Settings' }
		  ]
	   }, {
		  text: "My Profile",
		  items: [
			 { text: 'Interests' },
			 { text: 'Joint Profile' }
		  ]
	   }, {
		  text: "My Content", 
		  items: [
			 { text: 'Events' },
			 { text: 'Board Posts' },
			 { text: 'Reviews' },
			 { text: 'SiteSwaps'}
		  ]
	   }, {
		  text: "My Services" ,
		  items: [
			 { text: 'Upgrade Account' },
			 { text: 'Billing History' },
			 { text: 'Change Primary Id' },
			 { text: 'Delete Account' },
			 { text: 'Help Centre'}
		  ]
	   }]
	}, {
	   	border: false,
	   	region: 'center',
	   	itemId: 'mygaycanada-cards',
	   	xtype: 'panel',
	   	layout: 'card',
	   	defaults: {
		   	border: false
		},
	   	items: [{
	   		xtype: 'mygaycanada-console'
	   	}, {
			xtype: 'mygaycanada-account'
		}, {
			xtype: 'mygaycanada-password'
		}, {
	    	xtype: 'mygaycanada-photos'
	   	}, {
			xtype: 'mygaycanada-privacy'
	    }, {
	    	xtype: 'mygaycanada-profile'
	    }, {
	   		xtype: 'mygaycanada-travel'	
	   	}]
	}]    

});
