Ext.define('GayCanada.view.community.Main', {
    extend: 'Ext.Panel',
    alias: [ 'widget.communityTab' ],

    requires: [ 'GayCanada.view.MenuTree' ],

    title: 'Community',	
    border: false,
    layout: 'border',

    items: [{
       region: 'west',
       width: 130,
       id: 'communityMenu',
       xtype: 'menutree',
       data: [{
           text: "Community", expanded: true, collapsible: false,
           items: [
              { text: 'Profiles' },
              { text: 'Personals' },
              { text: 'Events' },
              { text: 'Entertainment' }
           ]
       }]
    }, {
       region: 'center',
       border: false,
       html: 'community'
    }]

});
