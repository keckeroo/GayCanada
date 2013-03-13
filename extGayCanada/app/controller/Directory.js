Ext.define('GayCanada.controller.Directory', {
    extend: 'Ext.app.Controller',

    stores: [ 'directory.EntryList' ],

    config: {
       refs: [{
          ref: 'directoryRegionFilter', 
          selector: '#directoryRegionFilter'
       }, {
          ref: 'directoryCategoryFilter',
          selector: '#directoryCategoryFilter'
       }, {
          ref: 'directorySearchText', 
          selector: '#directorySearchText'
       }, {
          ref: 'directoryTabPanel',
          selector: '#directoryTabPanel'
       }]
    },

    init: function() {
       this.control({
          '#directoryMenu' : {
              menuitemclick: this.doMenuItemClick
          }, 
          '#directoryGridPanel  > customtrigger' : {
              triggerClick: this.doSearchTrigger
          },
          '#directoryGridPanel' : {
              itemdblclick: this.addEntryTab
          },
          '#directoryTabPanel': {
//             activate: function() { alert('2'); }
          }
       });
    },

    doMenuItemClick: function(action, record, el, index, event) {
        var me = this,
            dcf = me.getDirectoryCategoryFilter(),
            dsf = me.getDirectorySearchText();

        if (action == 'setCategory') {
           dcf.setValue(record.data.text);
           dsf.setValue('').focus();
        }
    },

    doSearchTrigger: function(field) {
        var me = this,
            drf = me.getDirectoryRegionFilter(),
            dcf = me.getDirectoryCategoryFilter(),
            searchText = field.getValue();
//         alert('Looking for ' + searchText + ' in ' );
         me.getDirectoryEntryListStore().load();
    },

    addEntryTab: function(view, record, el, index, event, options) {
       var me = this,
           dtp = me.getDirectoryTabPanel(),
           tabId = 'entryPanel-' + record.data.entryid,
           tab;

       if (!(tab = dtp.down('panel[id="' + tabId + '"]'))) {
          tab = Ext.create('Ext.Panel', {
                   tabConfig: {
                       textAlign: 'left'
                   },
                   id: tabId,
                   title: record.data.title,
                   closable: true,
                   html: record.data.title
          });
          dtp.add(tab);
       }
       dtp.setActiveTab(tab);
    }
});
