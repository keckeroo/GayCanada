Ext.define('Ext.ux.CustomTrigger', {
    extend: 'Ext.form.field.Trigger',
    alias: 'widget.customtrigger',

    triggerCls: 'x-form-search-trigger',

    initComponent: function() {
       this.addEvents("triggerClick");

       this.on("specialkey", this.checkReturn);
    },

    onTriggerClick: function() {
        this.fireEvent("triggerClick", this);
    },

    checkReturn: function(f, o) {
       if (o.getKey() == 13) {
          f.fireEvent("triggerClick", this);
       }
    }
});

