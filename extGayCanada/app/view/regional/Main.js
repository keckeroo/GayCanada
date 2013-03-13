Ext.define('GayCanada.view.regional.Main', {
    extend: 'Ext.Panel',
    alias: [ 'widget.regionalTab' ],

    requires: [ 'GayCanada.view.MenuTree' ],

    stores: [ 'RegionalCities' ],

    title: 'Regional',
    border: false,
    layout: 'border',

    items: [{
       region: 'west',
       width: 130,
       itemId: 'regionalMenu',
       xtype: 'menutree',
       data: [{  
           text: 'Regional Guides', expanded: true, collapsible: false,
           items: [
              { text: 'Alberta', action: 'setProvince', value: 'AB', regionid: "36"},
              { text: 'British Columbia', action: 'setProvince', value: 'BC', regionid: "43" },
              { text: 'Manitoba', action: 'setProvince', value: 'MB', regionid: "38" },
              { text: 'New Brunswick', action: 'setProvince', value: 'NB', regionid: "42" },
              { text: 'Nfld/Labrador', action: 'setProvince', value: 'NL', regionid: "41" },
              { text: 'Northwest Territories', action: 'setProvince', value: 'NT', regionid: "46" },
              { text: 'Nova Scotia', action: 'setProvince', value: 'NS', regionid: "39" },
              { text: 'Nunavut', action: 'setProvince', value: 'NU', regionid: "4996" },
              { text: 'Ontario', action: 'setProvince', value: 'ON', regionid: "37" },
              { text: 'PEI', action: 'setProvince', value: 'PE', regionid: "45" },
              { text: 'Quebec', action: 'setProvince', value: 'QC', regionid: "35" },
              { text: 'Saskatchewan', action: 'setProvince', value: 'SK', regionid: "40" },
              { text: 'Yukon', action: 'setProvince', value: 'YK', regionid: "815" } 
          ]
       }]   
    }, { 
       region: 'center',
       border: false,   
       itemId: 'gcRegionalCanvas',
       xtype: 'tabpanel',
       plain: true,
       style: 'padding-top: 44px; background: #FFFFFF; background-image: url(/images/regional/CA.jpg); background-repeat: no-repeat;',
       activeItem: 0,
       resizeTabs: true,
       minTabWidth: 100,
       dockedItems: [{
          docked: 'top',
          xtype: 'toolbar',
          items: [{
              xtype: 'combobox', 
              labelWidth: 80, 
              labelStyle: 'font-weight: bold', 
              fieldLabel: 'Current City', 
              emptyText: 'All Cities',
              editable: false,
              queryMode: 'local',
              triggerAction: 'all', 
              displayField: 'city',
              valueField: 'cityid',
              style: 'padding-right: 5px; padding-left: 2px;'
          }]
       }],
       items: [{
          title: 'Main',
          html: 'main'
       }, {
          title: 'Directory',
          html: 'directory'
       }, {
          title: 'Profiles',
          html: 'profiles'
       }, {
          title: 'Personals',
          html: 'personals'
       }]    
    }]
});
