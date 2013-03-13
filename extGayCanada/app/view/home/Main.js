Ext.define('GayCanada.view.home.Main', {
    extend: 'Ext.Panel',
    alias: [ 'widget.homeTab' ],

    requires: [ 'GayCanada.view.MenuTree' ],

    title: 'Home',
    border: false,
    layout: 'border',

    items: [{
       region: 'west',
       width: 130,
       id: 'homeMenu',
       xtype: 'menutree',
       data: [{ 
          text: "Welcome", expanded: true, collapsible: false, 
          items: [
             { text: 'Console', id: 'console', action: 'showConsole' },
             { text: 'Join GayCanada', id: 'join', action: 'joinGayCanada' }
          ] 
       }, {
          text: "Community", 
          items: [
             { text: 'Church Groups' },
             { text: 'Community Groups' },
             { text: 'Pride Groups' },
             { text: 'Social Groups' },
             { text: 'Sports Groups' }
          ]
       }, {
          text: "Health",
          items: [
             { text: 'AIDS/HIV' },
             { text: 'Alternative Health' },
             { text: 'Massage' }
          ]
       }, {
          text: "Services", 
          items: [
             { text: 'Accountants' },
             { text: 'Lawyers' },
             { text: 'Realtors' }
          ]
       }, {
          text: "Support" ,
          items: [
             { text: 'Advocacy' },
             { text: 'Support Groups' },
             { text: 'Resources' },
             { text: 'PFlag Groups' }
          ]
       }, {
          text: "Travel/Tourism", 
          items: [
             { text: 'Accommodations' },
             { text: 'Bars' },
             { text: 'Restaurants' }
          ]
       }, {
          text: "About", 
          items: [
             { text: 'About' },
             { text: 'Privacy' },
             { text: 'Terms of Service' },
             { text: 'Harrassment Policy' },
             { text: 'Advertise' },
             { text: 'Volunteer' },
             { text: 'Credits' },
             { text: 'Application' }
          ]
       }]
    }, {
       border: false,
       region: 'center',
       html: 'main'
    }]
});
