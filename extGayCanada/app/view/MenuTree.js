Ext.define('GayCanada.view.MenuTree', {
    extend: 'Ext.tree.Panel',
    alias: 'widget.menutree',

    cls: 'ux-menutree',
    rootVisible: false,
    lines: false,
    rowLines: false,
    border: false,
    bodyBorder: false,

    initComponent: function() {
       var children = this.buildTree(this.data);

       this.root = {
          text: 'root',
          children: children
       };

       this.addEvents("menuitemclick");
       this.on("itemclick", this.onItemClick, this);
       this.callParent();
    },

    buildTree: function(d) {
       var items = [];

       Ext.Array.each(d, function(b) {
           var  hasItems = Ext.isArray(b.items),
                conf = {
                    expanded: false,
                    cls: hasItems ? 'ux-menutree-group' : 'ux-menutree-item',
                    leaf: !hasItems,
                    children: this.buildTree(b.items)
                };

            for (key in b) {
                conf[key] = b[key];
            }

            items.push(conf);
            //{
            //    text: b.text,
            //    action: b.action,
            //    value: b.value ? b.value : null,
            //    expanded: b.expanded ? b.expanded : false,
            //    cls: hasItems ? 'ux-menutree-group' : 'ux-menutree-item',
            //    leaf: !hasItems,
            //    children: this.buildTree(b.items)
            //});
       }, this);
       return items;
    },

    onItemClick: function(view, record, el, index, event) {
       var a=record.raw?record.raw.action:record.data.action;
       if(a){
          this.fireEvent("menuitemclick", a, record, el, index, event);
       }else{
          if(!record.isLeaf()){
             if(record.isExpanded()){
                record.collapse(false)
             }else{
                record.expand(false)   
             }    
          } 
       }
    }
});
