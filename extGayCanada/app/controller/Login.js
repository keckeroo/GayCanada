Ext.define('GayCanada.controller.Login', {
	extend: 'Ext.app.Controller',

	config: {
	   refs: [{
		   ref: 'main',
		   selector: 'mainview'
	   }, {
		   ref: 'username',
		   selector: '#username'
	   }, {
		   ref: 'password',
		   selector: '#password'
	   }, {
		   ref: 'mainTabPanel',
		   selector: '#mainTabPanel'
	   }, {
		   ref: 'mygaycanadaTab',
		   selector: '#mygaycanadaTab'
	   }, {
		   ref: 'chatroomsTab',
		   selector: '#chatroomsTab'
	   }, {
		   ref: 'adminTab',
		   selector: '#adminTab'
	   }, {
		  ref: 'loginButton',
		  selector: 'mainview button[action=doLogin]'
	   }, {
		  ref: 'logoutButton',
		  selector: 'mainview button[action=doLogout]'
	   }, {
		  ref: 'joinButton',
		  selector: 'mainview button[action=doJoin]'
	   }]
	},

	init: function() {
	   this.control({
		  'mainview button[action=doLogin]' : {
			 click: this.doLogin
		  },
		  'mainview button[action=doLogout]' : {
			 click: this.confirmBrowserLogout
		  },
		  'mainview button[action=doJoin]' : {
			 click: this.doJoin
		  }
	   });	   
	},

	doLogin: function() {
	   var x = this.getMain(),
		   me = this,
		   un = this.getUsername().getValue(),
		   pw = this.getPassword().getValue();

	   if (un == '')
		  alert('Username is required.')
	   else if (pw == '') 
		  alert('Password is required.')
	   else {
//          var store = this.getProfilesStore();

		  Ext.Ajax.request({
			  url: '/api/loginout.php',
			  params: { 
				  loginUsername: un,
				  loginPassword: pw
			  },
			  method: 'POST',
			  waitMsg: 'Connecting',
			  callback: function(options, success, response) {
				  var resp = Ext.decode(response.responseText);
				  if (resp.authenticated == false) {
				  	 Ext.Msg.alert('Error', resp.apiErrorMessage);
//                     main.setActiveItem(0);
				  }
				  else {
					 me.initUserEnvironment()
				  }
			  }
		  });
	   }
	},

	confirmBrowserLogout: function() {
	   var me = this;

		  Ext.Ajax.request({
			  url: '/api/loginout.php',
			  params: { logout: 1 },
			  method: 'POST',
			  waitMsg: 'Connecting',
			  callback: function(options, success, response) {
				  var resp = Ext.decode(response.responseText);
				  if (resp.authenticated == false) {
//                     Squirt.userObject = {};
//                     main.setActiveItem(0);
					 me.doLogout();
				  }
				  else {
//                     me.doLogout();
				  }
			  }
		  });
	},

	doJoin: function() {
	   alert('do join');
	},

	initUserEnvironment: function() {
		
	   var me = this,
		   mgcTab = this.getMygaycanadaTab(),
		   crTab = this.getChatroomsTab(),
		   unField = this.getUsername(),
		   pwField = this.getPassword(),
		   loginButton = this.getLoginButton(),
		   logoutButton = this.getLogoutButton(),
		   joinButton = this.getJoinButton(),
		   mainTabPanel = this.getMainTabPanel(),
		   adminTab = this.getAdminTab();

	   unField.hide();
	   pwField.hide();
	   loginButton.hide();
	   joinButton.hide();
	   logoutButton.show();
	   mgcTab.tab.show();
	   crTab.tab.show();
	   adminTab.tab.show();
	   adminTab.enable();
	   mainTabPanel.getLayout().setActiveItem(mgcTab);
	},

	doLogout: function() {
	   var me = this,
		   mgcTab = this.getMygaycanadaTab(),
		   crTab = this.getChatroomsTab(),
		   unField = this.getUsername(),
		   pwField = this.getPassword(),
		   loginButton = this.getLoginButton(),
		   logoutButton = this.getLogoutButton(),
		   joinButton = this.getJoinButton(),
		   mainTabPanel = this.getMainTabPanel(),
		   adminTab = this.getAdminTab();

	   unField.reset();
	   unField.show();
	   pwField.reset();
	   pwField.show();
	   loginButton.show();
	   joinButton.show();
	   logoutButton.hide();
	   mgcTab.tab.hide();
	   crTab.tab.hide();
	   adminTab.tab.hide();
	   mainTabPanel.getLayout().setActiveItem(0);
	}
});
