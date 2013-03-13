//
// Interactive Community Applications - Mail
//

var icaSendMailPanel;

Ext.namespace('icaMail');

icaMail.newMessageCount = 0;
icaMail.currentFolder   = '';

icaMail.updateCount = function(adjustCount) {
   var adjustCount = Number(adjustCount);
   if (adjustCount > icaMail.newMessageCount) {
       if (icaMail.currentFolder == 'Inbox') {
          icaMailHeadersDataStore.reload();
       }
       Ext.ux.Toaster.msg('New Message', 'You\'ve received a new message! (' + adjustCount + ', ' + icaMail.newMessageCount + ')');
   }
   icaMail.newMessageCount = adjustCount > 0 ? adjustCount : icaMail.newMessageCount + adjustCount;
   var mTitle = icaMail.newMessageCount > 0 ? ' (<b><blink>' + icaMail.newMessageCount + '</blink></b>)' : '';
   if (Ext.get('icaMailNewMessageCount')) {
      Ext.get('icaMailNewMessageCount').update(mTitle);
   }
}

var icaSendMailForm = new Ext.form.FormPanel({
    id: 'smFormPanel',
    baseCls: 'x-plain',
    layout: 'absolute',
    border: true,
    height: 300,
    items: [
       { x: 0,   y: 5, xtype: 'label', text: 'To:' },
       { x: 55,  y: 0, xtype: 'panel', frame: false, items: { xtype: 'button', text: 'Contacts', disabled: true } },
       { x: 130, y: 0, xtype: 'textfield', id: 'icaMailTo', xtype:'textfield', name: 'icaMailTo', anchor: '100%' },
       { x: 0,   y: 31, xtype: 'label', text: 'Subject:' },
       { x: 55,  y: 26, xtype: 'textfield', id: 'icaMailSubject', name: 'icaMailSubject', anchor: '100%' },
       { x: 0,   y: 53, xtype: 'textarea',  id: 'icaMailBody', name: 'icaMailBody',    anchor: '100% 100%' }
    ]
});

openSendMailPanel = function() {
   var toUser = arguments.length > 0 ? arguments[0] : '';
   var replySubject = arguments.length > 1 ? arguments[1] : '';
   var reply = arguments.length > 2 ? arguments[2] : '';
   var replyMessage = '';

   if (reply != '') {
      replyMessage = "\n----------------------------------------------\n" +  icaMailMessageDataStore.getAt(0).data.message;
   }

   if (!icaSendMailPanel) {
     icaSendMailPanel = new Ext.Window({
        id: 'smPanel',
        closeAction: 'hide',
        layout: 'fit',
        title: 'Send Message',
        bodyStyle: 'padding: 10px 5px 5px',
        cls: 'messageMask',
        width: 600,
        frame: true,
//        plain: true,
        items: icaSendMailForm,
        tbar: [{
                text: 'Send',
                iconCls: 'ux-icon-email-go',
                handler: function() {
               icaSendMailForm.getForm().submit({
                  method: 'POST',
                  waitTitle: 'Send Mail',
                  waitMsg: 'Sending message ...',
                  waitMsgTarget: false,
                  url: '/ica/icaSendMessage.php',
                  success: function(form, action) {
//                      Ext.Msg.alert(action.response.responseText);
                  },
                  failure: function(form, action) {
                      Ext.Msg.alert('Unable to send message. Please try again at a later time.');
                  }
               });
               icaSendMailPanel.hide();
            }
        },'-',
        { text: 'Cancel', 
          handler: function() {
              icaSendMailPanel.hide();
          }
        }
//        { text: 'Check Spelling', iconCls: 'icon-spell' },'-',
//        { text: 'Print', iconCls: 'icon-print' },'->',
//        { text: 'Attach a File', iconCls: 'icon-attach' }
        ],
      listeners: {
         show: function() {
            Ext.getCmp('icaMailBody').focus(false, 500);
         }
      }
      });
   }


   var selCount = messageList.getSelectionModel().getCount();
   if (sel) {
      var sel = messageList.getSelectionModel().getSelected();
      var selIndex = ds.indexOf(sel);
      var selData = sel.data;
   }

   Ext.getCmp('smFormPanel').getForm().reset();

   if (toUser) {
      Ext.getCmp('icaMailTo').setValue(toUser);
   }
   if (replySubject != '') {
      var pre = replySubject.match(/^Re:/) ? '' : 'Re: ';
      Ext.getCmp('icaMailSubject').setValue(pre+replySubject);
   }
   if (replyMessage) {
      Ext.getCmp('icaMailBody').setValue(replyMessage);
   }
   icaSendMailPanel.show();
}

var icaMailMessageDataStore = new Ext.data.Store({
    pruneModifiedRecords: true,
    url: '/ica/icaGetMessage.php',
    reader: new Ext.data.XmlReader({
       record: 'messageInfo',
       id: 'mid'
    }, ['mid', 'from', 'subject', 'date', 'message', 'status']
    )
});

