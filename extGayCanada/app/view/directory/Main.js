Ext.define('GayCanada.view.directory.Main', {
    extend: 'Ext.Panel',
    alias: [ 'widget.directoryTab' ],

    requires: [ 
       'GayCanada.view.MenuTree',
       'Ext.ux.CustomTrigger'
    ],

    title: 'Directory',
    border: false,
    layout: 'border',

    items: [{
       region: 'west',
       width: 130,
       id: 'directoryMenu',
       xtype: 'menutree',
       data: [{  
           text: 'For the Visitor', expanded: true, collapsible: false,
           items: [
              { text: 'Accommodations', action: 'setCategory' },
              { text: 'Bars', action: 'setCategory' },
              { text: 'Restaurants', action: 'setCategory' }
           ]
       }, {
          text: 'Meeting & Greeting',
          items: [
              { text: 'Church Services', action: 'setCategory' },
              { text: 'Social Groups', action: 'setCategory' },
              { text: 'Sports & Recreation', action: 'setCategory' }
          ]
       }, {
          text: 'Resources',
          items: [
              { text: 'AIDS/HIV Resources', action: 'setCategory' },
              { text: 'Immigration Services', action: 'setCategory' },
              { text: 'PFLAG Chapters', action: 'setCategory' },
              { text: 'Support Groups', action: 'setCategory' },
              { text: 'Youth Services', action: 'setCategory' }
          ]
       }, {
          text: 'Actions',
          items: [
              { text: 'Add/Edit Listings', action: 'addEdit' }
          ]
       }]   
    }, { 
       region: 'center',
       border: false,   
       xtype: 'tabpanel',
       id: 'directoryTabPanel',
       style: 'border-left: solid 1px #404000; padding-top: 44px; background: url(/images/headers/directory.jpg) no-repeat;',
       plain: true,
       resizeTabs: true,
       minTabWidth: 150,
       maxTabWidth: 150,
       tabWidth: 150,
       enableTabScroll: true,
       items: [{ 
           title: 'Search Directory',
           tabConfig: {
              textAlign: 'left'
           },
           id: 'directoryGridPanel',
           xtype: 'gridpanel',
           border: false,
           store: 'directory.EntryList',
           dockedItems: [{
              dock: 'top',
              xtype: 'toolbar',
              items: [{
                 xtype: 'label', text: 'Search:', style: 'font-weight: bold;'
              }, {
                 xtype: 'tbspacer', width: 10
              }, {
                 xtype: 'combobox',
                 triggerAction: 'all',
                 store: 'regions.RegionFilter',
                 id: 'directoryRegionFilter',
                 queryMode: 'local',
                 editable: false,
                 width: 175,
                 value: 'All Provinces',
                 valueField: 'regionid',
                 displayField: 'regionname'
              }, {
                 xtype: 'combobox',
                 triggerAction: 'all',
                 id: 'directoryCategoryFilter',
                 store: 'directory.CategoryList',
                 width: 220,
                 queryMode: 'local',
                 value: 'All Categories',
                 valueField: 'categoryid',
                 displayField: 'description',
                 editable: false
              }, {
                 xtype: 'customtrigger',
                 width: 150,
                 id: 'directorySearchText',
                 emptyText: 'Enter search term'
              }, '->', {
                 xtype: 'button',
                 text: 'Add Listing',
                 textAlign: 'left',
                 width: 200,
                 action: 'search'
              }]
           }, {
              dock: 'bottom',
              xtype: 'pagingtoolbar',
              store: 'directory.EntryList',
              displayInfo: true
           }],
           viewConfig: {
              emptyText: 'Please select category, province and/or enter listing name. <br><br>Then click the magnifying glass to search our database for matching records.',
              deferEmptyText: false,
              getRowClass: function(r, i, p, s) {
                 return r.data.enhancedlisting == 'Yes' ? "enhanced-grid-row" : "";
              }
           },
           loadMask: { msg: 'Loading Entries' },
           columns: [   
              { dataIndex: 'entryid', header: ' ', width: 30, renderer: function(v) { return '' } },  
              { id: 'title', dataIndex: 'title', header: 'Title', renderer: function(v, md, rec) {  
                   var address = rec.data.address ? rec.data.address + ', ' : "";  
                   var teaser =  rec.data.enhancedlisting == 'Yes' && rec.data.teaser ? '<br><br><i>' + rec.data.teaser + '</i>'  : '';  
                   address += rec.data.city + ', ' + rec.data.region;  
                   var bold = rec.data.enhancedlisting == 'Yes' ? 'font-weight: bold;' : 'font-weight: normal';  
                   return '<span style="font-family: arial; font-size: 12px; ' + bold + '">' + v + '</span><br><span style="font-family: arial; font-size: 10px;">' + address + '</span>' + teaser;  
                 },
                flex: 1   
              },  
              { dataIndex: 'cityid', header: 'CityID', width: 40 },  
              { dataIndex: 'city', header: 'City', width: 100, hidden: false }, // groupRenderer: function(v) { return v; } }, 
              { dataIndex: 'region', header: 'Province', width: 100, hidden: true },  
              { dataIndex: 'phone1', header: 'Phone', width: 100 }  
           ]  
/*
       view: new Ext.grid.GridView({
//          enableGroupingMenu: false,
//          groupTextTpl: '{[values.rs[0].data.city]}, {[values.rs[0].data.region]} ({[values.rs.length]} {[values.rs.length > 1 ? "Listings" : "Listing" ]})',
          listeners: {
             'rowupdated': function(v, row, record) {
                v.getCell(row, 0).rowSpan = 2;
             }
          }   
       }),    
       listeners: {
          rowdblclick: function(gp, ri, event) {
             Ext.directory.addTab(gp.store.getAt(ri).data.entryid);
          },
          scope: this
       } */
       }]
    }]

});
