Ext.define('GayCanada.view.boards.Main', {
    extend: 'Ext.Panel',
    alias: [ 'widget.messageboardTab' ],

    title: 'Boards',
    layout: 'border',
    border: false,

    requires: [ 'GayCanada.store.boards.Boards' ],

    items: [{
	    region: 'west',
    	width: 170,
    	layout: 'fit',
    	split: true,
    	border: false,
    	style: 'border-right: solid 1px #000000;',
    	title: 'Posts',
    	tools: [
    		{ type: 'refresh', qtip: 'Refresh list' }
    	],
    	items: [{
    		xtype: 'tabpanel',
       		plain: true,
       		border: false,
       		activeItem: 0,
       		style: 'padding-top: 7px;',
       		items: [{
       			xtype: 'grid',
			    title: 'Latest',
//    			store: Ext.home.boardStore,
    			enableHdMenu: false,
    			enableColumnMove: false,
    			enableColumnResize: false,
    			hideHeaders: true,
    			columns: [
       				{ id: 'subject',  dataIndex: 'subject' }
    			],
//    			sm: new Ext.grid.RowSelectionModel({ singleSelect: true }),
    			autoExpandColumn: 'subject',
    			viewConfig: {
       				emptyText: 'No current discussions',
       				forceFit: true,
       				scrollOffset: 0
	    		},
    			listeners: {
       				rowclick: function(grid, rowindex, event) {
          				var rec = grid.store.getAt(rowindex).data;
//          				Ext.forums.goto(rec.boardid, rec.boardname, rec.topicid, rec.subject);
       				}
    			}
    		}, {
			    title: 'Bookmarks',
//    			store: Ext.user.bookmarkStore,
    			disabled: true,
    			hideHeaders: true,
    			columns: [
       				{ id: 'subject',  dataIndex: 'subject' }
    			],
    			//sm: new Ext.grid.RowSelectionModel({ singleSelect: true }),
			    autoExpandColumn: 'subject',
    			viewConfig: {
       				emptyText: 'No bookmarks found.',
       				deferEmptyText: false,
       				forceFit: true,
       				scrollOffset: 0
    			},
    			listeners: {
       				rowdblclick: function(grid, rowindex, event) {
//           Ext.module.tabPanels.goto({ tab: 'gcForumsTab' });
//, callback: function() { Ext.forums.setup(height) }, scope: this });
//              alert('row double clicked');
	       			},
    	   			scope: this
    	   		}
       		}]
    	}]
    }, {
    	region: 'center',
    	xtype: 'tabpanel',
	    id: 'gcForumsCards',
    	plain: true,
    	border: false,
    	style: 'border-left: solid 1px #404000; padding-top: 44px; background: url(/images/headers/messageboards.jpg) no-repeat; ',   
    	activeItem: 0,
//    	plugins: new Ext.ux.TabCloseMenu(),
//    tbar: [
//      { text: 'My Postings' }, '-',
//       '->',
//       this.searchString
//    ],
    	items: [{
    		xtype: 'grid',
    		title: 'Board Areas',
    		store: 'boards.Boards',
    		border: false,
    		autoScroll: true,
    		enableHdMenu: false,
//    tools: [
//      { id: 'refresh', handler: function() { this.store.reload() }, scope: this }
//    ],
			columns: [ 
        		{ id: 'category', header: 'Category', dataIndex: 'category', hidden: true },
        		{ id: 'forum', flex: 1, header: 'Forum', dataIndex: 'boardname', 
        			xtype: 'templatecolumn', 
        			tpl: '<div class="forum"><b>{boardname}</b><span class="author">{description}</span></div>'
			    },
        		{ header: 'Topics', dataIndex: 'topics', width: 50  },
        		{ header: 'Posts', dataIndex: 'postings', width: 50 },
        		{ header: 'Last Post', dataIndex: 'lastpost', width: 120}
    		], 
//    		sm: new Ext.grid.RowSelectionModel({ singleSelect: true }),
    		trackMouseOver: false,
    		loadMask: { msg: 'Loading....' },
    		features: [{ 
    			ftype: 'grouping',
    			groupHeaderTpl: '{name}'
    		}],
//    		view: new Ext.grid.GroupingView({
//          		showGroupName: false,
//          		groupTextTpl: '{text}',
//          		enableRowBody: true
//    		}),
    		listeners: {
       			rowclick: function(grid, rowIndex, event) {
          			var x = grid.store.getAt(rowIndex).data; 
//          			Ext.forums.goto(x.boardid, x.boardname);
       			},
    			scope: this
	    	}
    	}]
    }]
});