var icaMailFoldersDataStore = new Ext.data.Store({
    autoLoad: true,
    pruneModifiedRecords: true,
    url: '/ica/icaGetFolders.php',
    reader: new Ext.data.XmlReader({
       record: 'icaMailFolderInfo',
       id: 'mid'
    }, ['folderName', 'folderUnreadCount', 'folderTotalCount' ]
    )
});

var icaMailHeadersDataStore = new Ext.data.Store({
    cRecords: 0,
    pruneModifiedRecords: true,
    url: '/ica/icaGetFolderMessages.php',
    reader: new Ext.data.XmlReader({
       record: 'messageHeader',
       id: 'mid'
    }, ['mid', 'from', 'subject', 'date', 'status' ]
    )
});

var messageTemplate = new Ext.XTemplate(
    '<tpl for=".">',
    '<div class="post-data">',
    '<span class="post-date">{date}</span>',
    '<span class="post-title">Subject: {subject}</span>',
    '<h4 class="post-author">From: <a href="javascript: showUserProfile(\'{from}\')";>{from:defaultValue("Unknown")}</a></h4>',
    '</div>',
    '<div class="post-body">{[this.wrapBody(values.message)]}</div>',
    '</tpl>', {
    wrapBody: function(message) {
       return message.replace(/\n/g, "<br>");
    }
});

var icaMailFolders =  new Ext.tree.TreePanel({
    region: 'west',
    split: true,
    minSize: 100,
    maxSize: 150,
    id:'im-tree',
    loader: new Ext.tree.TreeLoader(),
    rootVisible:false,
    lines:false,
    width: 125,
    height: 400,
    autoScroll:true,
    root: new Ext.tree.AsyncTreeNode({
       children:[
         { id: 'folder-Inbox', text:'Inbox', expandable: false, leaf: true,
           listeners: { 'click': function() {
                            icaMailDeleteBtn.disable();
                            icaMailReplyBtn.disable();
                            icaMailBlockBtn.disable();
                            messageList.setTitle('Inbox');      
                            icaMail.currentFolder = 'Inbox';
                            icaMailMessageDataStore.removeAll();
                            icaMailDeleteBtn.setText('Delete');
                            icaMailHeadersDataStore.load({ params: { 'icaMailFolder': 'Inbox'}});
                        }
                      }
         },
//         { id: 'folder-Sent', text:'Sent',  expandable: false, leaf: true,
//           listeners: { 'click': function() {
//                            icaMailDeleteBtn.disable();
//                            icaMailReplyBtn.disable();
//                            icaMailBlockBtn.disable();
//                            messageList.setTitle('Sent');   
//                            icaMail.currentFolder = 'Sent';     
//                            icaMailMessageDataStore.removeAll();
//                            icaMailDeleteBtn.setText('Unsend');
//                            icaMailHeadersDataStore.load({ params: { 'icaMailFolder': 'Send'}});
//                      }
//           }
//         },
         { id: 'folder-Trash', text:'Trash',  expandable: false, leaf: true, 
           listeners: { 'click': function() {
                            icaMailDeleteBtn.disable();
                            icaMailReplyBtn.disable();
                            icaMailBlockBtn.disable();
                            messageList.setTitle('Trash');   
                            icaMail.currentFolder = 'Trash';     
                            icaMailMessageDataStore.removeAll();
                            icaMailDeleteBtn.setText('Undelete');
                            icaMailHeadersDataStore.load({ params: { 'icaMailFolder': 'Trash'}});
                      }
           }
         }
        ]
    })
});

var messageForm = new Ext.form.FormPanel({
    labelWidth: 60,
    url: 'save-message.php',
    bodyStyle: 'padding: 2px',
    items: [
       { xtype: 'textfield', fieldLabel: 'From', name: 'from', anchor: '100%'},
       { xtype: 'textfield', fieldLabel: 'Received', name: 'date', anchor: '100%'},
       { xtype: 'textfield', fieldLabel: 'Subject', name: 'subject', anchor: '100%'},
       { xtype: 'textarea', hideLabel: true, name: 'message', anchor: '100% -53' }
    ]
});

var messageView = new Ext.DataView({
    store: icaMailMessageDataStore,
    height: 150,
    autoHeight: true,
    tpl: messageTemplate,     
    itemSelector: 'post-data',
    emptyText: 'No message selected.',
    loadingText: 'Loading ...'
});

var viewMessage = new Ext.Panel({
    layout: 'fit',
    height: 250,
    region: 'center',
    border: false,
    autoScroll: true,
    closeAction: 'hide',
    cls: 'messageMask',
    items: messageView
});

