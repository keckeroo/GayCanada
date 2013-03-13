Ext.define("GayCanada.view.mygaycanada.Account", {
	extend: "Ext.Panel",
	xtype: "mygaycanada-account",

	title: 'My Account',

	items: [{
		xtype: 'fieldset',
	    autoHeight: true,
    	border: true,
    	style: 'padding: 2px; margin: 0px;',
    	bodyStyle: 'padding: 5px;',
    	defaults: {
       		xtype: 'textfield',
       		msgTarget: 'side'
    	},
    	items: [
       	//	{ xtype: 'panel', style: 'padding-bottom: 10px;', bodyStyle: 'border: solid 1px #8C8C8C; background-color: #CCFF99; padding: 2px; margin: 0px;', border: false, 
       	//		items: new Ext.DataView({
 	    //       	store: Ext.user.accountStore,
        //    	tpl: Ext.mygaycanada.header,
        //    	autoHeight: true,
        //    	itemSelector: 'div.null'
        // 		})
      	//	}, 
      { fieldLabel: 'Firstname', name: 'firstname', width: 300 },
      { fieldLabel: 'Lastname', name: 'lastname', width: 300 },
      { xtype: 'combo', fieldLabel: 'Sex', name: 'sex', fieldWidth: 80, allowBlank: false, blankText: 'Sex field is required',
        store: ['Male', 'Female'], editable: false, triggerAction: 'all', queryMode: 'local' },
      { fieldLabel: 'Birthdate', name: 'birthdate', xtype: 'datefield', allowBlank: false },
      { fieldLabel: 'Primary Email', name: 'email', allowBlank: false, width: 300 },
      { fieldLabel: 'Secondary Email', name: 'alternateemail', width: 300 },
      { xtype: 'panel', style: 'padding: 10px;',  border: false, html: [
           '<div style="border: dashed 1px; padding: 5px; background-color: #FFFFE1;"><span class="bodytext">If you are changing your location temporarily, please go into ',
           '<a href="/GC_mygaycanada/accounts.php?set_vacation=1&page=2:1:8">Set Vacation</a> ',
           'to properly place your profile in the appropriate city. <a href="/GC_mygaycanada/accounts.php?set_vacation=1&page=2:1:8">Set Vacation</a> ',
           'has a start and end date and keeps your primary location in your profile.</span></div>'
        ] 
      },
      { fieldLabel: 'Address', name: 'address', width: 350 },
      { fieldLabel: 'Postal Code', name: 'postalcode', width: 200 },
      { fieldLabel: 'Area/District', name: 'community', width: 200 }
   ]
	}]

})