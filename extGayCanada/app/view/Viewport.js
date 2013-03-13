/*
 
 GayCanada Main Viewport

*/
Ext.define('GayCanada.view.Viewport', {
    extend: 'Ext.container.Viewport',
    alias: 'widget.mainview',
    
    layout: {
       type: 'hbox',
       align: 'stretch',
       pack: 'center'
    },

    requires: [
      'GayCanada.view.TaskBar',
      'GayCanada.view.home.Main',
      'GayCanada.view.community.Main',
      'GayCanada.view.directory.Main',
      'GayCanada.view.regional.Main',
      'GayCanada.view.boards.Main',
      'GayCanada.view.chatrooms.Main',
      'GayCanada.view.mygaycanada.Main',
      'GayCanada.view.admin.Main'
    ],

    config: {
       layout: {
          type: 'hbox',
          align: 'stretch',
          pack: 'center'
       },
       items: [{
          width: 960,
          border: false,
          layout: 'border',
          items: [{
             xtype: 'panel',
             border: false, 
             region: 'north',
             height: 70,
             style: 'border-right: solid 1px #000000;',
             html: [
                '<DIV class="gcHeader" ID="gcHeader">',
                '<div class="redcorner"></div>',
                '<div class="people"></div><img class="gcdotcom" src="/images/newtop/004.gif">',
                '<span class="commands">',
                '</span>',
                '</DIV>'  
             ]
          }, {
             xtype: 'tabpanel',
             id: 'mainTabPanel',  
             region: 'center', 
             plain: true,
             style: 'padding-top: 0px; background: #FFFFFF; border-left: solid 1px #000000; border-bottom: solid 1px #000000; border-right: solid 1px #000000;',
             border: false,
             minTabWidth: 115,
             defaults: { border: false },
             dockedItems: [{
                xtype: 'toolbar',
                items: [
                  { xtype: 'tbtext', text: 'Welcome to GayCanada!' },
                  '->',
                  { xtype: 'textfield', id: 'username', name: 'username', width: 100, emptyText: 'Username', style: 'margin-right: 5px;', hidden: false, required: true },
                  { xtype: 'textfield', id: 'password', name: 'password', width: 100, emptyText: 'Password', style: 'margin-right: 5px;', inputType: 'password', hidden: false, required: true },
                  { xtype: 'button', width: 60, text: 'Login', action: 'doLogin' },
                  { xtype: 'button', width: 60, text: 'Logout', action: 'doLogout', hidden: true },
                  { xtype: 'button', width: 60, text: 'Join', action: 'doJoin' },
                  '-',
                  { xtype: 'button', width: 60, text: 'Help', action: 'showHelp', iconCls: 'silk-help' }
                ]
             }], 
          items: [{
             xtype: 'homeTab',
             id: 'homeTab'
          }, {
             xtype: 'communityTab',
             id: 'communityTab'
          }, {
             xtype: 'directoryTab',
             id: 'directoryTab'
          }, {
             xtype: 'regionalTab',
             id: 'regionalTab'
          }, {
             xtype: 'messageboardTab',
             id: 'messageboardTab'
          }, {
             xtype: 'chatroomsTab',
             id: 'chatroomsTab',
             hidden: true
          }, {
             xtype: 'mygaycanadaTab',
             id: 'mygaycanadaTab',
             hidden: true
          }, {
             xtype: 'adminTab',
             id: 'adminTab',
             hidden: true
          }]
       }, {  
         region: 'east',
         width: 240,
         collapsible: true,
         hidden: true,
         html: 'qm'   
      }, {
         region: 'south',
         xtype: 'taskbar'
      }]
}]
    }

});