var messageList = new Ext.grid.GridPanel({
    layout: 'fit',
    title:'Inbox',
    border: false,
    region: 'north',
    height: 200,
    minSize: 150,
    maxSize: 300,
    split: true,
    store: icaMailHeadersDataStore,
    sm: new Ext.grid.RowSelectionModel({
        listeners: {
           rowdeselect : function(mod, rowIndex, r) {
               if (mod.getCount() == 0) {
                  icaMailDeleteBtn.disable();
                  icaMailBlockBtn.disable();
               }
               if (mod.getCount() != 1) {
                  icaMailReplyBtn.disable();
                  icaMailMessageDataStore.removeAll();
               }
           },
           rowselect : function(mod, rowIndex, r) {
               if (mod.getCount() != 1) {
                  icaMailReplyBtn.disable();
                  icaMailMessageDataStore.removeAll();
               }
               else {
                  if (r.data.status == 'Unread') {
                     icaMail.updateCount(-1);
                     Ext.fly(messageList.getView().getRow(rowIndex)).removeClass('mail-unread-font');
                     Ext.fly(messageList.getView().getRow(rowIndex)).addClass('mail-read-font');
                     Ext.Ajax.request({
                        url: '/ica/icaUpdateMessage.php',
                        params: {
                          'icaMailMID': r.data.mid,
                          'icaMailStatus' : 'Read'
                        }
                     });
                     r.data.status = 'Read';
                  }
                  icaMailMessageDataStore.load({ params: { MID: r.data.mid }});
                  icaMailDeleteBtn.enable();
                  icaMailReplyBtn.enable();
                  icaMailBlockBtn.enable();
              }
           }
        }
    }),
    cm: new Ext.grid.ColumnModel([
        {id:'from',     header: "From",    width: 100, sortable: true, dataIndex: 'from'},
        {id:'subject',  header: "Subject", width: 350, sortable: true, dataIndex: 'subject'},
        {header: "Date",          width: 125, sortable: true, dataIndex: 'date'},
        {dataIndex: 'status', hidden: true }
    ]),
    tools:[{
       id:'refresh',
       handler: function() {
          icaMailHeadersDataStore.reload();
       }
    }],  
    viewConfig: { 
       onLoad: Ext.emptyFn,
       listeners: {
           beforerefresh: function(v) {
              v.scrollTop = v.scroller.dom.scrollTop;
              v.scrollHeight = v.scroller.dom.scrollHeight;
           },
           refresh: function(v) {
              v.scroller.dom.scrollTop = v.scrollTop + (v.scrollTop == 0 ? 0 : v.scroller.dom.scrollHeight - v.scrollHeight);
           }
       },
       forceFit: true,
       emptyText: 'Empty folder',
       getRowClass: function(record, rowIndex, rowParams, store) { 
          if (record.get('status') == 'Unread') {
            return 'mail-unread-font';
          }
          return 'mail-read-font';
       } 
    },
    autoExpandColumn: 'subject',
    enableHdMenu: false,
    frame:false,
    stripeRows: true
});

var icaMailSplitPanel = new Ext.Panel({
    layout: 'border',
    border: false,
    region: 'center',
    height: 400,
    width: 650,
    items: [
       messageList,
       viewMessage
    ]
});

var icaMailComposeBtn = new Ext.Action({ iconCls: 'icon-mail-unread', text: 'Compose', tooltip: { text: 'Create a new message.'}, handler: function() { openSendMailPanel(); } });
var icaMailReplyBtn   = new Ext.Action({ 
    text: 'Reply',  
    disabled: true, 
    tooltip: { text: 'Reply to this message.'},
    handler: function() {
       var sel = messageList.getSelectionModel().getSelected();
       openSendMailPanel(sel.data.from, sel.data.subject, true);
    }
});

var icaMailDeleteBtn  = new Ext.Action({ 
    iconCls: 'icon-mail-delete',
    text: 'Delete', 
    disabled: true, 
    tooltip: { text: 'Move selected message(s) to trash.'},
    handler: function() {
        var selCount = messageList.getSelectionModel().getCount();
        var selArray = messageList.getSelectionModel().getSelections();
        var newFolder = icaMailDeleteBtn.getText() == 'Delete' ? 'Trash' : 'Inbox';
        for ($i = 0; $i < selCount; $i++) {
           Ext.Ajax.request({
              url: '/ica/icaUpdateMessage.php',
              params: {
                 'icaMailMID': selArray[$i].data.mid,
                 'icaMailFolder' : newFolder
              }
           });
           icaMailHeadersDataStore.remove(selArray[$i]);
        }
        icaMailMessageDataStore.removeAll();
        icaMailDeleteBtn.disable();
        icaMailReplyBtn.disable();
        icaMailBlockBtn.disable();
     }
});

var icaMailBlockBtn   = new Ext.Action({ 
    iconCls: 'icon-user-block',
    text: 'Block',  
    disabled: true, 
    tooltip: { text: 'Block user(s) from sending messages.'},
    handler: function() {
       alert('block user(s)');
    }
});

var icaMailOptionsBtn = new Ext.Action({ text: 'Options', tooltip: { text: 'Mail Options'} });

var icaMailToolBar = new Ext.Toolbar({
    items: [
       icaMailComposeBtn, '-',
       icaMailReplyBtn, '-',
       icaMailDeleteBtn, '-',
       icaMailBlockBtn, '->',
       icaMailOptionsBtn
    ]
});

var icaMailPanel = new Ext.Panel({
    id: 'icaMailPanel',
    header: true,
    layout: 'border',
    border: false,
    width: 778,
    height: 500,
    tbar: icaMailToolBar,
    items: [
       icaMailFolders,
        icaMailSplitPanel
     ]
});

// End of ICA Mail Library
