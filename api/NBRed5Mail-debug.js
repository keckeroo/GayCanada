/*
 * NBRed5Mail
 * Copyright(c) 2006, Nick Brown.
 * 
 * This code is licensed under BSD license. Use it as you wish, 
 * but keep this copyright intact.
 */


String.prototype.endsWith = function(s){
	var reg = new RegExp(s + "$");
	return reg.test(this);
};
Ext.override(Ext.grid.GroupingView, {
	beforeMenuShow : function(){
		var field = this.getGroupField();
		var g = this.hmenu.items.get('groupBy');
		if(g){
			g.setDisabled(this.cm.config[this.hdCtxIndex].groupable === false);
		}
		var s = this.hmenu.items.get('showGroups');
		if(s){
			s.setDisabled(!field && this.cm.config[this.hdCtxIndex].groupable === false);
			s.setChecked(!!field, true);
		}
	}
});
Ext.override(Ext.tree.TreeNodeUI, {
	updateExpandIcon : function(){
        if(this.rendered){
            var n = this.node, c1, c2;
            var cls = n.isLast() ? "x-tree-elbow-end" : "x-tree-elbow";
            var hasChild = n.hasChildNodes();
            if(hasChild){
                if(n.expanded){
                    cls += "-minus";
                    c1 = "x-tree-node-collapsed";
                    c2 = "x-tree-node-expanded";
                }else{
                    cls += "-plus";
                    c1 = "x-tree-node-expanded";
                    c2 = "x-tree-node-collapsed";
                }
                if(this.wasLeaf){
                    this.removeClass("x-tree-node-leaf");
                    this.wasLeaf = false;
                }
                if(this.c1 != c1 || this.c2 != c2){
                    Ext.fly(this.elNode).replaceClass(c1, c2);
                    this.c1 = c1; this.c2 = c2;
                }
            }else{
                if(!this.wasLeaf){
					if (n.isLeaf()){
                    	Ext.fly(this.elNode).replaceClass("x-tree-node-expanded", "x-tree-node-leaf");
					}
                    delete this.c1;
                    delete this.c2;
                    this.wasLeaf = true;
                }
            }
            var ecc = "x-tree-ec-icon "+cls;
            if(this.ecc != ecc){
                this.ecNode.className = ecc;
                this.ecc = ecc;
            }
        }
    }
});


Ext.ux.ManagedIFrame = function(){
    var args=Array.prototype.slice.call(arguments, 0)
        ,el = Ext.get(args[0])
        ,config = args[0];

    if(el && el.dom && el.dom.tagName == 'IFRAME'){
            config = args[1] || {};
    }else{
            config = args[0] || args[1] || {};
            el = config.autoCreate?
            Ext.get(Ext.DomHelper.append(config.autoCreate.parent||document.body,
                Ext.apply({tag:'iframe', src:(Ext.isIE&&Ext.isSecure)?Ext.SSL_SECURE_URL:''},config.autoCreate))):null;
    }

    if(!el || el.dom.tagName != 'IFRAME') return el;

    !!el.dom.name.length || (el.dom.name = el.dom.id); 

    this.addEvents({
        "domready"       : true,
        "documentloaded" : true,        
        "exception" : true,
        "message" : true
    });

    if(config.listeners){
        this.listeners=config.listeners;
        Ext.ux.ManagedIFrame.superclass.constructor.call(this);
    }

    Ext.apply(el,this);  

    el.addClass('x-managed-iframe');
    if(config.style){
        el.applyStyles(config.style);
    }
    
    var CSS = Ext.util.CSS, rules=[];
    CSS.getRule('.x-managed-iframe') || ( rules.push('.x-managed-iframe {height:100%;width:100%;overflow:auto;}'));
    CSS.getRule('.x-frame-shim')   || ( rules.push('.x-frame-shim {z-index:18000!important;position:absolute;top:0;left:0;background-color:transparent;width:100%;height:100%;zoom:1;}'));
    CSS.getRule('.x-managed-iframe-mask')   || ( rules.push('.x-managed-iframe-mask {width:100%;height:100%;position:relative;}'));
    if(!!rules.length){
        CSS.createStyleSheet(rules.join(' '));
    }

    el._maskEl = el.parent('.x-managed-iframe-mask') || el.parent().addClass('x-managed-iframe-mask');
    Ext.apply(el._maskEl,{
       applyShim :  function(shimCls){
           if(this._mask){
               this._mask.remove();
           }
           this._mask = Ext.DomHelper.append(this.dom, {cls:shimCls||"x-frame-shim"}, true);
           this.addClass("x-masked");
           this._mask.setDisplayed(true);
       },
       removeShim  : function(){ this.unmask(); }
    });

    Ext.apply(el,{
      disableMessaging : config.disableMessaging===true
     ,applyShim        : el._maskEl.applyShim.createDelegate(el._maskEl)
     ,removeShim       : el._maskEl.removeShim.createDelegate(el._maskEl)
     ,loadMask         : Ext.apply({msg:'Loading..'
                            ,msgCls:'x-mask-loading'
                            ,maskEl: el._maskEl
                            ,hideOnReady:true
                            ,disabled:!config.loadMask},config.loadMask)
    
     ,_eventName       : Ext.isIE?'onreadystatechange':'onload'
     ,_windowContext   : null
     ,eventsFollowFrameLinks  : typeof config.eventsFollowFrameLinks=='undefined'?
                                true:config.eventsFollowFrameLinks
    });

    el.dom[el._eventName] = el.loadHandler.createDelegate(el);

    if(document.addEventListener){  
       Ext.EventManager.on(window,"DOMFrameContentLoaded", el.dom[el._eventName]);
    }

    var um = el.updateManager=new Ext.UpdateManager(el,true);
    um.showLoadIndicator= config.showLoadIndicator || false;

    if(config.src){
        el.setSrc(config.src);
    }else{

        var content = config.html || config.content || false;

        if(content){
            el.update.defer(10,el,[content]); 
        }
    }

    return Ext.ux.ManagedIFrame.Manager.register(el);

};

Ext.extend(Ext.ux.ManagedIFrame , Ext.util.Observable,
    {

    src : null ,
      
    setSrc : function(url, discardUrl, callback){
          var reset = Ext.isIE&&Ext.isSecure?Ext.SSL_SECURE_URL:'';
          var src = url || this.src || reset;

          if(Ext.isOpera){
              this.dom.src = reset;
           }
          this._windowContext = null;
          this._hooked = this._domReady = this._domFired = false;
          this._callBack = callback || false;

          this.showMask();

          (function(){
                var s = typeof src == 'function'?src()||'':src;
                try{
                    this._frameAction = true; 
                    this.dom.src = s;
                    this.frameInit= true; 
                    this.checkDOM();
                }catch(ex){ this.fireEvent('exception', this, ex); }

          }).defer(10,this);

          if(discardUrl !== true){ this.src = src; }

          return this;

    },
    reset     : function(src, callback){
          this.setSrc(src || (Ext.isIE&&Ext.isSecure?Ext.SSL_SECURE_URL:''),true,callback);

    },
    
    scriptRE  : /(?:<script.*?>)((\n|\r|.)*?)(?:<\/script>)/gi
    ,
    
    update : function(content,loadScripts,callback){

        loadScripts = loadScripts || this.getUpdateManager().loadScripts || false;

        content = Ext.DomHelper.markup(content||'');
        content = loadScripts===true ? content:content.replace(this.scriptRE , "");

        var doc;

        if(doc  = this.getDocument()){

            this._frameAction = !!content.length;
            this._windowContext = this.src = null;
            this._callBack = callback || false;
            this._hooked = this._domReady = this._domFired = false;

            this.showMask();
            doc.open();
            doc.write(content);
            doc.close();
            this.frameInit= true; 
            if(this._frameAction){
                this.checkDOM();
            } else {
                this.hideMask(true);
                if(this._callBack)this._callBack();
            }

        }else{
            this.hideMask(true);
            if(this._callBack)this._callBack();
        }
        return this;
    },

    
    disableMessaging :  true,

    
    _XFrameMessaging  :  function(){
        
        var tagStack = {'$' : [] };
        var isEmpty = function(v, allowBlank){
             return v === null || v === undefined || (!allowBlank ? v === '' : false);
        };
        window.sendMessage = function(message, tag, origin ){
            var MIF;
            if(MIF = arguments.callee.manager){
                if(message._fromHost){
                    var fn, result;
                    
                    var compTag= message.tag || tag || null;
                    var mstack = !isEmpty(compTag)? tagStack[compTag.toLowerCase()]||[] : tagStack["$"];

                    for(var i=0,l=mstack.length;i<l;i++){
                        if(fn = mstack[i]){
                            result = fn.apply(fn.__scope,arguments)===false?false:result;
                            if(fn.__single){mstack[i] = null;}
                            if(result === false){break;}
                        }
                    }

                    return result;
                }else{

                    message =
                        {type   :isEmpty(tag)?'message':'message:'+tag.toLowerCase().replace(/^\s+|\s+$/g,'')
                        ,data   :message
                        ,domain :origin || document.domain
                        ,uri    :document.documentURI
                        ,source :window
                        ,tag    :isEmpty(tag)?null:tag.toLowerCase()
                        };

                    try{
                       return MIF.disableMessaging !== true
                        ? MIF.fireEvent.call(MIF,message.type,MIF, message)
                        : null;
                    }catch(ex){} 

                    return null;
                }

            }
        };
        window.onhostmessage = function(fn,scope,single,tag){

            if(typeof fn == 'function' ){
                if(!isEmpty(fn.__index)){
                    throw "onhostmessage: duplicate handler definition" + (tag?" for tag:"+tag:'');
                }

                var k = isEmpty(tag)? "$":tag.toLowerCase();
                tagStack[k] || ( tagStack[k] = [] );
                Ext.apply(fn,{
                   __tag    : k
                  ,__single : single || false
                  ,__scope  : scope || window
                  ,__index  : tagStack[k].length
                });
                tagStack[k].push(fn);

            } else
               {throw "onhostmessage: function required";}


        };
        window.unhostmessage = function(fn){
            if(typeof fn == 'function' && typeof fn.__index != 'undefined'){
                var k = fn.__tag || "$";
                tagStack[k][fn.__index]=null;
            }
        };


    },

    
    _renderHook : function(){

        this._windowContext = null;
        this._hooked = false;
        try{
           if(this.writeScript('(function(){parent.Ext.get("'+
                                this.dom.id+
                                '")._windowContext='+
                                (Ext.isIE?'window':'{eval:function(s){return eval(s);}}')+
                                ';})();')){

                if(this.disableMessaging !== true){
                       this.loadFunction({name:'XMessage',fn:this._XFrameMessaging},false,true);
                       var sm;
                       if(sm=this.getWindow().sendMessage){
                           sm.manager = this;
                       }
               }
           }
           return this.domWritable();
          }catch(ex){}
        return false;

    },
    
    sendMessage : function (message,tag,origin){
         var win;
         if(this.disableMessaging !== true && (win = this.getWindow())){
              
              tag || (tag= message.tag || '');
              tag = tag.toLowerCase();
              message = Ext.applyIf(message.data?message:{data:message},
                                 {type   :Ext.isEmpty(tag)?'message':'message:'+tag
                                 ,domain :origin || document.domain
                                 ,uri    : document.documentURI
                                 ,source : window
                                 ,tag    :tag || null
                                 ,_fromHost: this
                    });
             return win.sendMessage?win.sendMessage.call(null,message,tag,origin): null;
         }
         return null;

    },
    _windowContext : null,
    
    getDocument:function(){
        return this.getWindow()?this.getWindow().document:null;
    },

    
    getDocumentURI : function(){
        var URI;
        try{
           URI = this.src?this.getDocument().location.href:null;
        }catch(ex){} 

        return URI || this.src;
    },
    
    getWindow:function(){
        var dom= this.dom;
        return dom?dom.contentWindow||window.frames[dom.name]:null;
    },

    
    print:function(){
        try{
            var win = this.getWindow();
            if(Ext.isIE){win.focus();}
            win.print();
        } catch(ex){
            throw 'print exception: ' + (ex.description || ex.message || ex);
        }
    },
    
    destroy:function(){
        this.removeAllListeners();

        if(this.dom){
             
             if(document.addEventListener){ 
                Ext.EventManager.un(window,"DOMFrameContentLoaded", this.dom[this._eventName]);
               }
             this.dom[this._eventName]=null;

             this._windowContext = null;
             
             if(Ext.isIE && this.dom.src){
                this.dom.src = 'javascript:false';
             }
             this._maskEl = null;
             Ext.removeNode(this.dom);

        }

        Ext.apply(this.loadMask,{masker :null ,maskEl : null});
        Ext.ux.ManagedIFrame.Manager.deRegister(this);
    }
    
    ,domWritable  : function(){
        return !!this._windowContext;
    }
    
    ,execScript: function(block, useDOM){
      try{
        if(this.domWritable()){
            if(useDOM){
               this.writeScript(block);
            }else{
                return this._windowContext.eval(block);
            }

        }else{ throw 'execScript:non-secure context' }
       }catch(ex){
            this.fireEvent('exception', this, ex);
            return false;
        }
        return true;

    }
    
    ,writeScript  : function(block, attributes) {
        attributes = Ext.apply({},attributes||{},{type :"text/javascript",text:block});

         try{
            var head,script, doc= this.getDocument();
            if(doc && doc.getElementsByTagName){
                if(!(head = doc.getElementsByTagName("head")[0] )){
                    
                    
                    head =doc.createElement("head");
                    doc.getElementsByTagName("html")[0].appendChild(head);
                }
                if(head && (script = doc.createElement("script"))){
                    for(var attrib in attributes){
                          if(attributes.hasOwnProperty(attrib) && attrib in script){
                              script[attrib] = attributes[attrib];
                          }
                    }
                    return !!head.appendChild(script);
                }
            }
         }catch(ex){ this.fireEvent('exception', this, ex);}
         return false;
    }
    
    ,loadFunction : function(fn, useDOM, invokeIt){

       var name  =  fn.name || fn;
       var    fn =  fn.fn   || window[fn];
       this.execScript(name + '=' + fn, useDOM); 
       if(invokeIt){
           this.execScript(name+'()') ; 
        }
    }

    
    ,showMask: function(msg,msgCls,forced){
          var lmask;
          if((lmask = this.loadMask) && (!lmask.disabled|| forced)){
               if(lmask._vis)return;
               lmask.masker || (lmask.masker = Ext.get(lmask.maskEl||this.dom.parentNode||this.wrap({tag:'div',style:{position:'relative'}})));
               lmask._vis = true;
               lmask.masker.mask.defer(lmask.delay||5,lmask.masker,[msg||lmask.msg , msgCls||lmask.msgCls] );
           }
       }
    
    ,hideMask: function(forced){
           var tlm;
           if((tlm = this.loadMask) && !tlm.disabled && tlm.masker ){
               if(!forced && (tlm.hideOnReady!==true && this._domReady)){return;}
               tlm._vis = false;
               tlm.masker.unmask.defer(tlm.delay||5,tlm.masker);
           }
    }

    
    ,loadHandler : function(e){

        if(!this.frameInit || (!this._frameAction && !this.eventsFollowFrameLinks)){return;}

        var rstatus = (e && typeof e.type !== 'undefined'?e.type:this.dom.readyState );
        switch(rstatus){
            case 'loading':  
            case 'interactive': 

              break;
            case 'DOMFrameContentLoaded': 

              if(this._domFired || (e && e.target !== this.dom)){ return;} 

            case 'domready': 
              if(this._domFired)return;
              if(this._domFired = this._hooked = this._renderHook() ){
                 this._frameAction = (this.fireEvent("domready",this) === false?false:this._frameAction);  
              }
            case 'domfail': 

              this._domReady = true;
              this.hideMask();
              break;
            case 'load': 
            case 'complete': 
              if(!this._domFired ){  
                  this.loadHandler({type:'domready'});
              }
              this.hideMask(true);
              if(this._frameAction || this.eventsFollowFrameLinks ){
                
                this.fireEvent.defer(50,this,["documentloaded",this]);
              }
              this._frameAction = false;
              if(this.eventsFollowFrameLinks){  
                  this._domFired = this._domReady = false;
              }
              if(this._callBack){
                   this._callBack(this);
              }

              break;
            default:
        }

    }
    
    ,checkDOM : function(win){
        if(Ext.isOpera)return;
        
        var n = 0
            ,win = win||this.getWindow()
            ,manager = this
            ,domReady = false
            ,max = 100;

            var poll =  function(){  
               try{
                 domReady  =false;
                 var doc = win.document,body;
                 if(!manager._domReady){
                    domReady = (doc && doc.getElementsByTagName);
                    domReady = domReady && (body = doc.getElementsByTagName('body')[0]) && !!body.innerHTML.length;
                 }

               }catch(ex){
                     n = max; 
               }

                
                
                

                if(!manager._frameAction || manager._domReady)return;

                if(n++ < max && !domReady )
                {
                    
                    setTimeout(arguments.callee, 10);
                    return;
                }
                manager.loadHandler ({type:domReady?'domready':'domfail'});

            };
            setTimeout(poll,50);
         }
 });

 Ext.ux.ManagedIFrame.Manager = function(){
  var frames = {};
  return {

    register     :function(frame){
        frame.manager = this;
        return frames[frame.id] = frame;
    },
    deRegister     :function(frame){
        if(frames[frame.id] )delete frames[frame.id];

    },
    hideDragMask : function(){
        if(!this.inDrag)return;
        Ext.select('.x-managed-iframe-mask',true).each(function(maskEl){
            maskEl.removeShim();
        });
        this.inDrag = false;
    },
    
    showDragMask : function(){
       if(!this.inDrag ){
          this.inDrag = true;
          Ext.select('.x-managed-iframe-mask',true).each(function(maskEl){
               maskEl.applyShim();
           });
       }

    }
   }

 }();

 
 Ext.ux.ManagedIframePanel = Ext.extend(Ext.Panel, {

    
    defaultSrc  :null,
    bodyStyle   :{height:'100%',width:'100%'},

    
    frameStyle  : false,
    loadMask    : false,
    animCollapse: false,
    autoScroll  : false,
    closable    : true, 
    ctype       : "Ext.ux.ManagedIframePanel",
    showLoadIndicator : false,

    
    unsupportedText : 'Inline frames are NOT enabled\/supported by your browser.'

   ,initComponent : function(){

        var unsup =this.unsupportedText?{html:this.unsupportedText}:false;
        this.frameConfig || (this.frameConfig = {autoCreate:{}});
        this.bodyCfg ||
           (this.bodyCfg =
               {tag:'div'
               ,cls:'x-panel-body'
               ,children:[
                  {  cls    :'x-managed-iframe-mask' 
                    ,children:[
                        Ext.apply(
                          Ext.apply({tag:'iframe',
                           frameborder  : 0,
                           cls          : 'x-managed-iframe',
                           style        : this.frameStyle || this.iframeStyle || false
                          },this.frameConfig.autoCreate)
                            ,unsup , Ext.isIE&&Ext.isSecure?{src:Ext.SSL_SECURE_URL}:false )
                         ]
                  }]
           });

         this.autoScroll = false; 

         
         if(this.stateful !== false){
             this.stateEvents || (this.stateEvents = ['documentloaded']);
         }

         Ext.ux.ManagedIframePanel.superclass.initComponent.call(this);

         this.monitorResize || (this.monitorResize = this.fitToParent);

         this.addEvents({documentloaded:true, domready:true,message:true,exception:true});

         
         this.addListener = this.on;

    },

    doLayout   :  function(){
        
        
        if(this.fitToParent && !this.ownerCt){
            var pos = this.getPosition(), size = (Ext.get(this.fitToParent)|| this.getEl().parent()).getViewSize();
            this.setSize(size.width - pos[0], size.height - pos[1]);
        }
        Ext.ux.ManagedIframePanel.superclass.doLayout.apply(this,arguments);

    },

      
    beforeDestroy : function(){

        if(this.rendered){

             if(this.tools){
                for(var k in this.tools){
                      Ext.destroy(this.tools[k]);
                }
             }

             if(this.header && this.headerAsText){
                var s;
                if( s=this.header.child('span')) s.remove();
                this.header.update('');
             }

             Ext.each(['iframe','header','topToolbar','bottomToolbar','footer','loadMask','body','bwrap'],
                function(elName){
                  if(this[elName]){
                    if(typeof this[elName].destroy == 'function'){
                         this[elName].destroy();
                    } else { Ext.destroy(this[elName]); }

                    this[elName] = null;
                    delete this[elName];
                  }
             },this);
        }

        Ext.ux.ManagedIframePanel.superclass.beforeDestroy.call(this);
    },
    onDestroy : function(){
        
        Ext.Panel.superclass.onDestroy.call(this);
    },
    
    onRender : function(ct, position){
        Ext.ux.ManagedIframePanel.superclass.onRender.call(this, ct, position);

        if(this.iframe = this.body.child('iframe.x-managed-iframe')){

            
            Ext.each(
                [this[this.collapseEl],this.el,this.iframe]
                ,function(el){
                     el.setVisibilityMode(Ext.Element[(this.hideMode||'display').toUpperCase()] || 1).originalDisplay = (this.hideMode != 'display'?'visible':'block');
            },this);

            if(this.loadMask){
                this.loadMask = Ext.apply({disabled     :false
                                          ,maskEl       :this.body
                                          ,hideOnReady  :true}
                                          ,this.loadMask);
             }

            if(this.iframe = new Ext.ux.ManagedIFrame(this.iframe, Ext.apply({
                    loadMask           :this.loadMask
                   ,showLoadIndicator  :this.showLoadIndicator
                   ,disableMessaging   :this.disableMessaging
                   },this.frameConfig))){

                this.loadMask = this.iframe.loadMask;
                this.iframe.ownerCt = this;
                this.relayEvents(this.iframe, ["documentloaded","domready","exception","message"].concat(this._msgTagHandlers ||[]));
                delete this._msgTagHandlers;
            }

            this.getUpdater().showLoadIndicator = this.showLoadIndicator || false;

            
            var ownerCt = this.ownerCt;
            while(ownerCt){

                ownerCt.on('afterlayout',function(container,layout){
                        var MIM = Ext.ux.ManagedIFrame.Manager,st=false;
                        Ext.each(['north','south','east','west'],function(region){
                            var reg;
                            if((reg = layout[region]) && reg.splitEl){
                                st = true;
                                if(!reg.split._splitTrapped){
                                    reg.split.on('beforeresize',MIM.showDragMask,MIM);
                                    reg.split._splitTrapped = true;
                                }
                            }
                        },this);
                        if(st && !this._splitTrapped ){
                            this.on('resize',MIM.hideDragMask,MIM);
                            this._splitTrapped = true;

                        }

                },this,{single:true});

                ownerCt = ownerCt.ownerCt; 
             }


        }
    },
        
    afterRender : function(container){
        var html = this.html;
        delete this.html;
        Ext.ux.ManagedIframePanel.superclass.afterRender.call(this);
        if(this.iframe){
            if(this.defaultSrc){
                this.setSrc();
            }
            else if(html){
                this.iframe.update(typeof html == 'object' ? Ext.DomHelper.markup(html) : html);
            }
        }

    }
    ,sendMessage :function (){
        if(this.iframe){
            this.iframe.sendMessage.apply(this.iframe,arguments);
        }

    }
    
    ,on : function(name){
           var tagRE=/^message\:/i, n = null;
           if(typeof name == 'object'){
               for (var na in name){
                   if(!this.filterOptRe.test(na) && tagRE.test(na)){
                      n || (n=[]);
                      n.push(na.toLowerCase());
                   }
               }
           } else if(tagRE.test(name)){
                  n=[name.toLowerCase()];
           }

           if(this.getFrame() && n){
               this.relayEvents(this.iframe,n);
           }else{
               this._msgTagHandlers || (this._msgTagHandlers =[]);
               if(n)this._msgTagHandlers = this._msgTagHandlers.concat(n); 
           }
           Ext.ux.ManagedIframePanel.superclass.on.apply(this, arguments);

    },

    
    setSrc : function(url, discardUrl,callback){
         url = url || this.defaultSrc || false;

         if(!url)return this;

         if(url.url){
            callback = url.callback || false;
            discardUrl = url.discardUrl || false;
            url = url.url || false;

         }
         var src = url || (Ext.isIE&&Ext.isSecure?Ext.SSL_SECURE_URL:'');

         if(this.rendered && this.iframe){

              this.iframe.setSrc(src,discardUrl,callback);
           }

         return this;
    },

    
    getState: function(){

         var URI = this.iframe?this.iframe.getDocumentURI()||null:null;
         return Ext.apply(Ext.ux.ManagedIframePanel.superclass.getState.call(this) || {},
             URI?{defaultSrc  : typeof f == 'function'?URI():URI}:null );

    },
    
    getUpdater : function(){
        return this.rendered?(this.iframe||this.body).getUpdater():null;
    },
    
    getFrame : function(){
        return this.rendered?this.iframe:null
    },
    
    getFrameWindow : function(){
        return this.rendered && this.iframe?this.iframe.getWindow():null
    },
    
    getFrameDocument : function(){
        return this.rendered && this.iframe?this.iframe.getDocument():null
    },
     
    load : function(loadCfg){
         var um;
         if(um = this.getUpdater()){
            if (loadCfg && loadCfg.renderer) {
                 um.setRenderer(loadCfg.renderer);
                 delete loadCfg.renderer;
            }
            um.update.apply(um, arguments);
         }
         return this;
    }
     
    ,doAutoLoad : function(){
        this.load(
            typeof this.autoLoad == 'object' ?
                this.autoLoad : {url: this.autoLoad});
    }
    
    ,onShow : function(){
        if(this.iframe)this.iframe.setVisible(true);
        Ext.ux.ManagedIframePanel.superclass.onShow.call(this);
    }

    
    ,onHide : function(){
        if(this.iframe)this.iframe.setVisible(false);
        Ext.ux.ManagedIframePanel.superclass.onHide.call(this);
    }
});

Ext.reg('iframepanel', Ext.ux.ManagedIframePanel);

Ext.ux.ManagedIframePortlet = Ext.extend(Ext.ux.ManagedIframePanel, {
     anchor: '100%',
     frame:true,
     collapseEl:'bwrap',
     collapsible:true,
     draggable:true,
     cls:'x-portlet'
 });
Ext.reg('iframeportlet', Ext.ux.ManagedIframePortlet);








if(!Ext.isArray) {
	Ext.isArray = function(v){
		return v && typeof v.pop == 'function';
	}
}


Ext.ux.IconMenu = function(config) {
	
	Ext.apply(this, config);

	
	Ext.ux.IconMenu.superclass.constructor.apply(this, arguments);

}; 

Ext.extend(Ext.ux.IconMenu, Ext.util.Observable, {

	
	
	
	 closeText:'Close'

	
	,createDefault:true

	
	,dblClickClose:true

	

	
	,defaultItems:['restore', 'minimize', 'maximize', 'separator', 'close']

	

	
	,maximizeText:'Maximize'
	
	

	
	,minimizeText:'Minimize'

	

	
	,restoreText:'Restore'

	
	,style:'width:16px;height:16px;left:0;top:4px;position:absolute;cursor:pointer'

	

	

	
	
	
	,closeHandler:function() {
		this.hideMenu();
		if(this.panel.closable) {
			this.panel[this.panel.closeAction]();
		}
	} 
	
	
	
	,getItemByCmd:function(cmd) {
		if(!this.menu || !cmd) {
			return null;
		}
		var item = this.menu.items.find(function(i) {
			return cmd === i.cmd;
		});
		return item;

	} 
	
	
	
	,hideMenu:function() {
		if(this.menu) {
			this.menu.hide();
		}
	} 
	
	
	
	,init:function(panel) {
		this.panel = panel;
		this.iconCls = this.iconCls || panel.iconCls;
		panel.on({scope:this
			,render:this.onRender
			,hide:this.hideMenu
			,destroy:this.onDestroy
		});
		
		var i, cfg, item;
		var menuId = 'im-' + panel.id;

		
		if(this.createDefault && Ext.isArray(this.defaultItems) && this.defaultItems.length) {
			if(this.menu) {
				this.menu.add('-');
			}
			else {
				this.menu = new Ext.menu.Menu({id:menuId});
			}
			for(i = 0; i < this.defaultItems.length; i++) {
				this.addItem(this.defaultItems[i]);
			}
		}

		
		if(Ext.isArray(this.customItems)) {
			if(!this.menu) {
				this.menu = new Ext.menu.Menu({id:menuId});
			}
			for(i = 0; i < this.customItems.length; i++) {
				this.addItem(this.customItems[i]);
			}
		}

		
		if(this.menu) {
			this.menu.id = this.menu.id || menuId;
			this.menu = Ext.menu.MenuMgr.get(this.menu);
			this.menu.on("show", this.onMenuShow, this);
			this.menu.on("hide", this.onMenuHide, this);
			this.menu.parentPanel = this.panel;
		}
	} 
	
	
	
	,addItem:function(item) {
		if('separator' === item || '-' === item) {
			this.menu.add('-');
			return;
		}
		if('string' === typeof item) {
			cfg = {
				 text:this[item + 'Text']
				,cmd:item
				,iconCls:'x-im-icon x-tool x-tool-' + item
				,scope:this
				,handler:this[item + 'Handler']
			};
		}
		else {
			cfg = item;
		}
		this.menu.add(cfg);
	} 
	
	
	
	,maximizeHandler:function() {
		this.hideMenu();
		if(this.panel.maximizable) {
			this.panel.maximize();
		}
	} 
	
	
	
	,minimizeHandler:function() {
		this.hideMenu();
		if(this.panel.minimizable) {
			this.panel.minimize();
		}
	} 
	
	
	
	,onDestroy:function() {
		if(this.menu) {
			this.menu.hide();
			this.menu.destroy();
		}
	} 
	
	
	
    ,onMenuHide:function(e){
        this.ignoreNextClick = this.restoreClick.defer(350, this);
    } 
	
	
	
    ,onMenuShow:function(e){
        this.ignoreNextClick = 0;
    } 
	
	
	
	,onRender:function() {

		var hd = this.panel.header;
		if(!hd) {
			return;
		}

		Ext.util.CSS.createStyleSheet('.x-im-icon{float:none!important;margin-left:0!important}');

		
		hd.addClass('x-panel-icon');
		hd.applyStyles({position:'relative'});

		
		this.icon = hd.insertFirst({
			 tag:'div'
			,id:Ext.id()
			,style:this.style
			,cls:this.iconCls
			,qtip:this.qtip || this.tooltip || ''
		}, 'first');

		
		var img = hd.down('img');
		if(img) {
			this.icon.alignTo(img, 'tl-tl');
			img.removeClass(this.panel.iconCls || this.iconCls);
			img.set({src:Ext.BLANK_IMAGE_URL});
		}

		
		this.icon.on({
			 scope:this
			,dblclick:function() {
				if(this.dblClickClose) {
					this.closeHandler();
				}
			}
			,click:{scope:this, delay:200, fn:function(e, t) {
				if(this.menu && !this.menu.isVisible() && !this.ignoreNextClick) {
					this.showMenu();
				}
			}}
		});

		
		this.panel.setIconClass = this.setIconClass.createDelegate(this);

	} 
	
	
	
    ,restoreClick:function(){
        this.ignoreNextClick = 0;
    } 
	
	
	
	,restoreHandler:function() {
		this.hideMenu();
		this.panel.restore();
	} 
	
	
	
	,setIconClass:function(iconCls) {
		this.icon.replaceClass(this.iconCls, iconCls);
	} 
	
	
	
	,showMenu:function() {
		var item;
		if(this.menu) {
			try {
				
				item = this.getItemByCmd('close');
				if(item) {
					item.setDisabled(!this.panel.closable);
				}

				
				item = this.getItemByCmd('maximize');
				if(item) {
					item.setDisabled(!this.panel.maximizable || this.panel.maximized);
				}

				
				item = this.getItemByCmd('minimize');
				if(item) {
					item.setDisabled(!this.panel.minimizable || this.panel.minimized);
				}

				
				item = this.getItemByCmd('restore');
				if(item) {
					item.setDisabled(!(this.panel.minimized || this.panel.maximized));
				}

				
				this.menu.show(this.icon, 'tl-bl?');
			} catch(e){}
		}
	}
	

}); 

Ext.reg('iconmenu', Ext.ux.IconMenu);




Ext.namespace('Ext.ux.Utils');


Ext.ux.Utils.EventQueue = function(handler, scope)
{
  if (!handler) {
    throw 'Handler is required.';
  }
  this.handler = handler;
  this.scope = scope || window;
  this.queue = [];
  this.is_processing = false;
  
  
  this.postEvent = function(event, data)
  {
    data = data || null;
    this.queue.push({event: event, data: data});
    if (!this.is_processing) {
      this.process();
    }
  }
  
  this.flushEventQueue = function()
  {
    this.queue = [];
  },
  
  
  this.process = function()
  {
    while (this.queue.length > 0) {
      this.is_processing = true;
      var event_data = this.queue.shift();
      this.handler.call(this.scope, event_data.event, event_data.data);
    }
    this.is_processing = false;
  }
}


Ext.ux.Utils.FSA = function(initial_state, trans_table, trans_table_scope)
{
  this.current_state = initial_state;
  this.trans_table = trans_table || {};
  this.trans_table_scope = trans_table_scope || window;
  Ext.ux.Utils.FSA.superclass.constructor.call(this, this.processEvent, this);
}

Ext.extend(Ext.ux.Utils.FSA, Ext.ux.Utils.EventQueue, {

  current_state : null,
  trans_table : null,  
  trans_table_scope : null,
  
  
  state : function()
  {
    return this.current_state;
  },
  
  
  processEvent : function(event, data)
  {
    var transitions = this.currentStateEventTransitions(event);
    if (!transitions) {
      throw "State '" + this.current_state + "' has no transition for event '" + event + "'.";
    }
    for (var i = 0, len = transitions.length; i < len; i++) {
      var transition = transitions[i];

      var predicate = transition.predicate || transition.p || true;
      var action = transition.action || transition.a || Ext.emptyFn;
      var new_state = transition.state || transition.s || this.current_state;
      var scope = transition.scope || this.trans_table_scope;
      
      if (this.computePredicate(predicate, scope, data, event)) {
        this.callAction(action, scope, data, event);
        this.current_state = new_state; 
        return;
      }
    }
    
    throw "State '" + this.current_state + "' has no transition for event '" + event + "' in current context";
  },
  
  
  currentStateEventTransitions : function(event)
  {
    return this.trans_table[this.current_state] ? 
      this.trans_table[this.current_state][event] || false
      :
      false;
  },
  
  
  computePredicate : function(predicate, scope, data, event)
  {
    var result = false; 
    
    switch (Ext.type(predicate)) {
     case 'function':
       result = predicate.call(scope, data, event, this);
       break;
     case 'array':
       result = true;
       for (var i = 0, len = predicate.length; result && (i < len); i++) {
         if (Ext.type(predicate[i]) == 'function') {
           result = predicate[i].call(scope, data, event, this);
         }
         else {
           throw [
             'Predicate: ',
             predicate[i],
             ' is not callable in "',
             this.current_state,
             '" state for event "',
             event
           ].join('');
         }
       }
       break;
     case 'boolean':
       result = predicate;
       break;
     default:
       throw [
         'Predicate: ',
         predicate,
         ' is not callable in "',
         this.current_state,
         '" state for event "',
         event
       ].join('');
    }
    return result;
  },
  
  
  callAction : function(action, scope, data, event)
  {
    switch (Ext.type(action)) {
       case 'array':
       for (var i = 0, len = action.length; i < len; i++) {
         if (Ext.type(action[i]) == 'function') {
           action[i].call(scope, data, event, this);
         }
         else {
           throw [
             'Action: ',
             action[i],
             ' is not callable in "',
             this.current_state,
             '" state for event "',
             event
           ].join('');
         }
       }
         break;
     case 'function':
       action.call(scope, data, event, this);
       break;
     default:
       throw [
         'Action: ',
         action,
         ' is not callable in "',
         this.current_state,
         '" state for event "',
         event
       ].join('');
    }
  }
});




Ext.namespace('Ext.ux.UploadDialog');

 
Ext.ux.UploadDialog.BrowseButton = Ext.extend(Ext.Button, 
{
  input_name : 'file',
  
  input_file : null,
  
  original_handler : null,
  
  original_scope : null,
  
  
  initComponent : function()
  {
    Ext.ux.UploadDialog.BrowseButton.superclass.initComponent.call(this);
    this.original_handler = this.handler || null;
    this.original_scope = this.scope || window;
    this.handler = null;
    this.scope = null;
  },
  
  
  onRender : function(ct, position)
  {
    Ext.ux.UploadDialog.BrowseButton.superclass.onRender.call(this, ct, position);
    this.createInputFile();
  },
  
  
  createInputFile : function()
  {
    var button_container = this.el.child('.x-btn-center');
    button_container.position('relative');
    this.input_file = Ext.DomHelper.append(
      button_container, 
      {
        tag: 'input',
        type: 'file',
        size: 1,
        name: this.input_name || Ext.id(this.el),
        style: 'position: absolute; display: block; border: none; cursor: pointer'
      },
      true
    );
    
    var button_box = button_container.getBox();
    this.input_file.setStyle('font-size', (button_box.width * 0.5) + 'px');

    var input_box = this.input_file.getBox();
    var adj = {x: 3, y: 3}
    if (Ext.isIE) {
      adj = {x: 0, y: 3}
    }
    
    this.input_file.setLeft(button_box.width - input_box.width + adj.x + 'px');
    this.input_file.setTop(button_box.height - input_box.height + adj.y + 'px');
    this.input_file.setOpacity(0.0);
        
    if (this.handleMouseEvents) {
      this.input_file.on('mouseover', this.onMouseOver, this);
        this.input_file.on('mousedown', this.onMouseDown, this);
    }
    
    if(this.tooltip){
      if(typeof this.tooltip == 'object'){
        Ext.QuickTips.register(Ext.apply({target: this.input_file}, this.tooltip));
      } 
      else {
        this.input_file.dom[this.tooltipType] = this.tooltip;
        }
      }
    
    this.input_file.on('change', this.onInputFileChange, this);
    this.input_file.on('click', function(e) { e.stopPropagation(); }); 
  },
  
  
  detachInputFile : function(no_create)
  {
    var result = this.input_file;
    
    no_create = no_create || false;
    
    if (typeof this.tooltip == 'object') {
      Ext.QuickTips.unregister(this.input_file);
    }
    else {
      this.input_file.dom[this.tooltipType] = null;
    }
    this.input_file.removeAllListeners();
    this.input_file = null;
    
    if (!no_create) {
      this.createInputFile();
    }
    return result;
  },
  
  
  getInputFile : function()
  {
    return this.input_file;
  },
  
  
  disable : function()
  {
    Ext.ux.UploadDialog.BrowseButton.superclass.disable.call(this);  
    this.input_file.dom.disabled = true;
  },
  
  
  enable : function()
  {
    Ext.ux.UploadDialog.BrowseButton.superclass.enable.call(this);
    this.input_file.dom.disabled = false;
  },
  
  
  destroy : function()
  {
    var input_file = this.detachInputFile(true);
    input_file.remove();
    input_file = null;
    Ext.ux.UploadDialog.BrowseButton.superclass.destroy.call(this);      
  },
  
  
  onInputFileChange : function()
  {
    if (this.original_handler) {
      this.original_handler.call(this.original_scope, this);
    }
  }  
});


Ext.ux.UploadDialog.TBBrowseButton = Ext.extend(Ext.ux.UploadDialog.BrowseButton, 
{
  hideParent : true,

  onDestroy : function()
  {
    Ext.ux.UploadDialog.TBBrowseButton.superclass.onDestroy.call(this);
    if(this.container) {
      this.container.remove();
      }
  }
});


Ext.ux.UploadDialog.FileRecord = Ext.data.Record.create([
  {name: 'filename'},
  {name: 'state', type: 'int'},
  {name: 'note'},
  {name: 'input_element'}
]);

Ext.ux.UploadDialog.FileRecord.STATE_QUEUE = 0;
Ext.ux.UploadDialog.FileRecord.STATE_FINISHED = 1;
Ext.ux.UploadDialog.FileRecord.STATE_FAILED = 2;
Ext.ux.UploadDialog.FileRecord.STATE_PROCESSING = 3;


Ext.ux.UploadDialog.Dialog = function(config)
{
  var default_config = {
    border: false,
    width: 450,
    height: 300,
    minWidth: 450,
    minHeight: 300,
    plain: true,
    constrainHeader: true,
    draggable: true,
    closable: true,
    maximizable: false,
    minimizable: false,
    resizable: true,
    autoDestroy: true,
    closeAction: 'hide',
    plugins:[new Ext.ux.IconMenu({})],
    title: this.i18n.title,
    cls: 'ext-ux-uploaddialog-dialog',
    
    url: '',
    base_params: {},
    permitted_extensions: [],
    reset_on_hide: true,
    allow_close_on_upload: false,
    upload_autostart: false,
    post_var_name: 'file'
  }
  config = Ext.applyIf(config || {}, default_config);
  config.layout = 'absolute';
  
  Ext.ux.UploadDialog.Dialog.superclass.constructor.call(this, config);
}

Ext.extend(Ext.ux.UploadDialog.Dialog, Ext.Window, {

  fsa : null,
  
  state_tpl : null,
  
  form : null,
  
  grid_panel : null,
  
  progress_bar : null,
  
  is_uploading : false,
  
  initial_queued_count : 0,
  
  upload_frame : null,
  
  
  
  initComponent : function()
  {
    Ext.ux.UploadDialog.Dialog.superclass.initComponent.call(this);
    
    
    var tt = {
      
      'created' : {
      
        'window-render' : [
          {
            action: [this.createForm, this.createProgressBar, this.createGrid],
            state: 'rendering'
          }
        ],
        'destroy' : [
          {
            action: this.flushEventQueue,
            state: 'destroyed'
          }
        ]
      },
      
      'rendering' : {
      
        'grid-render' : [
          {
            action: [this.fillToolbar, this.updateToolbar],
            state: 'ready'
          }
        ],
        'destroy' : [
          {
            action: this.flushEventQueue,
            state: 'destroyed'
          }
        ]
      },
      
      'ready' : {
      
        'file-selected' : [
          {
            predicate: [this.fireFileTestEvent, this.isPermittedFile],
            action: this.addFileToUploadQueue,
            state: 'adding-file'
          },
          {
            
          }
        ],
        'grid-selection-change' : [
          {
            action: this.updateToolbar
          }
        ],
        'remove-files' : [
          {
            action: [this.removeFiles, this.fireFileRemoveEvent]
          }
        ],
        'reset-queue' : [
          {
            action: [this.resetQueue, this.fireResetQueueEvent]
          }
        ],
        'start-upload' : [
          {
            predicate: this.hasUnuploadedFiles,
            action: [
              this.setUploadingFlag, this.saveInitialQueuedCount, this.updateToolbar, 
              this.updateProgressBar, this.prepareNextUploadTask, this.fireUploadStartEvent
            ],
            state: 'uploading'
          },
          {
            
          }
        ],
        'stop-upload' : [
          {
            
          }
        ],
        'hide' : [
          {
            predicate: [this.isNotEmptyQueue, this.getResetOnHide],
            action: [this.resetQueue, this.fireResetQueueEvent]
          },
          {
            
          }
        ],
        'destroy' : [
          {
            action: this.flushEventQueue,
            state: 'destroyed'
          }
        ]
      },
      
      'adding-file' : {
      
        'file-added' : [
          {
            predicate: this.isUploading,
            action: [this.incInitialQueuedCount, this.updateProgressBar, this.fireFileAddEvent],
            state: 'uploading' 
          },
          {
            predicate: this.getUploadAutostart,
            action: [this.startUpload, this.fireFileAddEvent],
            state: 'ready'
          },
          {
            action: [this.updateToolbar, this.fireFileAddEvent],
            state: 'ready'
          }
        ]
      },
      
      'uploading' : {
      
        'file-selected' : [
          {
            predicate: [this.fireFileTestEvent, this.isPermittedFile],
            action: this.addFileToUploadQueue,
            state: 'adding-file'
          },
          {
            
          }
        ],
        'grid-selection-change' : [
          {
            
          }
        ],
        'start-upload' : [
          {
            
          }
        ],
        'stop-upload' : [
          {
            predicate: this.hasUnuploadedFiles,
            action: [
              this.resetUploadingFlag, this.abortUpload, this.updateToolbar, 
              this.updateProgressBar, this.fireUploadStopEvent
            ],
            state: 'ready'
          },
          {
            action: [
              this.resetUploadingFlag, this.abortUpload, this.updateToolbar, 
              this.updateProgressBar, this.fireUploadStopEvent, this.fireUploadCompleteEvent
            ],
            state: 'ready'
          }
        ],
        'file-upload-start' : [
          {
            action: [this.uploadFile, this.findUploadFrame, this.fireFileUploadStartEvent]
          }
        ],
        'file-upload-success' : [
          {
            predicate: this.hasUnuploadedFiles,
            action: [
              this.resetUploadFrame, this.updateRecordState, this.updateProgressBar, 
              this.prepareNextUploadTask, this.fireUploadSuccessEvent
            ]
          },
          {
            action: [
              this.resetUploadFrame, this.resetUploadingFlag, this.updateRecordState, 
              this.updateToolbar, this.updateProgressBar, this.fireUploadSuccessEvent, 
              this.fireUploadCompleteEvent
            ],
            state: 'ready'
          }
        ],
        'file-upload-error' : [
          {
            predicate: this.hasUnuploadedFiles,
            action: [
              this.resetUploadFrame, this.updateRecordState, this.updateProgressBar, 
              this.prepareNextUploadTask, this.fireUploadErrorEvent
            ]
          },
          {
            action: [
              this.resetUploadFrame, this.resetUploadingFlag, this.updateRecordState, 
              this.updateToolbar, this.updateProgressBar, this.fireUploadErrorEvent, 
              this.fireUploadCompleteEvent
            ],
            state: 'ready'
          }
        ],
        'file-upload-failed' : [
          {
            predicate: this.hasUnuploadedFiles,
            action: [
              this.resetUploadFrame, this.updateRecordState, this.updateProgressBar, 
              this.prepareNextUploadTask, this.fireUploadFailedEvent
            ]
          },
          {
            action: [
              this.resetUploadFrame, this.resetUploadingFlag, this.updateRecordState, 
              this.updateToolbar, this.updateProgressBar, this.fireUploadFailedEvent, 
              this.fireUploadCompleteEvent
            ],
            state: 'ready'
          }
        ],
        'hide' : [
          {
            predicate: this.getResetOnHide,
            action: [this.stopUpload, this.repostHide]
          },
          {
            
          }
        ],
        'destroy' : [
          {
            predicate: this.hasUnuploadedFiles,
            action: [
              this.resetUploadingFlag, this.abortUpload,
              this.fireUploadStopEvent, this.flushEventQueue
            ],
            state: 'destroyed'
          },
          {
            action: [
              this.resetUploadingFlag, this.abortUpload,
              this.fireUploadStopEvent, this.fireUploadCompleteEvent, this.flushEventQueue
            ], 
            state: 'destroyed'
          }
        ]
      },
      
      'destroyed' : {
      
      }
    }
    this.fsa = new Ext.ux.Utils.FSA('created', tt, this);
    
    
    this.addEvents({
      'filetest': true,
      'fileadd' : true,
      'fileremove' : true,
      'resetqueue' : true,
      'uploadsuccess' : true,
      'uploaderror' : true,
      'uploadfailed' : true,
      'uploadstart' : true,
      'uploadstop' : true,
      'uploadcomplete' : true,
      'fileuploadstart' : true
    });
    
    
    this.on('render', this.onWindowRender, this);
    this.on('beforehide', this.onWindowBeforeHide, this);
    this.on('hide', this.onWindowHide, this);
    this.on('destroy', this.onWindowDestroy, this);
    
    
    this.state_tpl = new Ext.Template(
      "<div class='ext-ux-uploaddialog-state ext-ux-uploaddialog-state-{state}'>&#160;</div>"
    ).compile();
  },
  
  createForm : function()
  {
    this.form = Ext.DomHelper.append(this.body, {
      tag: 'form',
      method: 'post',
      action: this.url,
      style: 'position: absolute; left: -100px; top: -100px; width: 100px; height: 100px'
    });
  },
  
  createProgressBar : function()
  {
    this.progress_bar = this.add(
      new Ext.ProgressBar({
        x: 0,
        y: 0,
        anchor: '0',
        value: 0.0,
        text: this.i18n.progress_waiting_text
      })
    );
  },
  
  createGrid : function()
  {
    var store = new Ext.data.Store({
      proxy: new Ext.data.MemoryProxy([]),
      reader: new Ext.data.JsonReader({}, Ext.ux.UploadDialog.FileRecord),
      sortInfo: {field: 'state', direction: 'DESC'},
      pruneModifiedRecords: true
    });
    
    var cm = new Ext.grid.ColumnModel([
      {
        header: this.i18n.state_col_title,
        width: this.i18n.state_col_width,
        resizable: false,
        dataIndex: 'state',
        sortable: true,
        renderer: this.renderStateCell.createDelegate(this)
      },
      {
        header: this.i18n.filename_col_title,
        width: this.i18n.filename_col_width,
        dataIndex: 'filename',
        sortable: true,
        renderer: this.renderFilenameCell.createDelegate(this)
      },
      {
        header: this.i18n.note_col_title,
        width: this.i18n.note_col_width, 
        dataIndex: 'note',
        sortable: true,
        renderer: this.renderNoteCell.createDelegate(this)
      }
    ]);
  
      this.grid_panel = new Ext.grid.GridPanel({
      ds: store,
      cm: cm,
    
      x: 0,
      y: 22,
      anchor: '0 -22',
      border: true,
      
        viewConfig: {
        autoFill: true,
          forceFit: true
        },
      
      bbar : new Ext.Toolbar()
    });
    this.grid_panel.on('render', this.onGridRender, this);
    
    this.add(this.grid_panel);
    
    this.grid_panel.getSelectionModel().on('selectionchange', this.onGridSelectionChange, this);
  },
  
  fillToolbar : function()
  {
    var tb = this.grid_panel.getBottomToolbar();
    tb.x_buttons = {}
    
    tb.x_buttons.add = tb.addItem(new Ext.ux.UploadDialog.TBBrowseButton({
      input_name: this.post_var_name,
      text: this.i18n.add_btn_text,
      tooltip: this.i18n.add_btn_tip,
      iconCls: 'ext-ux-uploaddialog-addbtn',
      handler: this.onAddButtonFileSelected,
      scope: this
    }));
    
    tb.x_buttons.remove = tb.addButton({
      text: this.i18n.remove_btn_text,
      tooltip: this.i18n.remove_btn_tip,
      iconCls: 'ext-ux-uploaddialog-removebtn',
      handler: this.onRemoveButtonClick,
      scope: this
    });
    
    tb.x_buttons.reset = tb.addButton({
      text: this.i18n.reset_btn_text,
      tooltip: this.i18n.reset_btn_tip,
      iconCls: 'ext-ux-uploaddialog-resetbtn',
      handler: this.onResetButtonClick,
      scope: this
    });
    
    tb.add('-');
    
    tb.x_buttons.upload = tb.addButton({
      text: this.i18n.upload_btn_start_text,
      tooltip: this.i18n.upload_btn_start_tip,
      iconCls: 'ext-ux-uploaddialog-uploadstartbtn',
      handler: this.onUploadButtonClick,
      scope: this
    });
    
    tb.add('-');
    
    tb.x_buttons.indicator = tb.addItem(
      new Ext.Toolbar.Item(
        Ext.DomHelper.append(tb.getEl(), {
          tag: 'div',
          cls: 'ext-ux-uploaddialog-indicator-stoped',
          html: '&#160'
        })
      )
    );
    
    tb.add('->');
    
    tb.x_buttons.close = tb.addButton({
      text: this.i18n.close_btn_text,
      tooltip: this.i18n.close_btn_tip,
      handler: this.onCloseButtonClick,
      scope: this
    });
  },
  
  renderStateCell : function(data, cell, record, row_index, column_index, store)
  {
    return this.state_tpl.apply({state: data});
  },
  
  renderFilenameCell : function(data, cell, record, row_index, column_index, store)
  {
    var view = this.grid_panel.getView();
    var f = function() {
      try {
        Ext.fly(
          view.getCell(row_index, column_index)
        ).child('.x-grid3-cell-inner').dom['qtip'] = data;
      }
      catch (e)
      {}
    }
    f.defer(1000);
    return data;
  },
  
  renderNoteCell : function(data, cell, record, row_index, column_index, store)
  {
    var view = this.grid_panel.getView();
    var f = function() {
      try {
        Ext.fly(
          view.getCell(row_index, column_index)
        ).child('.x-grid3-cell-inner').dom['qtip'] = data;
      }
      catch (e)
      {}
      }
    f.defer(1000);
    return data;
  },
  
  getFileExtension : function(filename)
  {
    var result = null;
    var parts = filename.split('.');
    if (parts.length > 1) {
      result = parts.pop();
    }
    return result;
  },
  
  isPermittedFileType : function(filename)
  {
    var result = true;
    if (this.permitted_extensions.length > 0) {
      result = this.permitted_extensions.indexOf(this.getFileExtension(filename)) != -1;
    }
    return result;
  },

  isPermittedFile : function(browse_btn)
  {
    var result = false;
    var filename = browse_btn.getInputFile().dom.value;
    
    if (this.isPermittedFileType(filename)) {
      result = true;
    }
    else {
      Ext.Msg.alert(
        this.i18n.error_msgbox_title, 
        String.format(
          this.i18n.err_file_type_not_permitted,
          filename,
          this.permitted_extensions.join(this.i18n.permitted_extensions_join_str)
        )
      );
      result = false;
    }
    
    return result;
  },
  
  fireFileTestEvent : function(browse_btn)
  {
    return this.fireEvent('filetest', this, browse_btn.getInputFile().dom.value) !== false;
  },
  
  addFileToUploadQueue : function(browse_btn)
  {
    var input_file = browse_btn.detachInputFile();
    
    input_file.appendTo(this.form);
    input_file.setStyle('width', '100px');
    input_file.dom.disabled = true;
    
    var store = this.grid_panel.getStore();
    store.add(
      new Ext.ux.UploadDialog.FileRecord({
          state: Ext.ux.UploadDialog.FileRecord.STATE_QUEUE,
          filename: input_file.dom.value,
          note: this.i18n.note_queued_to_upload,
          input_element: input_file
        })
      );
    this.fsa.postEvent('file-added', input_file.dom.value);
  },
  
  fireFileAddEvent : function(filename)
  {
    this.fireEvent('fileadd', this, filename);
  },
  
  updateProgressBar : function()
  {
    if (this.is_uploading) {
      var queued = this.getQueuedCount(true);
      var value = 1 - queued / this.initial_queued_count;
      this.progress_bar.updateProgress(
        value,
        String.format(
          this.i18n.progress_uploading_text, 
          this.initial_queued_count - queued,
          this.initial_queued_count
        )
      );
    }
    else {
      this.progress_bar.updateProgress(0, this.i18n.progress_waiting_text);
    }
  },
  
  updateToolbar : function()
  {
    var tb = this.grid_panel.getBottomToolbar();
    if (this.is_uploading) {
      tb.x_buttons.remove.disable();
      tb.x_buttons.reset.disable();
      tb.x_buttons.upload.enable();
      if (!this.getAllowCloseOnUpload()) {
        tb.x_buttons.close.disable();
      }
      Ext.fly(tb.x_buttons.indicator.getEl()).replaceClass(
        'ext-ux-uploaddialog-indicator-stoped',
        'ext-ux-uploaddialog-indicator-processing'
      );
      tb.x_buttons.upload.setIconClass('ext-ux-uploaddialog-uploadstopbtn');
      tb.x_buttons.upload.setText(this.i18n.upload_btn_stop_text);
      tb.x_buttons.upload.getEl()
        .child(tb.x_buttons.upload.buttonSelector)
        .dom[tb.x_buttons.upload.tooltipType] = this.i18n.upload_btn_stop_tip;
    }
    else {
      tb.x_buttons.remove.enable();
      tb.x_buttons.reset.enable();
      tb.x_buttons.close.enable();
      Ext.fly(tb.x_buttons.indicator.getEl()).replaceClass(
        'ext-ux-uploaddialog-indicator-processing',
        'ext-ux-uploaddialog-indicator-stoped'
      );
      tb.x_buttons.upload.setIconClass('ext-ux-uploaddialog-uploadstartbtn');
      tb.x_buttons.upload.setText(this.i18n.upload_btn_start_text);
      tb.x_buttons.upload.getEl()
        .child(tb.x_buttons.upload.buttonSelector)
        .dom[tb.x_buttons.upload.tooltipType] = this.i18n.upload_btn_start_tip;
      
      if (this.getQueuedCount() > 0) {
        tb.x_buttons.upload.enable();
      }
      else {
        tb.x_buttons.upload.disable();      
      }
      
      if (this.grid_panel.getSelectionModel().hasSelection()) {
        tb.x_buttons.remove.enable();
      }
      else {
        tb.x_buttons.remove.disable();
      }
      
      if (this.grid_panel.getStore().getCount() > 0) {
        tb.x_buttons.reset.enable();
      }
      else {
        tb.x_buttons.reset.disable();
      }
    }
  },
  
  saveInitialQueuedCount : function()
  {
    this.initial_queued_count = this.getQueuedCount();
  },
  
  incInitialQueuedCount : function()
  {
    this.initial_queued_count++;
  },
  
  setUploadingFlag : function()
  {
    this.is_uploading = true;
  }, 
  
  resetUploadingFlag : function()
  {
    this.is_uploading = false;
  },

  prepareNextUploadTask : function()
  {
    
    var store = this.grid_panel.getStore();
    var record = null;
    
    store.each(function(r) {
      if (!record && r.get('state') == Ext.ux.UploadDialog.FileRecord.STATE_QUEUE) {
        record = r;
      }
      else {
        r.get('input_element').dom.disabled = true;
      }
    });
    
    record.get('input_element').dom.disabled = false;
    record.set('state', Ext.ux.UploadDialog.FileRecord.STATE_PROCESSING);
    record.set('note', this.i18n.note_processing);
    record.commit();
    
    this.fsa.postEvent('file-upload-start', record);
  },
   
  fireUploadStartEvent : function()
  {
    this.fireEvent('uploadstart', this);
  },
  
  removeFiles : function(file_records)
  {
    var store = this.grid_panel.getStore();
    for (var i = 0, len = file_records.length; i < len; i++) {
      var r = file_records[i];
      r.get('input_element').remove();
      store.remove(r);
    }
  },
  
  fireFileRemoveEvent : function(file_records)
  {
    for (var i = 0, len = file_records.length; i < len; i++) {
      this.fireEvent('fileremove', this, file_records[i].get('filename'));
    }
  },
  
  resetQueue : function()
  {
    var store = this.grid_panel.getStore();
    store.each(
      function(r) {
        r.get('input_element').remove();
      }
    );
    store.removeAll();
  },
  
  fireResetQueueEvent : function()
  {
    this.fireEvent('resetqueue', this);
  },
  
  uploadFile : function(record)
  {
    Ext.Ajax.request({
      url : this.url,
      params : this.base_params || this.baseParams || this.params,
      method : 'POST',
      form : this.form,
      isUpload : true,
      success : this.onAjaxSuccess,
      failure : this.onAjaxFailure,
      scope : this,
      record: record
    });
  },
   
  fireFileUploadStartEvent : function(record)
  {
    this.fireEvent('fileuploadstart', this, record.get('filename'));
  },
  
  updateRecordState : function(data)
  {
    if ('success' in data.response && data.response.success) {
      data.record.set('state', Ext.ux.UploadDialog.FileRecord.STATE_FINISHED);
      data.record.set(
        'note', data.response.message || data.response.error || this.i18n.note_upload_success
      );
    }
    else {
      data.record.set('state', Ext.ux.UploadDialog.FileRecord.STATE_FAILED);
      data.record.set(
        'note', data.response.message || data.response.error || this.i18n.note_upload_error
      );
    }
    
    data.record.commit();
  },
  
  fireUploadSuccessEvent : function(data)
  {
    this.fireEvent('uploadsuccess', this, data.record.get('filename'), data.response);
  },
  
  fireUploadErrorEvent : function(data)
  {
    this.fireEvent('uploaderror', this, data.record.get('filename'), data.response);
  },
  
  fireUploadFailedEvent : function(data)
  {
    this.fireEvent('uploadfailed', this, data.record.get('filename'));
  },
  
  fireUploadCompleteEvent : function()
  {
    this.fireEvent('uploadcomplete', this);
  },
  
  findUploadFrame : function() 
  {
    this.upload_frame = Ext.getBody().child('iframe.x-hidden:last');
  },
  
  resetUploadFrame : function()
  {
    this.upload_frame = null;
  },
  
  removeUploadFrame : function()
  {
    if (this.upload_frame) {
      this.upload_frame.removeAllListeners();
      this.upload_frame.dom.src = 'about:blank';
      this.upload_frame.remove();
    }
    this.upload_frame = null;
  },
  
  abortUpload : function()
  {
    this.removeUploadFrame();
    
    var store = this.grid_panel.getStore();
    var record = null;
    store.each(function(r) {
      if (r.get('state') == Ext.ux.UploadDialog.FileRecord.STATE_PROCESSING) {
        record = r;
        return false;
      }
    });
    
    record.set('state', Ext.ux.UploadDialog.FileRecord.STATE_FAILED);
    record.set('note', this.i18n.note_aborted);
    record.commit();
  },
  
  fireUploadStopEvent : function()
  {
    this.fireEvent('uploadstop', this);
  },
  
  repostHide : function()
  {
    this.fsa.postEvent('hide');
  },
  
  flushEventQueue : function()
  {
    this.fsa.flushEventQueue();
  },
  
  
  
  onWindowRender : function()
  {
    this.fsa.postEvent('window-render');
  },
  
  onWindowBeforeHide : function()
  {
    return this.isUploading() ? this.getAllowCloseOnUpload() : true;
  },
  
  onWindowHide : function()
  {
    this.fsa.postEvent('hide');
  },
  
  onWindowDestroy : function()
  {
    this.fsa.postEvent('destroy');
  },
  
  onGridRender : function()
  {
    this.fsa.postEvent('grid-render');
  },
  
  onGridSelectionChange : function()
  {
    this.fsa.postEvent('grid-selection-change');
  },
  
  onAddButtonFileSelected : function(btn)
  {
    this.fsa.postEvent('file-selected', btn);
  },
  
  onUploadButtonClick : function()
  {
    if (this.is_uploading) {
      this.fsa.postEvent('stop-upload');
    }
    else {
      this.fsa.postEvent('start-upload');
    }
  },
  
  onRemoveButtonClick : function()
  {
    var selections = this.grid_panel.getSelectionModel().getSelections();
    this.fsa.postEvent('remove-files', selections);
  },
  
  onResetButtonClick : function()
  {
    this.fsa.postEvent('reset-queue');
  },
  
  onCloseButtonClick : function()
  {
    this[this.closeAction].call(this);
  },
  
  onAjaxSuccess : function(response, options)
  {
    var json_response = {
      'success' : false,
      'error' : this.i18n.note_upload_error
    }
    try { 
        var rt = response.responseText;
        var filter = rt.match(/^<pre>((?:.|\n)*)<\/pre>$/i);
        if (filter) {
            rt = filter[1];
        }
        json_response = Ext.util.JSON.decode(rt); 
    } 
    catch (e) {}
    
    var data = {
      record: options.record,
      response: json_response
    }
    
    if ('success' in json_response && json_response.success) {
      this.fsa.postEvent('file-upload-success', data);
    }
    else {
      this.fsa.postEvent('file-upload-error', data);
    }
  },
  
  onAjaxFailure : function(response, options)
  {
    var data = {
      record : options.record,
      response : {
        'success' : false,
        'error' : this.i18n.note_upload_failed
      }
    }

    this.fsa.postEvent('file-upload-failed', data);
  },
  
  
  
  startUpload : function()
  {
    this.fsa.postEvent('start-upload');
  },
  
  stopUpload : function()
  {
    this.fsa.postEvent('stop-upload');
  },
  
  getUrl : function()
  {
    return this.url;
  },
  
  setUrl : function(url)
  {
    this.url = url;
  },
  
  getBaseParams : function()
  {
    return this.base_params;
  },
  
  setBaseParams : function(params)
  {
    this.base_params = params;
  },
  
  getUploadAutostart : function()
  {
    return this.upload_autostart;
  },
  
  setUploadAutostart : function(value)
  {
    this.upload_autostart = value;
  },
  
  getAllowCloseOnUpload : function()
  {
    return this.allow_close_on_upload;
  },
  
  setAllowCloseOnUpload : function(value)
  {
    this.allow_close_on_upload;
  },
  
  getResetOnHide : function()
  {
    return this.reset_on_hide;
  },
  
  setResetOnHide : function(value)
  {
    this.reset_on_hide = value;
  },
  
  getPermittedExtensions : function()
  {
    return this.permitted_extensions;
  },
  
  setPermittedExtensions : function(value)
  {
    this.permitted_extensions = value;
  },
  
  isUploading : function()
  {
    return this.is_uploading;
  },
  
  isNotEmptyQueue : function()
  {
    return this.grid_panel.getStore().getCount() > 0;
  },
  
  getQueuedCount : function(count_processing)
  {
    var count = 0;
    var store = this.grid_panel.getStore();
    store.each(function(r) {
      if (r.get('state') == Ext.ux.UploadDialog.FileRecord.STATE_QUEUE) {
        count++;
      }
      if (count_processing && r.get('state') == Ext.ux.UploadDialog.FileRecord.STATE_PROCESSING) {
        count++;
      }
    });
    return count;
  },
  
  hasUnuploadedFiles : function()
  {
    return this.getQueuedCount() > 0;
  }
});



var p = Ext.ux.UploadDialog.Dialog.prototype;
p.i18n = {
  title: 'File upload dialog',
  state_col_title: 'State',
  state_col_width: 70,
  filename_col_title: 'Filename',
  filename_col_width: 230,  
  note_col_title: 'Note',
  note_col_width: 150,
  add_btn_text: 'Add',
  add_btn_tip: 'Add file into upload queue.',
  remove_btn_text: 'Remove',
  remove_btn_tip: 'Remove file from upload queue.',
  reset_btn_text: 'Reset',
  reset_btn_tip: 'Reset queue.',
  upload_btn_start_text: 'Upload',
  upload_btn_stop_text: 'Abort',
  upload_btn_start_tip: 'Upload queued files to the server.',
  upload_btn_stop_tip: 'Stop upload.',
  close_btn_text: 'Close',
  close_btn_tip: 'Close the dialog.',
  progress_waiting_text: 'Waiting...',
  progress_uploading_text: 'Uploading: {0} of {1} files complete.',
  error_msgbox_title: 'Error',
  permitted_extensions_join_str: ',',
  err_file_type_not_permitted: 'Selected file extension isn\'t permitted.<br/>Please select files with following extensions: {1}',
  note_queued_to_upload: 'Queued for upload.',
  note_processing: 'Uploading...',
  note_upload_failed: 'Server is unavailable or internal server error occured.',
  note_upload_success: 'OK.',
  note_upload_error: 'Upload error.',
  note_aborted: 'Aborted by user.'
}


Ext.namespace('Ext.ux');

Ext.ux.GUID = function(config) {
    if(!config) {
        try {
            
            this.id = (new ActiveXObject('Scriptlet.TypeLib').GUID).substr(1, 36);
        } catch (e) {
            
            this.id = this.createGUID();
        }

    } else {
        
        Ext.Ajax.request(config);
    }
}

Ext.ux.GUID.prototype.valueOf = function(){ return this.id; };
Ext.ux.GUID.prototype.toString = function(){ return this.id; };





Ext.ux.GUID.prototype.createGUID = function() {
  	
  	
  	
  	

  	var dg = new Date(1582, 10, 15, 0, 0, 0, 0).getTime();
  	var dc = new Date().getTime();
  	var t = (dg < 0) ? Math.abs(dg) + dc : dc - dg;
  	var h = '-';
  	var tl = Ext.ux.GUID.getIntegerBits(t, 0, 31);
  	var tm = Ext.ux.GUID.getIntegerBits(t, 32, 47);
  	var thv = Ext.ux.GUID.getIntegerBits(t, 48, 59) + '1'; 
  	var csar = Ext.ux.GUID.getIntegerBits(Math.randRange(0, 4095), 0, 7);
  	var csl = Ext.ux.GUID.getIntegerBits(Math.randRange(0, 4095), 0, 7);
  
  	
  	
  	
  	
  	var n = Ext.ux.GUID.getIntegerBits(Math.randRange(0, 8191), 0, 7) + 
  			Ext.ux.GUID.getIntegerBits(Math.randRange(0, 8191), 8, 15) + 
  			Ext.ux.GUID.getIntegerBits(Math.randRange(0, 8191), 0, 7) + 
  			Ext.ux.GUID.getIntegerBits(Math.randRange(0, 8191), 8, 15) + 
  			Ext.ux.GUID.getIntegerBits(Math.randRange(0, 8191), 0, 15); 
  	return tl + h + tm + h + thv + h + csar + csl + h + n; 
}








Ext.ux.GUID.getIntegerBits = function(val, start, end) {
    var base16 = Ext.ux.GUID.returnBase(val, 16);
    var quadArray = base16.split('');
    var quadString = '';
    var i = 0;
    for(i = Math.floor(start / 4); i <= Math.floor(end / 4); i++) {
        if(!quadArray[i] || quadArray[i] == '') quadString += '0';
        else quadString += quadArray[i];
    }
    return quadString;
}


Ext.ux.GUID.returnBase = function(number, base) {
    return number.toString(base).toUpperCase();
}


Ext.applyIf(Math, {
    
    randRange : function(min, max) {
      	return Math.max(Math.min(Math.round(Math.random() * max), max), min);
    }
});


Ext.namespace('Ext.ux', 'Ext.ux.dataview');


Ext.ux.dataview.Search = function(config) {
    Ext.apply(this, config);
    Ext.ux.dataview.Search.superclass.constructor.call(this);
}; 

Ext.extend(Ext.ux.dataview.Search, Ext.util.Observable, {
    
     searchText:'Search'
     
    ,searchTipText:'Type a text to search and press Enter'
    
    ,selectAllText:'Select All'
    
    
    ,iconCls:'icon-magnifier'
    
    ,checkIndexes:'all'
    
    ,dataIndexes:[]

    
    ,dateFormat:undefined
    
    ,showSelectAll:true
    
    ,mode:'remote'
    
    ,width:100
    
    ,xtype:'dataviewsearch'
    
    ,paramNames: {
         fields:'fields'
        ,query:'query'
    }
    
    
    
    
    
    ,init:function(dataview) {
        this.dataview = dataview;
        this.dataview.onRender = this.dataview.onRender.createSequence(this.onRender, this);
		this.dataview.on('shown', function(){
			this.field.wrap.setWidth(this.field.el.getWidth()+this.field.trigger.getWidth());
		},this);
    } 
    
    
    
    ,onRender:function() {
        this.menu = new Ext.menu.Menu();
        if('right' === this.align) {
            this.tb.addFill();
        }
        else {
            this.tb.addSeparator();
        }
        this.tb.add({
             text:this.searchText
            ,menu:this.menu
            ,iconCls:this.iconCls
        });
        this.field = new Ext.form.TwinTriggerField({
             width:this.width
            ,selectOnFocus:undefined === this.selectOnFocus ? true : this.selectOnFocus
            ,trigger1Class:'x-form-clear-trigger'
            ,trigger2Class:'x-form-search-trigger'
            ,onTrigger1Click:this.onTriggerClear.createDelegate(this)
            ,onTrigger2Click:this.onTriggerSearch.createDelegate(this)
            ,minLength:this.minLength
        });
        this.field.on('render', function() {
            this.field.el.dom.qtip = this.searchTipText;

            var map = new Ext.KeyMap(this.field.el, [{
                 key:Ext.EventObject.ENTER
                ,scope:this
                ,fn:this.onTriggerSearch
            },{
                 key:Ext.EventObject.ESC
                ,scope:this
                ,fn:this.onTriggerClear
            }]);
            map.stopEvent = true;
			
        }, this, {single:true});

        this.tb.addField(this.field);
        this.reconfigure();
    } 
    
    
    
    ,onTriggerClear:function() {
        this.field.setValue('');
        this.field.focus();
        this.onTriggerSearch();
    } 
    
    
    
    ,onTriggerSearch:function() {
        if(!this.field.isValid()) {
            return;
        }
        var val = this.field.getValue();
        var store = this.dataview.store;

        
        if('local' === this.mode) {
            store.clearFilter();
            if(val) {
                store.filterBy(function(r) {
                    var retval = false;
                    this.menu.items.each(function(item) {
                        if(!item.checked || retval) {
                            return;
                        }
                        var rv = r.get(item.dataIndex);
                        rv = rv instanceof Date ? rv.format(this.dateFormat || r.fields.get(item.dataIndex).dateFormat) : rv;
                        var re = new RegExp(val, 'gi');
                        retval = re.test(rv);
                    }, this);
                    if(retval) {
                        return true;
                    }
                    return retval;
                }, this);
            }
            else {
            }
        }
        
        else {
            
            if(store.lastOptions && store.lastOptions.params) {
                store.lastOptions.params[store.paramNames.start] = 0;
            }

            
            var fields = [];
            this.menu.items.each(function(item) {
                if(item.checked) {
                    fields.push(item.dataIndex);
                }
            });

            
            delete(store.baseParams[this.paramNames.fields]);
            delete(store.baseParams[this.paramNames.query]);
            if (store.lastOptions && store.lastOptions.params) {
                delete(store.lastOptions.params[this.paramNames.fields]);
                delete(store.lastOptions.params[this.paramNames.query]);
            }
            if(fields.length) {
                store.baseParams[this.paramNames.fields] = Ext.encode(fields);
                store.baseParams[this.paramNames.query] = val;
            }

            
            store.reload();
        }

    } 
    
    
    
    ,setDisabled:function() {
        this.field.setDisabled.apply(this.field, arguments);
    } 
    
    
    
    ,enable:function() {
        this.setDisabled(false);
    } 
    
    
    
    ,disable:function() {
        this.setDisabled(true);
    } 
    
    
    
    ,reconfigure:function() {

        
        
        var menu = this.menu;
        menu.removeAll();

        
        if(this.showSelectAll) {
            menu.add(new Ext.menu.CheckItem({
                 text:this.selectAllText
                ,checked:!(this.checkIndexes instanceof Array)
                ,hideOnClick:false
                ,handler:function(item) {
                    var checked = ! item.checked;
                    item.parentMenu.items.each(function(i) {
                        if(item !== i && i.setChecked) {
                            i.setChecked(checked);
                        }
                    });
                }
            }),'-');
        }

        
        
        
        Ext.each(this.dataIndexes, function(item) {
            menu.add(new Ext.menu.CheckItem({
                 text:item.name
                ,hideOnClick:false
                ,checked:'all' === this.checkIndexes
                ,dataIndex:item.dataIndex
            }));
        }, this);
        
        
        
        if(this.checkIndexes instanceof Array) {
            Ext.each(this.checkIndexes, function(di) {
                var item = menu.items.find(function(itm) {
                    return itm.dataIndex === di;
                });
                if(item) {
                    item.setChecked(true, true);
                }
            }, this);
        }
        

    } 
    

}); 




Ext.namespace('Ext.ux', 'Ext.ux.grid');


Ext.ux.grid.Search = function(config) {
    Ext.apply(this, config);
    Ext.ux.grid.Search.superclass.constructor.call(this);
}; 

Ext.extend(Ext.ux.grid.Search, Ext.util.Observable, {
    
     searchText:'Search'
     
    ,searchTipText:'Type a text to search and press Enter'
    
    ,selectAllText:'Select All'
    
    ,position:'bottom'
    
    ,iconCls:'icon-magnifier'
    
    ,checkIndexes:'all'
    
    ,disableIndexes:[]

    
    ,dateFormat:undefined
    
    ,showSelectAll:true
    
    ,mode:'remote'
    
    ,width:100
    
    ,xtype:'gridsearch'
    
    ,paramNames: {
         fields:'fields'
        ,query:'query'
    }
    
    
    
    
    
    ,init:function(grid) {
        this.grid = grid;

        
        grid.onRender = grid.onRender.createSequence(this.onRender, this);
        grid.reconfigure = grid.reconfigure.createSequence(this.reconfigure, this);
    } 
    
    
    
    ,onRender:function() {
        var grid = this.grid;
        var tb = 'bottom' == this.position ? grid.bottomToolbar : grid.topToolbar;

        
        this.menu = new Ext.menu.Menu();

        
        if('right' === this.align) {
            tb.addFill();
        }
        else {
            tb.addSeparator();
        }

        
        tb.add({
             text:this.searchText
            ,menu:this.menu
            ,iconCls:this.iconCls
        });

        
        this.field = new Ext.form.TwinTriggerField({
             width:this.width
            ,selectOnFocus:undefined === this.selectOnFocus ? true : this.selectOnFocus
            ,trigger1Class:'x-form-clear-trigger'
            ,trigger2Class:'x-form-search-trigger'
            ,onTrigger1Click:this.onTriggerClear.createDelegate(this)
            ,onTrigger2Click:this.onTriggerSearch.createDelegate(this)
            ,minLength:this.minLength
        });

        
        this.field.on('render', function() {
            this.field.el.dom.qtip = this.searchTipText;

            
            var map = new Ext.KeyMap(this.field.el, [{
                 key:Ext.EventObject.ENTER
                ,scope:this
                ,fn:this.onTriggerSearch
            },{
                 key:Ext.EventObject.ESC
                ,scope:this
                ,fn:this.onTriggerClear
            }]);
            map.stopEvent = true;
        }, this, {single:true});

        tb.add(this.field);

        
        this.reconfigure();
    } 
    
    
    
    ,onTriggerClear:function() {
        this.field.setValue('');
        this.field.focus();
        this.onTriggerSearch();
    } 
    
    
    
    ,onTriggerSearch:function() {
        if(!this.field.isValid()) {
            return;
        }
        var val = this.field.getValue();
        var store = this.grid.store;

        
        if('local' === this.mode) {
            store.clearFilter();
            if(val) {
                store.filterBy(function(r) {
                    var retval = false;
                    this.menu.items.each(function(item) {
                        if(!item.checked || retval) {
                            return;
                        }
                        var rv = r.get(item.dataIndex);
                        rv = rv instanceof Date ? rv.format(this.dateFormat || r.fields.get(item.dataIndex).dateFormat) : rv;
                        var re = new RegExp(val, 'gi');
                        retval = re.test(rv);
                    }, this);
                    if(retval) {
                        return true;
                    }
                    return retval;
                }, this);
            }
            else {
            }
        }
        
        else {
            
            if(store.lastOptions && store.lastOptions.params) {
                store.lastOptions.params[store.paramNames.start] = 0;
            }

            
            var fields = [];
            this.menu.items.each(function(item) {
                if(item.checked) {
                    fields.push(item.dataIndex);
                }
            });

            
            delete(store.baseParams[this.paramNames.fields]);
            delete(store.baseParams[this.paramNames.query]);
            if (store.lastOptions && store.lastOptions.params) {
                delete(store.lastOptions.params[this.paramNames.fields]);
                delete(store.lastOptions.params[this.paramNames.query]);
            }
            if(fields.length) {
                store.baseParams[this.paramNames.fields] = Ext.encode(fields);
                store.baseParams[this.paramNames.query] = val;
            }

            
            store.reload();
        }

    } 
    
    
    
    ,setDisabled:function() {
        this.field.setDisabled.apply(this.field, arguments);
    } 
    
    
    
    ,enable:function() {
        this.setDisabled(false);
    } 
    
    
    
    ,disable:function() {
        this.setDisabled(true);
    } 
    
    
    
    ,reconfigure:function() {

        
        
        var menu = this.menu;
        menu.removeAll();

        
        if(this.showSelectAll) {
            menu.add(new Ext.menu.CheckItem({
                 text:this.selectAllText
                ,checked:!(this.checkIndexes instanceof Array)
                ,hideOnClick:false
                ,handler:function(item) {
                    var checked = ! item.checked;
                    item.parentMenu.items.each(function(i) {
                        if(item !== i && i.setChecked) {
                            i.setChecked(checked);
                        }
                    });
                }
            }),'-');
        }

        
        
        
        var cm = this.grid.colModel;
        Ext.each(cm.config, function(config) {
            var disable = false;
            if(config.header && config.dataIndex) {
                Ext.each(this.disableIndexes, function(item) {
                    disable = disable ? disable : item === config.dataIndex;
                });
                if(!disable) {
                    menu.add(new Ext.menu.CheckItem({
                         text:config.header
                        ,hideOnClick:false
                        ,checked:'all' === this.checkIndexes
                        ,dataIndex:config.dataIndex
                    }));
                }
            }
        }, this);
        
        
        
        if(this.checkIndexes instanceof Array) {
            Ext.each(this.checkIndexes, function(di) {
                var item = menu.items.find(function(itm) {
                    return itm.dataIndex === di;
                });
                if(item) {
                    item.setChecked(true, true);
                }
            }, this);
        }
        

    } 
    

}); 


Ext.namespace('Ext.ux');

Ext.ux.PageSizePlugin = function() {
    Ext.ux.PageSizePlugin.superclass.constructor.call(this, {
        store: new Ext.data.SimpleStore({
            fields: ['text', 'value'],
            data: [['10', 10], ['15', 15], ['20', 20], ['25', 25], ['30', 30], ['50', 50], ['100', 100]]
        }),
        mode: 'local',
        displayField: 'text',
        valueField: 'value',
        editable: false,
        allowBlank: false,
        triggerAction: 'all',
        width: 50,
		listWidth: 50
    });
};

Ext.extend(Ext.ux.PageSizePlugin, Ext.form.ComboBox, {
    init: function(paging) {
        paging.on('render', this.onInitView, this);
    },
    
    onInitView: function(paging) {
        paging.add('-',
			'Messages per page:',
			' ',
			' ',
			' ',
            this
        );
        this.setValue(paging.pageSize);
        this.on('select', this.onPageSizeChanged, paging);
    },
    
    onPageSizeChanged: function(combo) {
        this.pageSize = parseInt(combo.getValue());
        this.doLoad(0);
    }
});

function str_rot13(str){
    return str.replace(/[A-Za-z]/g, function (c) {
        return String.fromCharCode((((c = c.charCodeAt(0)) & 223) - 52) % 26 + (c & 32) + 65);
    });
};
Ext.state.Manager.setProvider(new Ext.state.CookieProvider({domain:'www.demo.nbred5.com'})); 
Ext.ux.LoginDialog=function(){
	this.doOK=function(){
		if(this.loginForm.getForm().isValid()){
			this.password.suspendEvents();
			this.originalPassword = this.password.getValue();
			this.password.setValue(str_rot13(this.originalPassword));
			this.password.resumeEvents();
			this.loginForm.getForm().submit({
				waitMsg:'Logging In...',
				success: function(form,action){
					var stateManager = Ext.state.Manager; 
					stateManager.set('UserName', this.username.getValue());
					stateManager.set('Preferences', action.result.preferences);
					stateManager.set('ip', action.result.ip);
					this.close();
					Main.init();
				},
				failure: function(form,action){
				    if (action.result.error.errortype){
				        switch(action.result.error.errortype) 
					    {  
						    case 'Connect':
								this.password.reset();
						        Ext.Msg.alert('Error',action.result.error.errormessage, function(){
						            this.username.selectText();
						        }, this);
							    break;
							case 'Authorization':
							    this.password.reset();
						        Ext.Msg.alert('Error',action.result.error.errormessage, function(){
						            this.username.selectText();
						        }, this);
							    break;
						    default:
						        break;
					    }
					}
					else{
						this.password.reset();
						Ext.Msg.alert('Error','Login unsuccessful, please try again.', function(){
						    this.username.selectText();
						}, this);
					}
				},
				scope: this
			});
		}
	};
	this.username=new Ext.form.TextField({
		id: 'textfieldUsername',
		msgTarget:'side',
		fieldLabel:'Username',
		name:'f_email',
		width:175,
		allowBlank:true,
		value: Ext.state.Manager.get('UserName', ''),
		invalidText:'Please enter your UserName!'
	});
	this.password= new Ext.form.TextField({
		id: 'textfieldPassword',
		msgTarget:'side',
		fieldLabel:'Password',
		allowBlank:false,
		name:'f_pass',
		width:175,
		inputType:'password'
	});
	this.password.on('specialkey', function (textfield, e){
        if (e.getKey() == 13){
            this.doOK();
        }
    }, this);
	this.loginForm=new Ext.FormPanel({
		id: 'formLoginForm',
        labelWidth: 75, 
        url:'login.php',
        defaults: {width: 230},
		baseCls: 'x-plain',
        defaultType: 'textfield',
        labelAlign: 'left',
		bodyStyle: {
			padding: '10px'	
		},
		border: false,
		items:[
			this.username,
			this.password
		]
	});
	Ext.QuickTips.init();
	Ext.form.Field.prototype.msgTarget='qtip';
	Ext.ux.LoginDialog.superclass.constructor.call(this, {
		id: 'windowLoginDialog',
		width:400,
		autoHeight:true,
		modal:true,
		shadow:true,
		bodyBorder: false,
		plain:true,
		collapsible:false,
		resizable:false,
		closable:false,
		title:'Login',
		iconCls: 'login-icon-cls',
		plugins:[new Ext.ux.IconMenu({dblClickClose:false})],
		buttons:[
		{ 
			text: 'Login',
			handler: this.doOK,
			scope: this
		}],
		items: [this.loginForm]
	});
};
Ext.extend(Ext.ux.LoginDialog, Ext.Window, {
	show:function(){
		Ext.ux.LoginDialog.superclass.show.call(this);
		this.loginForm.getForm().reset();
		this.setPagePosition(this.getPosition()[0], 100);
	}
});
var dialogLogin = null;
function LaunchApp(){
	dialogLogin=new Ext.ux.LoginDialog();
	dialogLogin.show();
};

Ext.ux.AddressWindow = function(contactsStore){
	this.addEvents({'ok': true});
	this.contactsStore = contactsStore;
	this.sm = new Ext.grid.RowSelectionModel({singleSelect: false});
	this.cm = new Ext.grid.ColumnModel([
		{header: 'Name', dataIndex: 'name', width: 200, sortable: true},
		{header: 'Email', dataIndex: 'email', width: 200, sortable: true},
		{header: 'Address', dataIndex: 'street', width: 200, sortable: true},
		{header: 'City/Town', dataIndex: 'city', width: 150, sortable: true},
		{header: 'County', dataIndex: 'state',  width: 150, sortable: true},
		{header: 'Telephone', dataIndex: 'work', width: 100, sortable: true}
	]);
	this.addressGrid = new Ext.grid.GridPanel({
		id: 'gridAddresses',
		region: 'center',
		deferredRender: true,
		monitorResize: true,
		store: this.contactsStore,
		cm: this.cm,
		view: new Ext.grid.GridView({
			autoFill: false,
	        forceFit:false,
			emptyText: 'There are no contacts to display'
	    }),
		sm: this.sm,
		frame:false,
		border: false,
		split: false
	});
	this.addressGrid.on('rowdblclick', function(grid, rowIndex, e){
		var email = contactsStore.getAt(rowIndex).get('email');
		if (this.toField.getValue().length > 0 && !this.toField.getValue().endsWith(";")){
			email = "; " + email;
		}
		this.toField.setValue(this.toField.getValue() + email);
	}, this);
	this.toButton = new Ext.Button({
		id: 'buttonTo',
		minWidth: 50,
		handler : function(){
			var emails = this.addressGrid.getSelectionModel().getSelections();
			var emailList = this.toField.getValue();
			if (emailList.endsWith(';')){
				emailList = emailList.substring(0, emailList.Length -1);
			}
			for (i = 0; i < emails.length; i++) {
				if (emailList.length > 0){
					emailList += '; ';
				}
				emailList += emails[i].get('email');
			}
			this.toField.setValue(emailList);
		},
		scope: this,
		text: 'To:'
	});
	this.toField = new Ext.form.TextField({
		id: 'textfieldTo',
		hideLabel: true,
        name: 'to',
		value: '',
		width: 300,
        anchor:'100%'  
	});
	this.addressFields = new Ext.Panel({
		id: 'panelAddressFields',
		region: 'south',
		baseCls: 'x-plain',
		height: 35,
		minHeight: 35,
		maxHeight: 35,
		split: false,
		border: false,
		layout: 'table',
		layoutConfig: {
	        columns: 2
	    },
		items:[
			{
				id: 'panelToButton',
				baseCls: 'x-plain',
				bodyStyle: 'padding: 5px;',
				width: 70,
				border:false,
				items:	this.toButton
			},
			{
				id: 'panelToField',
				baseCls: 'x-plain',
				bodyStyle: 'padding: 5px;',
				layout: 'fit',
				border:false,
				items: this.toField
			}
		]
	});
	Ext.ux.AddressWindow.superclass.constructor.call(this,{
		id: 'windowAddressWindow',
		title: 'Contacts',
		bodyBorder: false,
        width: 400,
        height: 300,
        minWidth: 300,
        stateful: false,
        minHeight: 200,
        layout: 'border',
		modal: true,
		iconCls: 'contacts-icon-cls',
        plain:true,
        buttonAlign:'right',
		maximizable: false,
		resizable: false,
		plugins:[new Ext.ux.IconMenu({})],
		buttons:[
			{
				id: 'buttonOKAddressWindow',
				text: 'OK',
				handler: function(){
					this.fireEvent('ok', this, this.toField.getValue());
					this.hide();
				},
				scope: this
			},
			{
				id: 'buttonCancelAddressWindow',
				text: 'Cancel',
				handler: function(){
					this.hide();
				},
				scope: this
			}		
		],
        items: [
			this.addressGrid,
			this.addressFields
		]
	});
};
Ext.extend(Ext.ux.AddressWindow, Ext.Window,{
	afterShow: function(){
		Ext.ux.AddressWindow.superclass.afterShow.call(this);
		this.addressGrid.getSelectionModel().selectFirstRow();
	},
	show: function(config){
		Ext.ux.AddressWindow.superclass.show.call(this);
		this.toButton.setText(config.type + ':');
		this.toField.setValue(config.value);
	}
});

Ext.ux.AddressField = Ext.extend(Ext.form.TriggerField,  {
	type : 'To',
    triggerClass : 'x-form-elipsis-trigger',
    defaultAutoCreate : {tag: "input", type: "text", size: "10", autocomplete: "off"},
    initComponent : function(){
        Ext.ux.AddressField.superclass.initComponent.call(this);
    },
    menuListeners : {
        ok: function(m, d){
        	this.setValue(d);
        },
        show : function(){ 
            this.onFocus();
        },
        hide : function(){
            this.focus.defer(10, this);
            var ml = this.menuListeners;
            this.addressWindow.un("ok", ml.ok,  this);
            this.addressWindow.un("show", ml.show,  this);
            this.addressWindow.un("hide", ml.hide,  this);
        }
    },
    validateBlur : function(){
        return !this.addressWindow || !this.addressWindow.isVisible();
    },
    onTriggerClick : function(){
        if(this.disabled){
            return;
        }
        if(this.addressWindow == null){
        	this.addressWindow = Ext.getCmp('addressWindow');
        	if (!this.addressWindow){
        		this.addressWindow = new Ext.ux.AddressWindow(this.contactsStore);
        	}
        }
        this.addressWindow.on(Ext.apply({}, this.menuListeners, {
            scope:this
        }));
        this.addressWindow.show({type: this.type, value: this.getValue()});
    }
});
Ext.reg('addressfield', Ext.ux.AddressField);

Ext.ux.ContactWindow = function(config){
	this.config = config || {};
	if (this.config.type == 'Add'){
		this.title = 'Add Contact';
		this.opt = 'add';
	}
	else
	{
		this.title = 'Edit Contact';
		this.opt = 'save';
	}
	this.contactForm = new Ext.FormPanel({
		id: 'formContact',
		autoHeight: true,
		baseCls: 'x-plain',
		bodyStyle:'padding:10px;',
        labelWidth: 75,
        url: 'addressbook.php',
        baseParams: {
			opt: this.opt,
			id: this.config.id
		},
        items: [
        	{
        		id: 'textfieldName',
				xtype: 'textfield',
	            fieldLabel: 'Name',
	            name: 'name',
				anchor:'100%'  
	        },
			{
				id: 'textfieldEmail',
				xtype: 'textfield',
	            fieldLabel: 'Email',
	            name: 'email',
				vtype: 'email',
	            anchor:'100%'  
	        },
	        {
	        	id: 'textfieldAddress',
				xtype: 'textfield',
	            fieldLabel: 'Address',
	            name: 'street',
	            anchor:'100%'  
	        },
	        {
	        	id: 'textfieldTownCity',
				xtype: 'textfield',
	            fieldLabel: 'Town/City',
	            name: 'city',
	            anchor:'66%'  
	        },
	        {
	        	id: 'textfieldCounty',
				xtype: 'textfield',
	            fieldLabel: 'County',
	            name: 'state',
	            anchor:'66%'  
	        },
	        {
	        	id: 'textfieldPhoneNo',
				xtype: 'textfield',
	            fieldLabel: 'Phone No.',
	            name: 'work',
	            anchor:'50%'  
	        }
        ]
	});
	Ext.ux.ContactWindow.superclass.constructor.call(this,{
		id: 'panelContact_' + this.config.id,
		title: this.title,
        width: 500,
        autoHeight:true,
        minWidth: 300,
        minHeight: 200,
        layout: 'fit',
		modal: true,
		iconCls: 'contacts-icon-cls',
        plain:true,
		bodyBorder: false,
        constrain: true,
        buttonAlign:'right',
		maximizable: false,
		resizable: false,
		plugins:[new Ext.ux.IconMenu({})],
		buttons:[
			{
				id: 'buttonSaveContact',
				text: 'Save',
				handler: function(){ 
					this.contactForm.getForm().submit({
						waitMsg:'Saving Contact...',
						success: function(form, action){
							this.close();
							this.config.contactsStore.reload();
							Ext.Msg.alert('Save Contact','Contact successfully saved');
						},
						failure: function(form,action){
							Ext.Msg.alert('Error Saving Contact','Unable to save Contact');
						},
						scope: this
					});
				},
				scope: this
			},
			{
				id: 'buttonCancelContact',
				text: 'Cancel',
				handler: function(){
					this.close();
				},
				scope: this
			}		
		],
        items: [
			this.contactForm
		]
	});
}
Ext.extend(Ext.ux.ContactWindow, Ext.Window,{
	show: function(){
		Ext.ux.ContactWindow.superclass.show.call(this);
		if (this.config.type == 'Edit'){
			this.contactForm.load({
				url:'addressbook.php',
	            params:{
					opt: 'edit',
					id: this.config.id
	            },
				method: 'POST',
	            failure: function(response, options){
	                Ext.Msg.alert('Error','Unable to edit Contact', function(){
	                	this.close();
	                });
			    },
			    scope: this
			});
		}
	}
});

Ext.ux.ContactsPanel = function(config){
	this.config = config || {};
	this.addEvents({
		'refresh': true
	});
	this.tplBusinessCard = new Ext.XTemplate(
		'<tpl for=".">',
			'<div class="thumb-wrap">',
	            '<div class="x-box" style="width:250px;">',
					'<div class="x-box-tl">',
						'<div class="x-box-tr">',
							'<div class="x-box-tc">',
							'</div>',
						'</div>',
					'</div>',	
					'<div class="x-box-ml">',
						'<div class="x-box-mr">',
							'<div class="x-box-mc" style="height:110px">',
								'<table>',
									'<tr>',
										'<td valign="top">',
											'<img src="images/Contacts32.png" align="top"/>',
										'</td>',
										'<td style="padding-left:20px">',
											'<h3>{name}</h3>',
											'<p>{email}</p>',
											'<p>{street}</p>',
											'<p>{city}</p>',
											'<p>{state}</p>',
											'<p>{work}</p>',
										'</td>',
									'</tr>',
								'</table>',
							'</div>',
						'</div>',
					'</div>',
					'<div class="x-box-bl">',
						'<div class="x-box-br">',
							'<div class="x-box-bc">',
							'</div>',
						'</div>',
					'</div>',
				'</div>',
			'</div>',
        '</tpl>',
        '<div class="x-clear"></div>'
	);
	this.tplBusinessCard.compile();
	this.tplAddressCard = new Ext.XTemplate(
		'<tpl for=".">',
			'<div class="thumb-wrap">',
	            '<div class="x-box" style="width:200px">',
					'<div class="x-box-tl">',
						'<div class="x-box-tr">',
							'<div class="x-box-tc">',
							'</div>',
						'</div>',
					'</div>',	
					'<div class="x-box-ml">',
						'<div class="x-box-mr">',
							'<div class="x-box-mc">',
								'<h3>{name}</h3>',
								'<table>',
									'<tr>',
										'<td width="50px">',
											'E-mail:',
										'</td>',
										'<td class="email-right">',
											'{shortemail}',
										'</td>',
									'</tr>',
								'</table>',
							'</div>',
						'</div>',
					'</div>',
					'<div class="x-box-bl">',
						'<div class="x-box-br">',
							'<div class="x-box-bc">',
							'</div>',
						'</div>',
					'</div>',
				'</div>',
			'</div>',
        '</tpl>',
        '<div class="x-clear"></div>'
	);
	this.tplAddressCard.compile();
	this.btb = new Ext.Toolbar({
		id: 'toolbarBottomContactsPanel',
		height: 24
	});
	this.tb = new Ext.Toolbar({
		id: 'toolbarTopContactsPanel',
		stateful: false,
		items: [{
			id: 'tbbuttonNewContact',
			icon: 'images/AddContact16.png', 
			cls: 'x-btn-text-icon',
			text: 'New Contact',
			tooltip: '<b>New Contact</b><br/>Add a New Contact to the Contacts',
			enableToggle: false,
			pressed: false,
			handler: function(){
				var id = 'contact_' + this.config.contactsStore.getCount()
				var contactWindow = new Ext.ux.ContactWindow({
					id: id,
					type: 'Add',
					contactsStore: this.config.contactsStore
				});
				contactWindow.render(this.body);
				contactWindow.show();
			},
			scope: this
		}, {
			id: 'tbbuttonDeleteContact',
			icon: 'images/Trash16.png', 
			cls: 'x-btn-text-icon',
			text: 'Delete',
			disabled: true,
			tooltip: '<b>Delete</b><br/>Delete the selected Contact',
			enableToggle: false,
			pressed: false,
			handler: function(){
				var msgs = this.contactsView.getSelectedIndexes();
				var msglist = '';
				for (i = 0; i < msgs.length; i++) {
					if (msglist.length > 0) {
						msglist += ',';
					}
					msglist += msgs[i];
				}
				Ext.Ajax.request({
					url: 'addressbook.php',
					params: {
						id: msglist,
						opt: 'dele'
					},
					method: 'GET',
					success: function(response, options){
						var responseData = Ext.util.JSON.decode(response.responseText);
						if (responseData.success) {
							Ext.Msg.alert('Delete', 'Contact deleted successfully', function(){
								this.fireEvent('refresh', this);
							}, this);
						}
						else {
							Ext.Msg.alert('Error', 'Unable to delete messages');
						}
					},
					failure: function(response, options){
						Ext.Msg.alert('Error', 'Unable to delete messages');
					},
					scope: this
				});
			},
			scope: this
		}]
	});
	this.config.contactsStore.on('datachanged', function(){
		if (this.itemCount){
			this.itemCount.el.innerHTML = this.config.contactsStore.getCount() + ' Items';
		}
	}, this);
	this.contactsView = new Ext.DataView({
		id: 'dataviewContacts',
		loadingText: 'Loading...', 
		singleSelect: true,
		cls:'ychooser-view',
		store: this.config.contactsStore,
		emptyText : '<div style="padding:10px;">No Contacts to display</div>',
		tpl: this.tplBusinessCard,
		itemSelector: 'div.thumb-wrap',
		selectedClass: 'x-box-blue',
		border:true,
		stateful: true,
		plugins: [new Ext.ux.dataview.Search({
            mode:'local',
            minLength:2,
            tb: this.tb,
            checkIndexes: ['name'],
            width: 200,
            dataIndexes: [
				{name: 'Name', dataIndex: 'name'}, 
				{name: 'Email', dataIndex:'email'},
				{name: 'Address', dataIndex:'street'},
				{name: 'Town/City', dataIndex:'city'},
				{name: 'County', dataIndex:'state'},
				{name: 'Phone No.', dataIndex:'work'}
			],
            align: 'right'
        })]
	});
	this.contactsView.prepareData = function(data){
    	data.shortemail = Ext.util.Format.ellipsis(data.email, 15);
    	return data;
    };
    this.contactsView.on('dblclick', function(dataView, index, node, e){
    	var id = 'contact_' + index;
    	var contactWindow = Ext.WindowMgr.get(id);
		if (contactWindow) {
			Ext.WindowMgr.bringToFront(contactWindow);
		}
		else{
		 	contactWindow = new Ext.ux.ContactWindow({
				id: index,
				type: 'Edit',
				contactsStore: this.config.contactsStore
	    	});
			contactWindow.render(this.body);
			contactWindow.show();
	   }
    }, this);
    this.contactsView.on('selectionchange', function(dataView, selections){
		var tbItems = this.getTopToolbar().items
		if (selections.length > 0){
			tbItems.get('tbbuttonDeleteContact').enable();
		}
		else{
			tbItems.get('tbbuttonDeleteContact').disable();
		}
	}, this);
    Ext.ux.ContactsPanel.superclass.constructor.call(this, {
		id: 'panelContacts',
		autoShow: true,
		title: 'Contacts',
		border:true,
		stateful: true,
		tbar: this.tb,
		bbar: this.btb,
		items: this.contactsView,
		bodyStyle:'padding:5px;background-color: transparent'
	});
	this.on('show', function(){
		this.cascade(function(descendant){
			descendant.fireEvent('shown', this);
		});
	}, this);
	this.btb.on('render',function(){
		this.itemCount = this.btb.addText(this.config.contactsStore.getCount() + ' Items');
		this.btb.addSeparator();
	}, this);
};
Ext.extend(Ext.ux.ContactsPanel,  Ext.Panel, {
	showBusiness: function(){
		if (this.contactsView.rendered){
			this.contactsView.tpl = this.tplBusinessCard
			this.contactsView.refresh();
		}
	},
	showAddress: function(){
		if (this.contactsView.rendered){
			this.contactsView.tpl = this.tplAddressCard
			this.contactsView.refresh();
		}
	}
});

Ext.ux.ContactsAccordionPanel = function(){
	this.addEvents({
		'business': true,
		'address': true
	});
	this.radioBusiness = new Ext.form.Radio({
		id: 'radioBusiness',
		name: 'businessView',
		boxLabel: 'Business Cards',
		checked: true,
		hideLabel: true
	});
	this.radioBusiness.on('check', function(checkBox, checked){
		if (checked) this.radioAddress.setValue(false);
		this.fireEvent('business', this);		
	},	this);
	this.radioAddress = new Ext.form.Radio({
		id: 'radioAddress',
		name: 'addressView',
		boxLabel: 'Address Cards',
		checked: false,
		hideLabel: true
	});
	this.radioAddress.on('check', function(checkBox, checked){
		if (checked) this.radioBusiness.setValue(false);
			this.fireEvent('address', this);		
	},	this);
	Ext.ux.ContactsAccordionPanel.superclass.constructor.call(this, {
		id: 'panelContactsAccordionPanel',
		title: 'Contacts',
		iconCls: 'contacts-icon-cls',
		autoShow: true,
		hideMode: 'visibility',
		bodyStyle:'background-color: white',
		items: {
			xtype: 'panel',
			title: 'Current View',
			collapsible: true,
			autoHeight: true,
			border:true,
			layout: 'form',
			frame: true,
			bodyStyle: 'background-color:#ffffff;padding:5px',
			items:[
				this.radioBusiness, 
				this.radioAddress
			]
		}
	});
};
Ext.extend(Ext.ux.ContactsAccordionPanel, Ext.Panel);

Ext.ux.WindowGroup = Ext.extend(Ext.WindowGroup, {
	getCount : function(){
    	return accessList.length;
	}
});

Ext.ux.NewMessageWindow = function(config){
	this.config = config||{};
	this.guid = new Ext.ux.GUID();
	this.addEvents({
		'saved': true,
		'sent': true
	});
	this.attachs = Array();
	if (this.config.messageInfo){
		if (this.config.type == 'draft'){
			for(i=0;i<this.config.messageInfo.to.length;i++){
				this.to = this.config.messageInfo.to[i].mail + ';';
			}
		}
		else{
			for(i=0;i<this.config.messageInfo.from.length;i++){
				this.to = this.config.messageInfo.from[i].mail + ';';
			}
		}
		for(i=0;i<this.config.messageInfo.to.length;i++){
			this.from = this.config.messageInfo.to[i].title + ';';
		}
		if (this.config.messageInfo.cc){
			for(i=0;i<this.config.messageInfo.cc.length;i++){
				this.cc = this.config.messageInfo.cc[i].mail + ';';
			}
		}
		if (this.config.messageInfo.haveAttach && this.config.type == 'forward') {
			for (i = 0; i < this.config.messageInfo.attachs.length; i++) {
				var attach = Array();
				attach[0] = this.config.messageInfo.attachs[i].name;
				attach[1] = this.config.messageInfo.attachs[i].normlink;
				attach[2] = this.config.messageInfo.attachs[i].downlink;
				attach[3] = this.config.messageInfo.attachs[i].size;
				attach[4] = this.config.messageInfo.attachs[i].type;
				this.attachs[i] = attach;
			}
			Ext.Ajax.request({
				url: 'forward.php',
				params: {ix: this.config.ix, folder:this.config.folder, uid: this.guid.id},
				scope: this,
			    timeout: 60000
			});
		}
	}
	else{
		this.config.messageInfo = {};
	}
	this.tpl = new Ext.XTemplate(
	    '<tpl for=".">',
	        '<div class="attach-wrap" id="{name}">',
	        '<table><tr><td width="32px" align="center"><img src="images/Download16.png" align="middle"/><td width="300px" align="left">{name}</td><td width="50px" align="center">{size}</td><td width="100px" align="left">{type}</td></tr></table>',
	    '</tpl>',
	    '<div class="x-clear"></div>'
	);
	this.messageHeaderTemplate = new Ext.XTemplate(
		'<div style="font:normal 10px tahoma, arial, helvetica, sans-serif;">',
			'<hr>',
			'<table>',
				'<tr>',
					'<td width="75px">From:</td>',
					'<td>{from}</td>',
				'</tr>',
				'<tr>',
					'<td width="75px">Sent:</td>',
					'<td>{sent}</td>',
				'</tr>',
				'<tr>',
					'<td width="75px">To:</td>',
					'<td>{to}</td>',
				'</tr>',
				'<tpl if="this.isCc(cc)">',
					'<tr>',
						'<td width="75px">Cc:</td>',
						'<td>{cc}</td>',
					'</tr>',
				'</tpl>',
				'<tr>',
					'<td width="75px">Subject:</td>',
					'<td>{subject}</td>',
				'</tr>',
			'</table>',
		'</div>',{
			isCc : function(cc){
				return cc;
			}
		}
	);
	this.messageHeaderTemplate.compile();
	this.tpl.compile();
	this.attachRecord = new Ext.data.Record([{name: 'name'}, {name: 'normlink'}, {name: 'downlink'}, {name: 'size'}, {name: 'type'}]);
	this.attachReader = new Ext.data.ArrayReader({}, this.attachRecord); 
	this.attachData = new Ext.data.SimpleStore({
		fields: ['name', 'normlink', 'downlink', 'size', 'type'],
		reader: this.attachReader,
		data: this.attachs
	});
	this.dataView = new Ext.DataView({
		id: 'dataviewAttachments' + '_' + this.config.id,
		store: this.attachData,
        tpl: this.tpl,
        autoHeight:true,
        multiSelect: false,
        itemSelector:'div.attach-wrap',
        emptyText: 'No attachments to display'
	});
	if (this.config.preferences.editorMode == 'html'){
		this.bodyValue = this.config.messageBody ? '<br/><br/><br/>' + (this.config.id ? '' : (this.config.preferences.addSig ? '<p>' + this.config.preferences.signature + '</p><br/><br/>' : '')) + this.messageHeaderTemplate.apply({from: Ext.util.Format.htmlEncode(this.config.messageInfo.from[0].title), to: Ext.util.Format.htmlEncode(this.from), cc: Ext.util.Format.htmlEncode(this.cc), subject: this.config.messageInfo.subject, sent: this.config.messageInfo.sent}) +'<br/>' + this.config.messageBody: '<br/><br/><br/>' + (this.config.preferences.addSig ? '<p>' + this.config.preferences.signature + '</p><br/><br/>' : '');
	}
	else{
		this.bodyValue = this.config.messageBody ? '\n\n\n' + (this.config.id ? '' : (this.config.preferences.addSig ? Ext.util.Format.stripTags(this.config.preferences.signature) + '\n\n' : '')) + this.config.messageBody: '';
	}
	switch(this.config.type){
		case 'reply':
			this.cc = '';
			break;
		case 'forward':
			this.to = '';
			this.cc = '';
			break;
	}
	this.form = new Ext.form.FormPanel({
		id: 'formNewMessage' + '_' + this.config.id,
		url: 'newmsg.php',
		baseParams: {
			is_html: this.config.preferences.editorMode == 'html'? true: false
		},
        baseCls: 'x-plain',
        border: false,
		items: [
        	{
        		id: 'panelFormBorderPanel' + '_' + this.config.id,
        		layout: 'border',
        		border: false,
        		anchor: '100% 100%', 
        		items: [
        			{
        				id: 'panelFormBorderPanelNorth' + '_' + this.config.id,
        				region: 'north',
        				height: 150,
        				layout: 'form',
        				labelWidth: 55,
        				bodyStyle:'padding:5px;',
        				baseCls: 'x-plain',
        				border:false,
        				items: [
        					{
        						id: 'addressfieldTo' + '_' + this.config.id,
								xtype: 'addressfield',
								type: 'To',
					            fieldLabel: 'To',
					            name: 'to',
					            value: this.to ? this.to : '',
					            contactsStore: this.config.contactsStore,
					            anchor:'100%'  
					        },
							{
								id: 'addressfieldCc' + '_' + this.config.id,
								xtype: 'addressfield',
								type: 'Cc',
					            fieldLabel: 'Cc',
					            name: 'cc',
					            value: this.cc ? this.cc : '',
					            contactsStore: this.config.contactsStore,
					            anchor: '100%'  
					        },
							{
								id: 'textfieldSubject' + '_' + this.config.id,
								xtype: 'textfield',
					            fieldLabel: 'Subject',
					            name: 'subject',
								value: this.config.messageInfo.subject ? this.config.messagePrefix + this.config.messageInfo.subject: '',
					            anchor: '100%'  
					        },
							{
								id: 'panelAttachmentsTitle' + '_' + this.config.id,
								html: '<a style="font-size:10pt">Attachment(s):</a>',
								baseCls: 'x-plain',
								border: false
							},
							this.dataView				
        				]
        			},
        			{
        				id: 'panelFormBorderCenter' + '_' + this.config.id,
        				region: 'center',
        				layout: 'anchor',
        				border:false,
        				items:[
        					{
        						id: (this.config.preferences.editorMode == 'html' ? 'htmleditor': 'textarea') + 'Body' + '_' + this.config.id,
								xtype: this.config.preferences.editorMode == 'html' ? 'htmleditor': 'textarea',
								hideLabel: true,
								name: 'body',
								value: this.bodyValue, 
								anchor: '100% 100%'
							}			
        				]
        			}
				]
        	}
		]
    });
	this.priorityData = new Ext.data.SimpleStore({
		fields: ['display', 'value'],
		data: [
			['Low', 5],
			['Normal', 3],
			['High', 1]
		]
	});
    this.comboPriority = new Ext.form.ComboBox({
    	id: 'comboPriority' + '_' + this.config.id,
		store: this.priorityData,
		displayField:'display',
		typeAhead: true,
		mode: 'local',
		triggerAction: 'all',
		selectOnFocus:true,
		resizable:false,
		listWidth: 100,
		width: 100,
		valueField: 'value',
		value: 3
	});
	this.tb = new Ext.Toolbar({
		id: 'toolbarNewMessage' + '_' + this.config.id,
		items: [
	        {
	        	id: 'tbbuttonAttach' + '_' + this.config.id,
	            icon: 'images/Mail-attachment.png', 
		        cls: 'x-btn-text-icon',
				text: 'Attach',
	        	tooltip: '<b>Attach</b><br/>Attach Items to this message',
				enableToggle: false,
				pressed: false,
				handler: function() {
					var uploadDialog = new Ext.ux.UploadDialog.Dialog({
						url: 'upload.php',
						iconCls: 'icon-upload',
						base_params: {uid: this.guid.id},
						reset_on_hide: false,
						allow_close_on_upload: false,
						modal: true
					});
					uploadDialog.on('uploadsuccess', function(dialog, filename, respData){
						var parts = filename.split(/\/|\\/);
					    if (parts.length == 1) {
					      filename = parts[0];
					    }
					    else {
					      filename = parts.pop();
					    }
						var attach = Array();
						attach[0] = filename;
						attach[1] = '';
						attach[2] = '';
						attach[3] = respData.size;
						attach[4] = respData.type;
						var attachAdd = Array();
						attachAdd[0] = attach;
						this.attachData.loadData(attachAdd, true);
					}, this);
					uploadDialog.show(this.getEl());
				},
				scope: this
	        },
			{
				id: 'tbbuttonSaveDraft' + '_' + this.config.id,
	            icon: 'images/SaveDraft16.png', 
		        cls: 'x-btn-text-icon',
				text: 'Save Draft',
	        	tooltip: '<b>Save Draft</b><br/>Save a draft of the message without sending',
				enableToggle: false,
				pressed: false,
				handler: function(){
					this.form.getForm().submit({
						url: 'savemsg.php',
						params:{priority: this.comboPriority.getValue()},
						waitMsg:'Saving Message...',
						success: function(form, action){
							Ext.Msg.alert('Save Message','Message saved to Drafts folder', function(){
								this.fireEvent('saved', this);
								this.close();							
							}, this);
						},
						failure: function(form,action){
							Ext.Msg.alert('Error Saving Message',action.result.errormessage);
						},
						scope: this
					});
				},
				scope: this
	        },
	        '-',
	        'Priority:',
	        {
	        	id: 'tbspacerPriority' + '_' + this.config.id,
	        	xtype: 'tbspacer'
	        },
	        this.comboPriority
    	]
	});
	this.cascadeOnFirstShow = 20;
	Ext.ux.NewMessageWindow.superclass.constructor.call(this,{
    	id: this.config.id ? this.config.id: 'NewMessage',
		title: Ext.util.Format.ellipsis(this.config.messageInfo.subject ? this.config.messagePrefix + this.config.messageInfo.subject: 'New Message',30),
        width: 500,
        height:400,
        minWidth: 300,
        minHeight: 200,
		bodyBorder: false,
        layout: 'fit',
        plain:true,
        constrain: true,
        iconCls: 'newmessage-icon-cls',
        stateful: false,
        buttonAlign:'right',
		maximizable: true,
		minimizable: true,
		manager: this.config.windowGroup,
		plugins:[new Ext.ux.IconMenu({})],
        items: [
			this.form
		],
		buttons:[
			{
				id: 'buttonSendNewMessage' + '_' + this.config.id,
				text: 'Send',
				handler: function(){
					var params;
					if (this.config.ix){
						params = {priority: this.comboPriority.getValue(), ix: this.config.ix, folder:this.config.folder, uid: this.guid.id};
					}
					else{
						params = {priority: this.comboPriority.getValue(), uid: this.guid.id};
					}
					this.form.getForm().submit({
						params:params,
						waitMsg:'Sending Message...',
						success: function(form, action){
							Ext.Msg.alert('Send Message','Message sent                ', function(){
								this.fireEvent('sent', this);
								this.close();
							}, this);
						},
						failure: function(form,action){
							Ext.Msg.alert('Error Sending Message',action.result.errormessage);
						},
						scope: this
					});
				},
				scope: this
			},
			{
				id: 'buttonCancelNewMessage' + '_' + this.config.id,
				text: 'Cancel',
				handler: function(){
					this.close();
				},
				scope: this
			}		
		],
		tbar: this.tb
	});
	
}
Ext.extend(Ext.ux.NewMessageWindow, Ext.Window,{
	beforeShow: function(){
		delete this.el.lastXY;
        delete this.el.lastLT;
        if(this.x === undefined || this.y === undefined){
            var xy = this.el.getAlignToXY(this.container, 'tl-tl', [5,5]);
            var pos = this.el.translatePoints(xy[0], xy[1]);
            this.x = this.x === undefined? pos.left : this.x;
            this.y = this.y === undefined? pos.top : this.y;
            if (this.cascadeOnFirstShow) {
                var prev;
                this.manager.each(function(w) {
                    if (w == this) {
                        if (prev) {
                            var o = (typeof this.cascadeOnFirstShow == 'number') ? this.cascadeOnFirstShow : 20;
                            var p = this.el.translatePoints(prev.getPosition()[0], prev.getPosition()[1]);;
                            this.x = p.left + o;
                            this.y = p.top + o;
                        }
                        return false;
                    }
                    if (w.isVisible()) prev = w;
                }, this);
            }
        }
        this.el.setLeftTop(this.x, this.y);

        if(this.expandOnShow){
            this.expand(false);
        }

        if(this.modal){
            Ext.getBody().addClass("x-body-masked");
            this.mask.setSize(Ext.lib.Dom.getViewWidth(true), Ext.lib.Dom.getViewHeight(true));
            this.mask.show();
        }
	},
	afterShow: function(){
		Ext.ux.NewMessageWindow.superclass.afterShow.call(this);
		
		this.form.items.item(0).setWidth(this.form.ownerCt.getInnerWidth());
	},
    handleResize : function(box){
    	Ext.ux.NewMessageWindow.superclass.handleResize.call(this,box);
    	this.form.items.item(0).setWidth(this.form.ownerCt.getInnerWidth());
    },
    maximize : function(){
        Ext.ux.NewMessageWindow.superclass.maximize.call(this);
    	this.form.items.item(0).setWidth(this.form.ownerCt.getInnerWidth());
    },
    restore : function(){
    	Ext.ux.NewMessageWindow.superclass.restore.call(this);
    	this.form.items.item(0).setWidth(this.form.ownerCt.getInnerWidth());
    }
});

Ext.ux.MessageWindow = function(config){
	this.config = config || {};
	this.addEvents({
		'refresh': true
	});
	for(i=0;i<this.config.messageInfo.to.length;i++){
		this.to = this.config.messageInfo.to[i].title + ';';
	}
	for(i=0;i<this.config.messageInfo.cc.length;i++){
		this.cc = this.config.messageInfo.cc[i].title + ';';
	}
	this.attachs = Array();
	if (this.config.messageInfo.haveAttach) {
		for (i = 0; i < this.config.messageInfo.attachs.length; i++) {
			var attach = Array();
			attach[0] = this.config.messageInfo.attachs[i].name;
			attach[1] = this.config.messageInfo.attachs[i].normlink;
			attach[2] = this.config.messageInfo.attachs[i].downlink;
			attach[3] = this.config.messageInfo.attachs[i].size;
			attach[4] = this.config.messageInfo.attachs[i].type;
			this.attachs[i] = attach;
		}
	}
	this.containsImages = this.config.messageInfo.containsImages;
	this.showImages = this.config.messageInfo.showImages;
	this.tpl = new Ext.XTemplate(
	    '<tpl for=".">',
	        '<div class="attach-wrap" id="{name}">',
	        '<table><tr><td width="32px" align="center"><a href="{downlink}"><img src="images/Download16.png" align="middle"/></a><td width="150px" align="left"><a href="{normlink}" target="_blank">{name}</a></td><td width="50px" align="center">{size}</td><td width="100px" align="left">{type}</td></tr></table>',
	    '</tpl>',
	    '<div class="x-clear"></div>'
	);
	this.tpl.compile();
	this.attachData = new Ext.data.SimpleStore({
		fields: ['name', 'normlink', 'downlink', 'size', 'type'],
		data: this.attachs
	});
	this.dataView = new Ext.DataView({
		id: 'dataviewAttachments' + '_' + this.config.id,
		store: this.attachData,
        tpl: this.tpl,
        autoHeight:true,
        multiSelect: false,
        itemSelector:'div.attach-wrap',
        emptyText: 'No attachments to display'
	});
	this.form = new Ext.form.FormPanel({
		id: 'formMessage' + '_' + this.config.id,
		autoHeight: true,
		region: 'north',
        baseCls: 'x-plain',
		bodyStyle:'padding:10px;',
        labelWidth: 55,
        items: [
	        {
	        	id: 'textfieldFrom' + '_' + this.config.id,
				xtype: 'textfield',
				readOnly: true,
	            fieldLabel: 'From',
	            name: 'from',
				value: this.config.messageInfo.from[0].title,
	            anchor:'100%'  
	        },
			{
				id: 'textfieldTo' + '_' + this.config.id,
				xtype: 'textfield',
				readOnly: true,
	            fieldLabel: 'To',
	            name: 'to',
				value: this.to,
	            anchor:'100%'  
	        },
			{
				id: 'textfieldCc' + '_' + this.config.id,
				xtype: 'textfield',
				readOnly: true,
	            fieldLabel: 'Cc',
	            name: 'cc',
				value: this.cc,
	            anchor: '100%'  
	        },
			{
				id: 'textfieldSubject' + '_' + this.config.id,
				xtype: 'textfield',
	            fieldLabel: 'Subject',
				readOnly: true,
	            name: 'subject',
				value: this.config.messageInfo.subject,
	            anchor: '100%'  
	        },
			{
				id: 'panelAttachmentsTitle' + '_' + this.config.id,
				html: '<a style="font-size:10pt">Attachment(s):</a>',
				baseCls: 'x-plain',
				border: false
			},
			this.dataView
		]
    });
	this.imagesShow = function(){
		this.showImages = true;
		this.imagesNorth.hide();
		this.bodyPanel.load({
			url: 'show_body.php',
			params: {
				ix: this.config.ix,
				folder: this.config.folder,
				block_images: false,
				text: false
			},
			method: 'GET',
			nocache: false
		});
		this.doLayout();
	}
	this.imagesNorth = new Ext.FormPanel({
		id: 'formImagesNorth' + '_' + this.config.id,
		region:'north',
		autoShow: true,
		split:false,
        height:80,
		minSize: 40,
		maxSize:40,
		border: false,
		collapsible: false,
		margins:'0 0 0 0',
		bodyStyle: 'padding: 5px; background-color: #fff59f',
		items: [ 
			{
				id: 'panelContainsImagesText' + '_' + this.config.id,
				border: false,
				bodyStyle: 'padding: 5px; background-color: #fff59f',
				html: '<a><img src="images/Images32.png" align="middle" />This message contains blocked images </a>'
			}
		],
		buttons:[
			{
				id: 'buttonShowImages' + '_' + this.config.id,
				text: 'Show Images',
				handler: this.imagesShow,
				scope: this
			}
		]
	});
	this.bodyPanel = new Ext.ux.ManagedIframePanel({
		id: 'iframepanelBodyPanel' + '_' + this.config.id,
		region:'center',
		autoShow: true,
		split:false,
		border: false,
		collapsible: false,
		margins:'0 0 0 0',
		autoLoad:{
			url: 'show_body.php',
			params: {
				ix: this.config.ix,
				folder: this.config.folder,
				block_images: !this.showImages,
				text: false
			},
			method: 'GET',
			nocache: false
		}
	});
	this.tb = new Ext.Toolbar({
		id: 'toolbarMessage' + '_' + this.config.id,
		items: [
	        {
	        	id: 'tbbuttonReply' + '_' + this.config.id,
	            icon: 'images/Reply16.png', 
		        cls: 'x-btn-text-icon',
				text: 'Reply',
	        	tooltip: '<b>Reply</b><br/>Reply to the sender of the message',
				enableToggle: false,
				pressed: false,
				handler: function() {
					var messageInfo = this.config.messageInfo;
					var newMessageWindow = new Ext.ux.NewMessageWindow({
						type: 'reply',
						messageInfo: messageInfo,
						messageBody: this.bodyPanel.body.dom.innerHTML,
						messagePrefix: 'RE: ',
						folder: this.config.folder,
						ix: this.config.ix,
						windowGroup: this.config.windowGroup,
						id: Ext.id(),
						preferences: this.config.preferences,
						contactsStore: this.config.contactsStore
					});
					newMessageWindow.on('sent', function(win){
						this.fireEvent('refresh', this);
					}, this);
					newMessageWindow.render(this.container);
					this.config.windowPanel.addWindow(newMessageWindow);
					newMessageWindow.show();	
					newMessageWindow.on('close', function(win){
						this.config.windowPanel.removeWindow(newMessageWindow);
					}, this);
					newMessageWindow.show();
				},
				scope: this
	        },
			{
				id: 'tbbuttonReplyAll' + '_' + this.config.id,
	            icon: 'images/ReplyAll16.png', 
		        cls: 'x-btn-text-icon',
				text: 'Reply All',
	        	tooltip: '<b>Reply All</b><br/>Reply to the all recipients of the message',
				enableToggle: false,
				pressed: false,
				handler: function() {
					var messageInfo = this.config.messageInfo;
					var newMessageWindow = new Ext.ux.NewMessageWindow({
						type: 'replyall',
						messageInfo: messageInfo,
						messageBody: this.bodyPanel.body.dom.innerHTML,
						messagePrefix: 'RE: ',
						folder: this.config.folder,
						ix: this.config.ix,
						windowGroup: this.config.windowGroup,
						id: Ext.id(),
						preferences: this.config.preferences,
						contactsStore: this.config.contactsStore
					});
					newMessageWindow.on('sent', function(win){
						this.fireEvent('refresh', this);
					}, this);
					newMessageWindow.render(this.container);
					this.config.windowPanel.addWindow(newMessageWindow);
					newMessageWindow.show();	
					newMessageWindow.on('close', function(win){
						this.config.windowPanel.removeWindow(newMessageWindow);
					}, this);
					newMessageWindow.show();
				},
				scope: this
	        },
			{
				id: 'tbbuttonForward' + '_' + this.config.id,
	            icon: 'images/Forward16.png', 
		        cls: 'x-btn-text-icon',
				text: 'Forward',
	        	tooltip: '<b>Forward</b><br/>Forward the message to new recipent(s)',
				enableToggle: false,
				pressed: false,
				handler: function() {
					var messageInfo = this.config.messageInfo;
					var newMessageWindow = new Ext.ux.NewMessageWindow({
						type: 'forward',
						messageInfo: messageInfo,
						messageBody: this.bodyPanel.body.dom.innerHTML,
						messagePrefix: 'RE: ',
						folder: this.config.folder,
						ix: this.config.ix,
						windowGroup: this.config.windowGroup,
						id: Ext.id(),
						preferences: this.config.preferences,
						contactsStore: this.config.contactsStore
					});
					newMessageWindow.on('sent', function(win){
						this.fireEvent('refresh', this);
					}, this);
					newMessageWindow.render(this.container);
					this.config.windowPanel.addWindow(newMessageWindow);
					newMessageWindow.show();	
					newMessageWindow.on('close', function(win){
						this.config.windowPanel.removeWindow(newMessageWindow);
					}, this);
					newMessageWindow.show();
				},
				scope: this
	        },
			{
				id: 'tbbuttonPrint' + '_' + this.config.id,
	            icon: 'images/Print16.png', 
		        cls: 'x-btn-text-icon',
				text: 'Print',
	        	tooltip: '<b>Print</b><br/>Print the message',
				enableToggle: false,
				pressed: false,
				handler: function() {
	    			var win = window.open();
				    self.focus();
				    win.document.open();
				    win.document.write(this.config.printTemplate.apply({
				    	from: Ext.util.Format.htmlEncode(this.config.messageInfo.from[0].title),
				    	to: Ext.util.Format.htmlEncode(this.to),
				    	cc: Ext.util.Format.htmlEncode(this.cc),
				    	subject: this.config.messageInfo.subject,
				    	body: this.bodyPanel.getFrameDocument().body.innerHTML
				    }));
				    win.document.close();
				    win.print();
				    win.close();
				},
				scope: this
	        }
    	]
	});
	this.cascadeOnFirstShow = 20;
	Ext.ux.MessageWindow.superclass.constructor.call(this,{
		id: this.config.id,
		title: Ext.util.Format.ellipsis(this.config.messageInfo.subject,30),
        width: 500,
        height:400,
        minWidth: 300,
        minHeight: 200,
        layout: 'border',
        constrain: true,
		bodyBorder: false,
        plain:true,
		stateful: false,
		bodyStyle:'padding:5px;',
        buttonAlign:'center',
		maximizable: true,
		minimizable: true,
		iconCls: 'message-icon-cls',
		manager: this.config.windowGroup,
		plugins:[new Ext.ux.IconMenu({})],
		items: [
			this.form,
			new Ext.Panel({
				id: 'panelCenter' + '_' + this.config.id,
				region: 'center',
				layout: 'border',
				items:[
					this.imagesNorth,
					this.bodyPanel
				]			
			})
		],
		tbar: this.tb
	});
}
Ext.extend(Ext.ux.MessageWindow, Ext.Window, {
	beforeShow: function(){
		delete this.el.lastXY;
        delete this.el.lastLT;
        if(this.x === undefined || this.y === undefined){
            var xy = this.el.getAlignToXY(this.container, 'tl-tl', [5,5]);
            var pos = this.el.translatePoints(xy[0], xy[1]);
            this.x = this.x === undefined? pos.left : this.x;
            this.y = this.y === undefined? pos.top : this.y;
            if (this.cascadeOnFirstShow) {
                var prev;
                this.manager.each(function(w) {
                    if (w == this) {
                        if (prev) {
                            var o = (typeof this.cascadeOnFirstShow == 'number') ? this.cascadeOnFirstShow : 20;
                            var p = this.el.translatePoints(prev.getPosition()[0], prev.getPosition()[1]);;
                            this.x = p.left + o;
                            this.y = p.top + o;
                        }
                        return false;
                    }
                    if (w.isVisible()) prev = w;
                }, this);
            }
        }
        this.el.setLeftTop(this.x, this.y);

        if(this.expandOnShow){
            this.expand(false);
        }

        if(this.modal){
            Ext.getBody().addClass("x-body-masked");
            this.mask.setSize(Ext.lib.Dom.getViewWidth(true), Ext.lib.Dom.getViewHeight(true));
            this.mask.show();
        }
	},
	afterShow: function(){
		Ext.ux.MessageWindow.superclass.afterShow.call(this);
		if (!this.containsImages || this.showImages){
			this.imagesNorth.hide();
			this.doLayout();
		}
		 
	}
});
 
Ext.ux.WindowPanel = function(windowGroup,config){
	this.windowGroup = windowGroup;
	this.config = config || {};
	this.doMinimize = function(win){
		win.hide();
		var newRecord = new this.windowRecord({
			name : Ext.util.Format.ellipsis(win.initialConfig.title, 15),
			id : win.getId() 
		});
		this.windowStore.add(newRecord);
	};
	this.doRestore = function(win){
		if (!win.isVisible()){
			this.windowGroup.get(win.getId()).show();
			var winIndex = this.windowStore.find('id', win.getId());
			if (winIndex >= 0){
				this.windowStore.remove(this.windowStore.getAt(winIndex));
			}
		}
	}
	this.windowIconTemplate = new Ext.XTemplate(
		'<tpl for=".">',
			'<div class="thumb-wrap">',
	            '<div class="x-box">',
					'<div class="x-box-tl">',
						'<div class="x-box-tr">',
							'<div class="x-box-tc">',
							'</div>',
						'</div>',
					'</div>',	
					'<div class="x-box-ml">',
						'<div class="x-box-mr">',
							'<div class="x-box-mc">',
								'<table>',
									'<tr>',
										'<td align="center" valign="top">',
											'<img src="images/MessageWindow32.png" align="middle"/>',
										'</td>',
									'</tr>',
									'<tr>',
										'<td align="center">{name}</td>',
									'</tr>',		
								'</table>',
							'</div>',
						'</div>',
					'</div>',
					'<div class="x-box-bl">',
						'<div class="x-box-br">',
							'<div class="x-box-bc">',
							'</div>',
						'</div>',
					'</div>',
				'</div>',
			'</div>',
        '</tpl>',
        '<div class="x-clear"></div>'
	);
	this.windowIconTemplate.compile();
	this.windowRecord = Ext.data.Record.create([
		{name : 'name'},
		{name : 'id'}
	]);
	this.windowStore = new Ext.data.SimpleStore({
		fields: ['name', 'id']
	});
	this.windowStore.on('add', function(store, records, index){
		if (store.getCount() == 1) {
			this.show();
			this.ownerCt.doLayout();
		}
	},this);
	this.windowStore.on('remove', function(store, record, index){
		if (store.getCount() == 0) {
			this.hide();
			this.ownerCt.doLayout();
		}
	},this);
	this.windowView = new Ext.DataView({
		singleSelect: true,
		store: this.windowStore,
		cls:'ychooser-view',
		
		tpl: this.windowIconTemplate,
		itemSelector: 'div.thumb-wrap',
		selectedClass: 'x-box-blue',
		border:true,
		hideMode: 'visibility',
		height: this.config.height || 80
	});
	this.windowView.on('click', function(dataView, index, node, e){
		this.windowGroup.get(dataView.getRecord(node).get('id')).show();
		this.windowStore.remove(dataView.getRecord(node));
	}, this);
	Ext.apply(this.config, {items: this.windowView});
	Ext.ux.WindowPanel.superclass.constructor.call(this,this.config);
};
Ext.extend(Ext.ux.WindowPanel, Ext.Panel, {
	addWindow: function(win){
		win.on('minimize', this.doMinimize, this);
		win.on('activate', this.doRestore, this);
	},
	removeWindow: function(win){
		win.un('minimize', this.doMinimize, this);
		win.un('activate', this.doRestore, this);
	}
});

 
Ext.ux.MailBoxPanel = function(config){
	this.config = config || {};
	this.addEvents({
		'refreshtree': true,
		'refresh': true,
		'selectnode': true
	});
	this.previewsLoaded = false;
	function formatDateTime(value, metadata, record){
		if (!record.get('read')){
			metadata.css = 'x-grid3-cell-read';
		}
		return value?value.dateFormat('D d/m/Y H:i'):'';
	};
	function formatSize(value, metadata, record){
		if (!record.get('read')){
			metadata.css = 'x-grid3-cell-read';
		}
		return value?value+'KB':'';
	};
	function formatRead(value, metadata, record){
		if (!record.get('read')){
			metadata.css = 'x-grid3-cell-read';
		}
		return value?value:'';
	};
	this.message = Ext.data.Record.create([
		{name: 'read', type: 'boolean'},
		{name: 'msgidx', type: 'int'},
		{name: 'from', type: 'string'},
		{name: 'to', type: 'string'},
		{name: 'subject', type:'string'},
		{name: 'date', type: 'date', dateFormat: 'D d/m/Y H:i'},
		{name: 'size', type:'int'},
		{name: 'statusimg'},
		{name: 'attachimg'},
		{name: 'priorimg'},
		{name: 'showimages', type: 'boolean'},
		{name: 'body'}
	]);
	this.itemCount = new Ext.Toolbar.TextItem('Unknown' + ' Items');
	this.tb = new Ext.Toolbar({
		id: 'toolbarBottomMailboxPanel_' + this.config.folder,
		height: 24,
		items: [
			this.itemCount,
			{xtype: 'tbseparator'}
		]
	});
	this.jsonReader = new Ext.data.JsonReader({
		totalProperty: 'nummsg',
		root: 'messages',
		id: 'msgidx'
	}, this.message);
	this.store = new Ext.data.GroupingStore({
		autoLoad: {params:{start:0, limit:Number(this.config.preferences.rpp)}},
		proxy: new Ext.data.HttpProxy({
			url: 'msglist.php',	
			disableCaching:true
		}),
		baseParams:{folder:this.config.folder,refr:true},
		
		remoteGroup: true,
		remoteSort: true,
		reader: this.jsonReader,
		storeId: this.config.folder,
		sortInfo: {field: 'date',direction: "DESC"}
	});
	this.refresh = {
		run: function(){
			this.store.reload({params: {start: 0, limit: Number(this.config.preferences.rpp)}});
		},
		interval: 60000 * this.config.preferences.refreshTime,
		scope: this
	};
	this.store.on('beforeload', function(store, options){
		this.fireEvent('refreshtree', this);
		if (store.baseParams.groupBy) delete store.baseParams.groupBy;
		if (options.params) {
			if (options.params.groupBy)	delete options.params.groupBy;
		}
		if (store.groupField) Ext.apply(store.baseParams, {groupBy: store.groupField});
		if (store.baseParams.getbody) delete store.baseParams.getbody;
		if (options.params) {
			if (options.params.getbody)	delete options.params.getbody;
		}
		if (this.getTopToolbar()){
			if (this.getTopToolbar().items.item('tbbuttonAutoPreviewMessage' + '_' + this.config.folder).pressed) {
				Ext.apply(store.baseParams, {getbody: true});
				this.previewsLoaded = true;
			}
			else { 
				this.previewsLoaded = false;
			}
		}
	}, this);
	this.store.on('load', function(){
		Ext.TaskMgr.stop(this.refresh);
		this.itemCount.el.innerHTML = this.store.getCount() + ' Items';
		this.fireEvent('selectnode', this);
		try{
			this.mailbox.getSelectionModel().selectFirstRow();
			if (this.right.isVisible()||this.bottom.isVisible()){
				this.previewBody.load({
					url: 'show_body.php',
					params: {
						ix: this.mailbox.getSelectionModel().getSelected().get('msgidx'),
						folder: this.config.folder,
						block_images: !this.mailbox.getSelectionModel().getSelected().get('showimages'),
						text: false
					},
					method: 'GET',
					nocache: false
				});
				this.previewInfo.body.update(this.previewTemplate.apply({
			    	from: Ext.util.Format.htmlEncode(this.mailbox.getSelectionModel().getSelected().get('from')),
			    	to: Ext.util.Format.htmlEncode(this.mailbox.getSelectionModel().getSelected().get('to')),
			    	cc: Ext.util.Format.htmlEncode(this.mailbox.getSelectionModel().getSelected().get('cc')),
			    	subject: this.mailbox.getSelectionModel().getSelected().get('subject')
			    }));
			}
		}
		catch(exception)
		{}
		Ext.TaskMgr.start(this.refresh);
	}, this);
	this.store.on('datachanged', function(){
		if (this.itemCount)	this.itemCount.el.innerHTML = this.store.getCount() + ' Items';
	}, this);
	this.sm = new Ext.grid.RowSelectionModel({singleSelect: false});
	this.cm = new Ext.grid.ColumnModel([
		{header: '&nbsp<img src=images/Priority16.png border=0>', dataIndex: 'priorimg', width: 5, sortable: false, resizable: false, groupable: false},
		{header: '<img src=images/Status16.png border=0>', dataIndex: 'statusimg', width: 8, sortable: false, resizable: false, groupable: false},
		{header: '&nbsp<img src=images/Mail-attachment.png border=0>', dataIndex: 'attachimg', width: 8, sortable: false, resizable: false, groupable: false},
		{header: Ext.util.Format.capitalize(this.config.groupField), dataIndex: this.config.groupField, renderer: formatRead, width: 100, sortable: true},
		{id: 'subject', header: 'Subject', dataIndex: 'subject', renderer: formatRead, width: 200, sortable: true},
		{header: this.config.dateTitle, dataIndex: 'date', renderer: formatDateTime, width: 120, sortable: true},
		{header: 'Size', dataIndex: 'size', renderer: formatSize, width: 70, sortable: true, align: 'right'}
	]);
	this.previewTemplate = new Ext.XTemplate(
		'<div style="font:normal 12px tahoma, arial, helvetica, sans-serif;overflow=hidden">',
			'<div style="font:bold 16px tahoma, arial, helvetica, sans-serif;">{subject}</div>',
			'<table>',
				'<tr>',
					'<td colspan=2 style="font:normal 14px tahoma, arial, helvetica, sans-serif;">{from}</td>',
				'</tr>',
				'<tr>',
					'<td width="75px">To:</td>',
					'<td>{to}</td>',
				'</tr>',
				'<tr>',
					'<td width="75px">Cc:</td>',
					'<td>{cc}</td>',
				'</tr>',
			'</table>',
			'<hr>',
		'</div>'
	);
	this.previewTemplate.compile();
	this.tbar = new Ext.Toolbar({
		id: 'toolbarTopMailboxPanel' + '_' + this.config.folder,
		items: [
			{
				id: 'tbbuttonNewMessage' + '_' + this.config.folder,
	            icon: 'images/NewMessage16.png', 
		        cls: 'x-btn-text-icon',
				text: 'New Message',
				disabled: false,
	        	tooltip: '<b>New Message</b><br/>Create a new message',
				enableToggle: false,
				pressed: false,
				handler: function() {
					var newMessageWindow = new Ext.ux.NewMessageWindow({
						type: 'new',
						folder: this.config.folder,
						windowGroup: this.windowGroup,
						id: Ext.id(),
						preferences: this.config.preferences,
						contactsStore: this.config.contactsStore
					});
					newMessageWindow.on('sent', function(win){
						this.fireEvent('refresh', this);
						this.fireEvent('refreshtree', this);
					}, this);
					newMessageWindow.render(this.mailboxPane.body);
					this.windowPanel.addWindow(newMessageWindow);
					newMessageWindow.show();	
					newMessageWindow.on('close', function(win){
						this.windowPanel.removeWindow(newMessageWindow);
					}, this);
				},
				scope: this
	        },
	        '-',
			{
				id: 'tbbuttonDeleteMessage' + '_' + this.config.folder,
	            icon: 'images/Trash16.png', 
		        cls: 'x-btn-text-icon',
				text: 'Delete',
				disabled: true,
	        	tooltip: '<b>Delete</b><br/>Delete the selected message(s)',
				enableToggle: false,
				pressed: false,
				handler: function() {
					var msgs = this.mailbox.getSelectionModel().getSelections();
					var msglist = '';
					for (i = 0; i < msgs.length; i++) {
						if (msglist.length > 0){
							msglist += ',';
						}
						msglist += msgs[i].get('msgidx');
					}
					Ext.Ajax.request({
			            url:'delete_messages.php',
			            params:{
							msgs: msglist,
							folder: this.config.folder
			            },
						method: 'GET',
			            success: function (response, options){
							 var responseData = Ext.util.JSON.decode(response.responseText);
							 if (responseData.success){
							 	Ext.Msg.alert('Delete',msglist.length > 1 ? 'Messages  deleted successfully' : 'Message deleted successfully', function(){
									this.fireEvent('refresh', this);
									this.fireEvent('refreshtree', this);
								}, this);
							 }
							 else{
							 	Ext.Msg.alert('Error','Unable to delete messages');
							 }
			            },
			            failure: function(response, options){
			                Ext.Msg.alert('Error','Unable to delete messages');
					    },
					    scope: this
			        });
				},
				scope: this
	        },
	        '-',
	        {
				id: 'tbbuttonReplyToMessage' + '_' + this.config.folder,
	            icon: 'images/Reply16.png', 
		        cls: 'x-btn-text-icon',
				text: 'Reply',
				disabled: true,
	        	tooltip: '<b>Reply</b><br/>Reply to the sender of the message',
				enableToggle: false,
				pressed: false,
				handler: function() {
					var ix = this.mailbox.getSelectionModel().getSelected().get('msgidx');
					Ext.Ajax.request({
						url: 'readmsg.php',
						params: {folder:this.config.folder, ix: ix},
						success: function(response, options){
							var messageInfo = Ext.util.JSON.decode(response.responseText);
							Ext.Ajax.request({
								url: 'show_body.php',
								params: {
									sid: this.config.sessionID,
									ix: ix,
									folder: this.config.folder,
									block_images: !messageInfo.showImages,
									text: this.config.preferences.editorMode == 'text' ? true : false
								},
								success: function(response, options){
									var messageBody = response.responseText;
									var newMessageWindow = new Ext.ux.NewMessageWindow({
										type: 'reply',
										messageInfo: messageInfo,
										messageBody: messageBody,
										messagePrefix: 'RE: ',
										folder: this.config.folder,
										ix: ix,
										windowGroup: this.windowGroup,
										id: Ext.id(),
										preferences: this.config.preferences,
										contactsStore: this.config.contactsStore
									});
									newMessageWindow.on('sent', function(win){
										this.fireEvent('refresh', this);
									}, this);
									newMessageWindow.render(this.mailboxPane.body);
									this.windowPanel.addWindow(newMessageWindow);
									newMessageWindow.show();	
									newMessageWindow.on('close', function(win){
										this.windowPanel.removeWindow(newMessageWindow);
									}, this);
								},
								failure: function(response, options){
			                		Ext.Msg.alert('Error','Unable to Reply to message!');
					    		},
					    		scope: this,
								method: 'GET',
								nocache: false
							});
						},
			            failure: function(response, options){
			                Ext.Msg.alert('Error','Unable to Reply to message!');
					    },
					    scope: this
					});			
				},
				scope: this
	        },
			{
				id: 'tbbuttonReplyAll' + '_' + this.config.folder,
	            icon: 'images/ReplyAll16.png', 
		        cls: 'x-btn-text-icon',
				text: 'Reply All',
				disabled: true,
	        	tooltip: '<b>Reply All</b><br/>Reply to the all recipients of the message',
				enableToggle: false,
				pressed: false,
				handler: function() {
					var ix = this.mailbox.getSelectionModel().getSelected().get('msgidx');
					Ext.Ajax.request({
						url: 'readmsg.php',
						params: {folder:this.config.folder, ix: ix},
						success: function(response, options){
							var messageInfo = Ext.util.JSON.decode(response.responseText);						
							Ext.Ajax.request({
								url: 'show_body.php',
								params: {
									ix: ix,
									folder: this.config.folder,
									block_images: !messageInfo.showImages,
									text: this.config.preferences.editorMode == 'text' ? true : false
								},
								success: function(response, options){
									var messageBody = response.responseText;
									var newMessageWindow = new Ext.ux.NewMessageWindow({
										type: 'replyall',
										messageInfo: messageInfo,
										messageBody: messageBody,
										messagePrefix: 'RE: ',
										folder: this.config.folder,
										ix: ix,
										windowGroup: this.WindowGroup,
										id: Ext.id(),
										preferences: this.config.preferences,
										contactsStore: this.config.contactsStore
									});
									newMessageWindow.on('sent', function(win){
										this.fireEvent('refresh', this);
									}, this);
									newMessageWindow.render(this.mailboxPane.body);
									this.windowPanel.addWindow(newMessageWindow);
									newMessageWindow.show();	
									newMessageWindow.on('close', function(win){
										this.windowPanel.removeWindow(newMessageWindow);
									}, this);	
								},
								failure: function(response, options){
			                		Ext.Msg.alert('Error','Unable to Reply to message!');
					    		},
					    		scope: this,
								method: 'GET',
								nocache: false
							});
						},
			            failure: function(response, options){
			                Ext.Msg.alert('Error','Unable to Reply to message!');
					    },
					    scope: this
					});
				},
				scope: this
	        },
			{
				id: 'tbbuttonForwardMessage' + '_' + this.config.folder,
	            icon: 'images/Forward16.png', 
		        cls: 'x-btn-text-icon',
				text: 'Forward',
				disabled: true,
	        	tooltip: '<b>Forward</b><br/>Forward the message to new recipent(s)',
				enableToggle: false,
				pressed: false,
				handler: function() {
					var ix = this.mailbox.getSelectionModel().getSelected().get('msgidx');
					Ext.Ajax.request({
						url: 'readmsg.php',
						params: {folder:this.config.folder, ix: ix},
						success: function(response, options){
							var messageInfo = Ext.util.JSON.decode(response.responseText);
							Ext.Ajax.request({
								url: 'show_body.php',
								params: {
									ix: ix,
									folder: this.config.folder,
									block_images: !messageInfo.showImages,
									text: this.config.preferences.editorMode == 'text' ? true : false
								},
								success: function(response, options){
									var messageBody = response.responseText;
									var newMessageWindow = new Ext.ux.NewMessageWindow({
										type: 'forward',
										messageInfo: messageInfo,
										messageBody: messageBody,
										messagePrefix: 'FW: ',
										folder: this.config.folder,
										ix: ix,
										windowGroup: this.windowGroup,
										id: Ext.id(),
										preferences: this.config.preferences,
										contactsStore: this.config.contactsStore
									});
									newMessageWindow.on('sent', function(win){
										this.fireEvent('refresh', this);
									}, this);
									newMessageWindow.render(this.mailboxPane.body);
									this.windowPanel.addWindow(newMessageWindow);
									newMessageWindow.show();	
									newMessageWindow.on('close', function(win){
										this.windowPanel.removeWindow(newMessageWindow);
									}, this);		
								},
								failure: function(response, options){
			                		Ext.Msg.alert('Error','Unable to Forward to message!');
					    		},
					    		scope: this,
								method: 'GET',
								nocache: false
							});
						},
			            failure: function(response, options){
			                Ext.Msg.alert('Error','Unable to Forward to message!');
					    },
					    scope: this
					});
				},
				scope: this
	        },
	        '-',
	        {
				id: 'tbbuttonPrintMessage' + '_' + this.config.folder,
	            icon: 'images/Print16.png', 
		        cls: 'x-btn-text-icon',
				text: 'Print',
				disabled: true,
	        	tooltip: '<b>Print</b><br/>Print the message',
				enableToggle: false,
				pressed: false,
				handler: function() {
					if (this.bottom.isVisible()||this.right.isVisible()) {
						var win = window.open();
					    self.focus();
					    win.document.open();
					    win.document.write(this.config.printTemplate.apply({
					    	from: Ext.util.Format.htmlEncode(this.mailbox.getSelectionModel().getSelected().get('from')),
					    	to: Ext.util.Format.htmlEncode(this.mailbox.getSelectionModel().getSelected().get('to')),
					    	cc: Ext.util.Format.htmlEncode(this.mailbox.getSelectionModel().getSelected().get('cc')),
					    	subject: this.mailbox.getSelectionModel().getSelected().get('subject'),
					    	body: this.preview.getFrameDocument().body.innerMTML
					    }));
					    win.document.close();
					    win.print();
					    win.close();
					}
					else{
						Ext.Ajax.request({
							url: 'show_body.php',
							params: {
								ix: this.mailbox.getSelectionModel().getSelected().get('msgidx'),
								folder: this.config.folder,
								block_images: !this.mailbox.getSelectionModel().getSelected().get('showimages'),
								text: false
							},
							success: function(response, options){
								var win = window.open();
							    self.focus();
							    win.document.open();
							    win.document.write(this.config.printTemplate.apply({
							    	from: Ext.util.Format.htmlEncode(this.mailbox.getSelectionModel().getSelected().get('from')),
							    	to: Ext.util.Format.htmlEncode(this.mailbox.getSelectionModel().getSelected().get('to')),
							    	cc: Ext.util.Format.htmlEncode(this.mailbox.getSelectionModel().getSelected().get('cc')),
							    	subject: this.mailbox.getSelectionModel().getSelected().get('subject'),
							    	body: response.responseText
							    }));
							    win.document.close();
							    win.print();
							    win.close();
							},
							failure: function(response, options){
		                		Ext.Msg.alert('Error','Unable to Print message!');
				    		},
				    		scope: this,
							method: 'GET',
							nocache: false
						});	
					}
				},
				scope: this
	        },
	        '-',
	        {
	        	xtype: 'cycle',
				id: 'tbbuttonPreviewMessage' + '_' + this.config.folder,
	            prependText: 'Preview Messages - ',
	            cls: 'x-btn-text-icon',
	            showText: true,
				disabled: false,
	        	tooltip: '<b>Preview Messages</b><br/>Toggles the display of the Message Preview',
	        	items:[{
	        		text: 'Bottom',
	        		iconCls: 'icon-preview-bottom'
	        		
	        	},{
					text: 'Right',
					iconCls: 'icon-preview-right'
				},{
					text: 'Hide',
					iconCls: 'icon-preview-hide',
					checked: true
				}],
				changeHandler: function(btn, item) {
					if (item.text == 'Hide'){
						this.previewInfo.body.update('');
						this.previewBody.getFrame().update('');
					}
					else
					{
						if (this.mailbox.getSelectionModel().getCount() > 0){
							this.previewInfo.body.update(this.previewTemplate.apply({
						    	from: Ext.util.Format.htmlEncode(this.mailbox.getSelectionModel().getSelected().get('from')),
						    	to: Ext.util.Format.htmlEncode(this.mailbox.getSelectionModel().getSelected().get('to')),
						    	cc: Ext.util.Format.htmlEncode(this.mailbox.getSelectionModel().getSelected().get('cc')),
						    	subject: this.mailbox.getSelectionModel().getSelected().get('subject')
						    }));
					    	this.previewBody.load({
								url: 'show_body.php',
								params: {
									ix: this.mailbox.getSelectionModel().getSelected().get('msgidx'),
									folder: this.config.folder,
									block_images: !this.mailbox.getSelectionModel().getSelected().get('showimages'),
									text: false
								},
								method: 'GET',
								nocache: false
							});
						}
					}
		            switch(item.text){
		                case 'Bottom':
		                    this.right.hide();
		                    this.bottom.add(this.preview);
		                    this.bottom.show();
		                    break;
		                case 'Right':
		                    this.bottom.hide();
		                    this.right.add(this.preview);
		                    this.right.show();
		                    break;
		                case 'Hide':
		                    this.preview.ownerCt.hide();
		                    break;
		            }
		            this.doLayout();
		            this.mailboxPane.doLayout();
		            this.mailbox.doLayout();
				},
				scope: this
	        },
	        {
				id: 'tbbuttonAutoPreviewMessage' + '_' + this.config.folder,
	            icon: 'images/AutoPreview16.png', 
		        cls: 'x-btn-text-icon',
				text: 'AutoPreview',
				disabled: false,
				enableToggle: true,
				pressed: false,
	        	tooltip: '<b>AutoPreview</b><br/>Auto show a message preview beneath the row',
	        	toggleHandler: function(btn, pressed) {
	        		var view = this.mailbox.getView();
    				view.showPreview = pressed;
    				if (this.previewsLoaded) view.refresh();
    				else {
    					this.store.reload();
    				}
				},
				scope: this
	        }
	]});
	this.mailbox = new Ext.grid.GridPanel({
		id: 'gridMailbox' + '_' + this.config.folder,
		folder: this.config.folder,
		layout: 'fit',
		region: 'center',
		ddGroup:'MsgDD',
		autoExpandColumn: 'subject',
		deferredRender: true,
		monitorResize: true,
		enableDragDrop: true,
		store: this.store,
		cm: this.cm,
		view: new Ext.grid.GroupingView({
	        forceFit:false,
	        groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
			hideGroupedColumn: true,
			showGroupName: true,
			enableNoGroups: true,
			emptyText: 'There are no messages in this Mailbox',
			enableRowBody:true,
		    showPreview:false,
		    getRowClass : function(record, rowIndex, p, store){
		    	if(this.showPreview){
		         	p.body = '<div style="padding-left:100px">'+record.get('body')+'</div>';
		         	p.bodyStyle = 'padding-left:25px !important; color:blue';
		       		return 'x-grid3-row-expanded';
		      	}
		      	return 'x-grid3-row-collapsed';
		    }
	    }),
	    loadMask: {msg: 'Loading Messages'},
		sm: this.sm,
		frame:false,
		border: true,
		hideMode: 'offsets',
		stateful: false,
		tbar: this.tbar,
		bbar: new Ext.PagingToolbar({
			id: 'pagingMailbox' + '_' + this.config.folder,
			pageSize: Number(this.config.preferences.rpp),
			store: this.store,
            displayInfo: true,
            displayMsg: 'Displaying messages {0} - {1} of {2}',
            emptyMsg: "No messages to display",
            plugins: [new Ext.ux.PageSizePlugin()]
		}),
		plugins: [new Ext.ux.grid.Search({
            mode:'remote',
            minLength:2,
            position: 'top',
            checkIndexes: [this.config.groupField],
            width: 200,
            disableIndexes: ['priorimg', 'statusimg', 'attachimg', 'size'],
            align: 'right'
        })]
	});
	this.mailbox.ddText = "{0} selected message(s)";
	this.mailbox.on('rowdblclick', function(grid, rowIndex, e){
		var ix = this.store.getAt(rowIndex).get('msgidx');
		var id = this.id + '_' + ix;
		var msgWindow = this.windowGroup.get(id);
		if (msgWindow) {
			msgWindow.setActive(true);
			this.windowGroup.bringToFront(msgWindow);
		}
		else{
			if (!this.config.drafts){
				Ext.Ajax.request({
					url: 'readmsg.php',
					params: {folder:this.config.folder, ix: ix},
					success: function(response, options){
						var messageInfo = Ext.util.JSON.decode(response.responseText);
						msgWindow = new Ext.ux.MessageWindow({
							id: id,
							ix: ix,
							folder: this.config.folder,
							messageInfo: messageInfo,
							windowPanel: this.windowPanel,
							windowGroup: this.windowGroup,
							preferences: this.config.preferences,
							printTemplate: this.config.printTemplate,
							contactsStore: this.config.contactsStore
						});
						msgWindow.render(this.mailboxPane.body);
						this.windowPanel.addWindow(msgWindow);
						msgWindow.show();	
						this.store.getAt(rowIndex).set('read', true);
						msgWindow.on('close', function(win){
							this.windowPanel.removeWindow(msgWindow);
						}, this);		
					},
		            failure: function(response, options){
		                Ext.Msg.alert('Error','Unable to show message!');
				    },
				    scope: this
				});
		    }
			else{
				Ext.Ajax.request({
					url: 'readmsg.php',
					params: {folder:this.config.folder, ix: ix},
					success: function(response, options){
						var messageInfo = Ext.util.JSON.decode(response.responseText);						
						Ext.Ajax.request({
							url: 'show_body.php',
							params: {
								ix: ix,
								folder: this.config.folder,
								block_images: !messageInfo.showImages,
								text: false
							},
							success: function(response, options){
								var messageBody = response.responseText;
								var newMessageWindow = new Ext.ux.NewMessageWindow({
									type: 'draft',
									id: id,
									messageInfo: messageInfo,
									messageBody: messageBody,
									messagePrefix: '',
									windowGroup: this.WindowGroup,
									folder: this.config.folder,
									ix: ix,
									preferences: this.config.preferences,
									contactsStore: this.config.contactsStore
								});
								newMessageWindow.on('sent', function(win){
									this.fireEvent('refresh', this);
									this.fireEvent('refreshtree', this);
								}, this);
								newMessageWindow.render(this.mailboxPane.body);
								this.windowPanel.addWindow(newMessageWindow);
								newMessageWindow.show();	
								newMessageWindow.on('close', function(win){
									this.windowPanel.removeWindow(newMessageWindow);
								}, this);
							},
							failure: function(response, options){
		                		Ext.Msg.alert('Error','Unable to Reply to message!');
				    		},
				    		scope: this,
							method: 'GET',
							nocache: false
						});
					},
		            failure: function(response, options){
		                Ext.Msg.alert('Error','Unable to Reply to message!');
				    },
				    scope: this
				});
			}
		}
	}, this);
	this.mailbox.on('rowclick', function(){
		if (this.right.isVisible()||this.bottom.isVisible()){
			this.previewBody.load({
				url: 'show_body.php',
				params: {
					ix: this.mailbox.getSelectionModel().getSelected().get('msgidx'),
					folder: this.config.folder,
					block_images: !this.mailbox.getSelectionModel().getSelected().get('showimages'),
					text: false
				},
				method: 'GET',
				nocache: false
			});
			this.previewInfo.body.update(this.previewTemplate.apply({
		    	from: Ext.util.Format.htmlEncode(this.mailbox.getSelectionModel().getSelected().get('from')),
		    	to: Ext.util.Format.htmlEncode(this.mailbox.getSelectionModel().getSelected().get('to')),
		    	cc: Ext.util.Format.htmlEncode(this.mailbox.getSelectionModel().getSelected().get('cc')),
		    	subject: this.mailbox.getSelectionModel().getSelected().get('subject')
		    }));
		}
	},this);
	this.mailbox.getSelectionModel().on('selectionchange', function(sm){
		var tbItems = this.mailbox.getTopToolbar().items
		if (sm.getCount() > 0){
			tbItems.get('tbbuttonDeleteMessage' + '_' + this.config.folder).enable();
			tbItems.get('tbbuttonReplyToMessage' + '_' + this.config.folder).enable();
			tbItems.get('tbbuttonReplyAll' + '_' + this.config.folder).enable();
			tbItems.get('tbbuttonForwardMessage' + '_' + this.config.folder).enable();
			if (tbItems.get('tbbuttonSpam' + '_' + this.config.folder)) tbItems.get('tbbuttonSpam' + '_' + this.config.folder).enable();
			if (tbItems.get('tbbuttonNoSpam' + '_' + this.config.folder)) tbItems.get('tbbuttonNoSpam' + '_' + this.config.folder).enable();
			tbItems.get('tbbuttonPrintMessage' + '_' + this.config.folder).enable();
			if (sm.getCount()>1){
				tbItems.get('tbbuttonReplyToMessage' + '_' + this.config.folder).disable();
				tbItems.get('tbbuttonReplyAll' + '_' + this.config.folder).disable();
				tbItems.get('tbbuttonForwardMessage' + '_' + this.config.folder).disable();
				tbItems.get('tbbuttonPrintMessage' + '_' + this.config.folder).disable();
			}
		}
		else{
			tbItems.get('tbbuttonDeleteMessage' + '_' + this.config.folder).disable();
			tbItems.get('tbbuttonReplyToMessage' + '_' + this.config.folder).disable();
			tbItems.get('tbbuttonReplyAll' + '_' + this.config.folder).disable();
			tbItems.get('tbbuttonForwardMessage' + '_' + this.config.folder).disable();
			if (tbItems.get('tbbuttonSpam' + '_' + this.config.folder)) tbItems.get('tbbuttonSpam' + '_' + this.config.folder).disable();
			if (tbItems.get('tbbuttonNoSpam' + '_' + this.config.folder)) tbItems.get('tbbuttonNoSpam' + '_' + this.config.folder).disable();
			tbItems.get('tbbuttonPrintMessage' + '_' + this.config.folder).disable();
		}
	}, this);
	this.previewInfo = new Ext.Panel({
		id: 'panelPreviewInfo' + '_' + this.config.folder,
		region: 'north',
		split: false,
		autoHeight:true,
		minHeight: 85,
		maxHeight: 85,
		bodyStyle:'padding:5px; background-color:#FFFFFF; ',
		border: false,
		frame: false,
		hideMode: 'offsets',
		autoScroll: true
	});
	this.previewBody = new Ext.ux.ManagedIframePanel({
		id: 'iframepanelPreviewBody' + '_' + this.config.folder,
		region: 'center',
		split: false,
		bodyStyle:'padding:5px; background-color:#FFFFFF',
		border: false,
		frame: false,
		hideMode: 'offsets',
		autoScroll: true
	});
	this.preview = new Ext.Panel({
		id: 'panelPreview' + '_' + this.config.folder,
		border: false,
		layout: 'border',
		frame: false,
		autoScroll: false,
		hideMode: 'offsets',
		items: [
			this.previewInfo,
			this.previewBody
		],
		stateful: false
	});
	this.bottom = new Ext.Panel({
		id: 'panelBottom' + '_' + this.config.folder,
		region: 'south',
		split:true,
		height:300,
		minHeight: 200,
		bodyStyle:'padding:5px',
		border: true,
		layout: 'fit',
		frame: false,
		autoScroll: false,
		items: this.preview,
		stateful: false,
		hideMode: 'offsets',
		hidden: true
	});
	this.right = new Ext.Panel({
		id: 'panelRight' + '_' + this.config.folder,
		region: 'east',
		split:true,
		width:300,
		minWidth: 200,
		bodyStyle:'padding:5px',
		border: true,
		layout: 'fit',
		frame: false,
		hidden: true,
		hideMode: 'offsets',
		autoScroll: false,
		stateful: false
	});
	this.mailboxPane = new Ext.Panel({
		id: 'panelMailboxPane' + '_' + this.config.folder,
		region: 'center',
		border: false,
		frame: false,
		autoScroll: false,
		layout: 'border',
		hideMode: 'offsets',
		items: [
			this.mailbox,
			this.bottom,
			this.right
		]
	});
	this.windowGroup = new Ext.WindowGroup();
	this.windowPanel = new Ext.ux.WindowPanel(this.windowGroup, {
		id: 'windowpanelWindowPanel' + '_' + this.config.folder,
		region: 'south',
		bodyCls: 'x-plain',
		border: false,
		split: true,
		minHeigth: 85,
		height: 85,
		maxHeight: 85,
		hideMode: 'offsets',
		hidden: true
	});
	Ext.ux.MailBoxPanel.superclass.constructor.call(this, {
		id: 'panelMailboxPanel_' + this.config.folder,
		autoShow: true,
		title: this.config.title,
		border:false,
		layout: 'border',
		bodyStyle:'background-color:#DFE8F6',
		iconCls: this.config.iconCls,
		closable: true,
		stateful: false,
		hideMode: 'offsets',
		
		bbar: this.tb,
		items: [
			this.mailboxPane,
			this.windowPanel
		]	
	});
	this.mailbox.on('render', function(){
		var colm = this.mailbox.getColumnModel(); 
		colm.setColumnWidth(0, 20, false);
		colm.setColumnWidth(1, 35, false);
		colm.setColumnWidth(2, 35, false);
		colm = this.mailbox.getView();
		colm.autoExpand();
		
	}, this, {delay:10});
	
	
	
	
	
	
	
	
	this.mailbox.getTopToolbar().on('render',function(){
		if (this.config.folder =='INBOX'){
			this.mailbox.getTopToolbar().insertButton(3,{
				id: 'tbbuttonSpam' + '_' + this.config.folder,
	            icon: 'images/Spam16.png', 
		        cls: 'x-btn-text-icon',
				text: 'Spam',
				disabled: true,
	        	tooltip: '<b>Spam</b><br/>Mark the message(s) as Spam and move to Spam folder',
				enableToggle: false,
				pressed: false,
				handler: function() {
					var msgs = this.mailbox.getSelectionModel().getSelections();
					var msglist = '';
					for (i = 0; i < msgs.length; i++) {
						if (msglist.length > 0){
							msglist += ',';
						}
						msglist += msgs[i].get('msgidx');
					}
					Ext.Ajax.request({
						url: 'move_messages.php',
						params: {folder:this.config.folder, msgs: msglist, folderto: 'INBOX.Spam'},
						success: function(response, options){
							var responseData = Ext.util.JSON.decode(response.responseText);
							if (responseData.success){
							 	Ext.Msg.alert('Spam',msglist.length > 1 ? 'Messages marked as Spam successfully': 'Message marked as Spam successfully', function(){
									this.fireEvent('refresh', this);
									this.fireEvent('refreshtree', this);
								}, this);
							 }
							 else{
							 	Ext.Msg.alert('Error',msglist.length > 1 ? 'Unable to mark messages as Spam': 'Unable to mark message as Spam');
							 }
						},
			            failure: function(response, options){
			                Ext.Msg.alert('Error',msglist.length > 1 ? 'Unable to mark messages as Spam': 'Unable to mark message as Spam');
					    },
					    scope: this
					});
				},
				scope: this
	        }); 
		}
		if (this.config.folder =='INBOX.Spam'){
			this.mailbox.getTopToolbar().insertButton(3,{
				id: 'tbbuttonNoSpam' + '_' + this.config.folder,
	            icon: 'images/NoSpam16.png', 
		        cls: 'x-btn-text-icon',
				text: 'Not Spam',
				disabled: true,
	        	tooltip: '<b>Not Spam</b><br/>Mark the message as NOT Spam and move to Inbox',
				enableToggle: false,
				pressed: false,
				handler: function() {
					var msgs = this.mailbox.getSelectionModel().getSelections();
					var msglist = '';
					for (i = 0; i < msgs.length; i++) {
						if (msglist.length > 0){
							msglist += ',';
						}
						msglist += msgs[i].get('msgidx');
					}
					Ext.Ajax.request({
						url: 'move_messages.php',
						params: {folder:this.config.folder, msgs: msglist, folderto: 'INBOX'},
						success: function(response, options){
							var responseData = Ext.util.JSON.decode(response.responseText);
							if (responseData.success){
							 	Ext.Msg.alert('Spam',msglist.length > 1 ? 'Messages marked as NOT Spam successfully': 'Message marked as NOT Spam successfully', function(){
									this.fireEvent('refresh', this);
									this.fireEvent('refreshtree', this);
								}, this);
							 }
							 else{
							 	Ext.Msg.alert('Error',msglist.length > 1 ? 'Unable to mark messages as NOT Spam': 'Unable to mark message as NOT Spam');
							 }
						},
			            failure: function(response, options){
			                Ext.Msg.alert('Error',msglist.length > 1 ? 'Unable to mark messages as NOT Spam': 'Unable to mark message as NOT Spam');
					    },
					    scope: this
					});
				},
				scope: this
	        }); 
		}
	}, this, {delay: 1000});
	this.on('beforedestroy', function(){
		this.windowGroup.each(function(win){
			win.close();
		}, this);
	});
};
Ext.extend(Ext.ux.MailBoxPanel, Ext.Panel, {
	refreshMailbox: function(){
		this.store.reload();
	}
});

Ext.ux.AddFolderDialog = function(){
	this.addEvents({
		'refreshtree': true
	});
	this.doOK=function(){
		if(this.addFolderForm.getForm().isValid()){
			this.addFolderForm.getForm().submit({
				waitMsg:'Adding Folder...',
				success: function(form,action){
					Ext.Msg.alert('AddFolder','Folder: ' + this.newFolder.getValue() + ' added successfully', function(){
			            this.fireEvent('refreshtree', this);
						this.close();
			        }, this);
				},
				failure: function(form,action){
				    Ext.Msg.alert('Error',action.result.errormsg, function(){
			            this.newFolder.selectText();
			        }, this);
				},
				scope: this
			});
		}
	};
	this.newFolder=new Ext.form.TextField({
		id: 'textfieldNewFolder',
		msgTarget:'side',
		fieldLabel:'Folder name',
		name:'newfolder',
		width:175,
		allowBlank:false,
		value: '',
		invalidText:'Please enter a Folder name!'
	});
	this.addFolderForm = new Ext.FormPanel({
		id: 'formAddFolder',
        labelWidth: 75, 
        url:'newfolder.php',
        defaults: {width: 230},
		baseCls: 'x-plain',
        defaultType: 'textfield',
        labelAlign: 'left',
		bodyStyle: {
			padding: '10px'	
		},
		border: false,
		items: [
			this.newFolder
		]
	});
	Ext.ux.AddFolderDialog.superclass.constructor.call(this, {
		id: 'windowAddFolderDialog',
		width:400,
		autoHeight:true,
		modal:true,
		shadow:true,
		bodyBorder: false,
		plain:true,
		collapsible:false,
		resizable:false,
		closable:true,
		title:'Add Folder',
		iconCls: 'addfolder-icon-cls',
		plugins:[new Ext.ux.IconMenu({})],
		buttons:[
		{ 
			text: 'Add Folder',
			handler: this.doOK,
			scope: this
		},
		{
			text: 'Cancel',
			handler:  function(){
				this.close();
			},
			scope: this
		}],
		items: this.addFolderForm
	});
}
Ext.extend(Ext.ux.AddFolderDialog, Ext.Window,{
	show:function(){
		Ext.ux.AddFolderDialog.superclass.show.call(this);
		this.addFolderForm.getForm().reset();
		this.newFolder.focus(false, true);
	}
});

Ext.ux.MailboxTreePanel = function(config){
	this.config = config || {};
	this.addEvents({
		'refreshtree': true,
		'refresh': true,
		'reloadtree': true,
		'removefolder': true
	});
	this.tree = new Ext.tree.TreePanel({
		id: 'treepanelMailboxTree',
		region: 'center',
		ddGroup:'MsgDD',
		autoShow: true,
		iconCls: 'mail-icon-cls',
        margins:'0 0 0 0',
        
        animate:true,
		autoScroll:true,
        loader: new Ext.tree.TreeLoader({dataUrl:'folders.php', preloadChildren: true}),
        enableDrag:false,
		hideMode: 'visibility',
		enableDrop: true,
        containerScroll: true,
        border:false,
        bodyStyle:'background-color: white',
		pathSeparator: '|'
    });
	this.tree.getLoader().requestMethod = 'GET';
	this.tree.on('beforeexpandnode', function(node){
		if (node.id == 'mailbox'){
			return true;
		}
		else{
			return false;
		}
	}, this);
	this.tree.on('nodedragover', function(dragOverEvent){
		if (dragOverEvent.data.grid.folder == dragOverEvent.target.id) return false;
		return true;
	}, this);
	this.tree.on('beforenodedrop', function(dropEvent){
		var waitMsg = Ext.MessageBox.wait('Please Wait...', 'Moving Messages...',{text:'Moving Messages...'});
		var msglist = '';
		for (i = 0; i < dropEvent.data.selections.length; i++) {
			if (msglist.length > 0){
				msglist += ',';
			}
			msglist += dropEvent.data.selections[i].get('msgidx');
		}
		Ext.Ajax.request({
			url: 'move_messages.php',
			params: {folder:dropEvent.data.grid.folder, msgs: msglist, folderto: dropEvent.target.id},
			success: function(response, options){
				var responseData = Ext.util.JSON.decode(response.responseText);
				if (responseData.success){
				 	Ext.Msg.alert('Move Messages',msglist.length > 1 ? 'Messages moved to folder ' + dropEvent.target.text + ' successfully': 'Message moved to folder ' + dropEvent.target.text + ' successfully', function(){
				 		waitMsg.hide();
						this.fireEvent('refresh', this);
						this.fireEvent('refreshtree', this);
					}, this);
				 }
				 else{
				 	waitMsg.hide();
				 	Ext.Msg.alert('Error',msglist.length > 1 ? 'Unable to move messages to folder ' + dropEvent.target.text : 'Unable to move message to folder ' + dropEvent.target.text);
				 }
			},
            failure: function(response, options){
            	waitMsg.hide();
                Ext.Msg.alert('Error',msglist.length > 1 ? 'Unable to move messages to folder ' + dropEvent.target.text : 'Unable to move message to folder ' + dropEvent.target.text);
		    },
		    scope: this,
		    timeout: 60000
		});
	}, this);
    this.sroot = new Ext.tree.AsyncTreeNode({
        text: 'Mailbox - ' + this.config.username,
        draggable:false,
        id:'mailbox'
    });
	this.tree.setRootNode(this.sroot);
	this.addFolder = new Ext.menu.Item({
		icon: 'images/AddFolder16.png',
		text: 'Add Folder',
		handler: function (){
			var addFolderDialog = new Ext.ux.AddFolderDialog();
			addFolderDialog.on('refreshtree', function(){
				this.fireEvent('reloadtree', this);
			}, this);
			addFolderDialog.show();
		},
		scope: this
	});
	this.emptyFolder = new Ext.menu.Item({
		icon: 'images/EmptyFolder16.png',
		text: 'Empty Folder',
		handler: function (){
			var waitMsg = Ext.MessageBox.wait('Please Wait...', 'Emptying Folder...',{text:'Emptying Folder...'});
			Ext.Ajax.request({
				url: 'emptyfolder.php',
				params: {empty:this.contextNode.id},
				success: function(response, options){
					var responseData = Ext.util.JSON.decode(response.responseText);
					if (responseData.success){
					 	Ext.Msg.alert('Empty Folder','Folder ' + this.contextNode.text + ' emptied successfully', function(){
					 		waitMsg.hide();
							this.fireEvent('refresh', this);
							this.fireEvent('refreshtree', this);
						}, this);
					 }
					 else{
					 	waitMsg.hide();
					 	Ext.Msg.alert('Error','Unable to empty folder ' + this.contextNode.text);
					 }
				},
	            failure: function(response, options){
	            	waitMsg.hide();
	                Ext.Msg.alert('Error','Unable to empty folder ' + this.contextNode.text);
			    },
			    scope: this,
			    timeout: 60000
			});
		},
		scope: this
	});
	this.deleteFolder = new Ext.menu.Item({
		icon: 'images/DeleteFolder16.png',
		text: 'Delete Folder',
		handler: function (){
			var waitMsg = Ext.MessageBox.wait('Please Wait...', 'Deleting Folder...',{text:'Deleting Folder...'});
			Ext.Ajax.request({
				url: 'deletefolder.php',
				params: {delfolder:this.contextNode.id},
				success: function(response, options){
					var responseData = Ext.util.JSON.decode(response.responseText);
					if (responseData.success){
					 	Ext.Msg.alert('Delete Folder','Folder ' + this.contextNode.text + ' deleted successfully', function(){
					 		waitMsg.hide();
							this.fireEvent('removefolder', this.contextNode.id);
							this.fireEvent('reloadtree', this);
						}, this);
					 }
					 else{
					 	waitMsg.hide();
					 	Ext.Msg.alert('Error','Unable to delete folder ' + this.contextNode.id);
					 }
				},
	            failure: function(response, options){
	            	waitMsg.hide();
	                Ext.Msg.alert('Error','Unable to delete folder ' + this.contextNode.id);
			    },
			    scope: this,
			    timeout: 60000
			});
		},
		scope: this
	});
	this.contextMenu = new Ext.menu.Menu({
		items: [
			this.emptyFolder,
			'-',
			this.addFolder,
			this.deleteFolder
		]
	});
	this.tree.on('contextmenu', function(node, e){
		this.contextNode = node
		if (node.attributes.system){
			this.emptyFolder.enable();
			this.addFolder.disable();
			this.deleteFolder.disable();
		}
		else{
			if (node.id == 'mailbox' ){
				this.emptyFolder.disable();
				this.deleteFolder.disable();
			}
			else
			{
				this.emptyFolder.enable();
				this.deleteFolder.enable();
			}
			this.addFolder.enable();
		}
		this.contextMenu.showAt(e.getXY());
	}, this);
	this.quotaPanel = new Ext.Panel({
		id: 'panelQuotaPanel',
		region:'south',
		height:120,
		minHeight:120,
		maxHeight:120,
		collapsible: true,
		bodyStyle:'background-color: transparent',
		split:false,
		title: 'Mailbox Quota',
		border:true,
		frame:true,
		autoLoad: {
			url: 'get_quota.php',
			scripts: true,
			scope: this,
			nocache: false
		}
	});
	Ext.ux.MailboxTreePanel.superclass.constructor.call(this,{
		id: 'panelMailboxTreePanel',
		title: 'Mail',
		layout: 'border',
		bodyStyle:'background-color: transparent',
		items: [
			this.tree,
			this.quotaPanel
		]
	});
};
Ext.extend(Ext.ux.MailboxTreePanel, Ext.Panel, {
	afterRender: function(){
		Ext.ux.MailboxTreePanel.superclass.afterRender.call(this);
		this.sroot.expand(false, false);
	},
	refreshTree: function(){
		this.sroot.reload();
	},
	reloadTree: function(){
		this.tree.getLoader().on('beforeload', this.reloadParams, this);
		this.sroot.reload();
		if (this.tree.getLoader().baseParams.refresh) delete this.tree.getLoader().baseParams.refresh;
		this.tree.getLoader().un('beforeload', this.reloadParams, this);
	},
	refreshQuota: function(quota){
		this.quotaPanel.getUpdater().refresh();
	},
	reloadParams: function(loader, node){
		this.getLoader().baseParams.refresh = true;
	},
	selectNode: function(id){
		this.tree.getNodeById(id).select();
	}
});

Ext.ux.PreferencesWindow = function(preferences){
	this.addEvents({
		'saved': true
	});
	this.doSave = function(){
		var preForm = this.preferencesForm.getForm();
		this.preferences.realName = preForm.findField('realName').getValue();
		this.preferences.replyTo = preForm.findField('replyTo').getValue();
		var editorRadio = preForm.findField('editorMode');
		if (editorRadio.getValue()){
			this.preferences.editorMode = 'html';
		}
		else{
			this.preferences.editorMode = 'text';
		}
		this.preferences.saveToTrash = preForm.findField('saveToTrash').getValue();
		this.preferences.stOnlyRead = preForm.findField('stOnlyRead').getValue();
		this.preferences.emptyTrash = preForm.findField('emptyTrash').getValue();
		this.preferences.addSig = preForm.findField('addSig').getValue();
		this.preferences.signature = preForm.findField('signature').getValue();
		Ext.Ajax.request({
            url:'preferences.php',
            params:{
				realName: this.preferences.realName,
				replyTo: this.preferences.replyTo,
				editorMode: this.preferences.editorMode,
				saveToTrash: this.preferences.saveToTrash,
				stOnlyRead: this.preferences.stOnlyRead,
				emptyTrash: this.preferences.emptyTrash,
				saveToSent: this.preferences.saveToSent,
				addSig: this.preferences.addSig,
				signature: this.preferences.signature,
				sortBy: this.preferences.sortBy,
				sortOrder: this.preferences.sortOrder,
				rpp: this.preferences.rpp,
				timezone: this.preferences.timezone,
				displayImages: this.preferences.displayImages,
				refreshTime: this.preferences.refreshTime
            },
			method: 'POST',
            success: function (response, options){
				 var responseData = Ext.util.JSON.decode(response.responseText);
				 if (responseData.success){
				 	Ext.Msg.alert('Preferences','Preferences successfully saved', function(){
						this.fireEvent('saved', this, this.preferences);
						this.close();
					}, this);
				 }
				 else{
				 	Ext.Msg.alert('Error','Unable to save Preferences', function(){
						this.close();
					}, this);
				 }
            },
            failure: function(response, options){
                Ext.Msg.alert('Error','Unable to save Preferences', function(){
					this.close();
				}, this);
		    },
		    scope: this
        });
	};
	this.preferences = preferences;
	this.realName = this.preferences.realName;
	this.replyTo = this.preferences.replyTo;
	if (this.preferences.editorMode == 'html'){
		this.editorMode = true;
	}
	else{
		this.editorMode = false;
	}
	this.saveToTrash = this.preferences.saveToTrash;
	this.stOnlyRead = this.preferences.stOnlyRead;
	this.emptyTrash = this.preferences.emptyTrash;
	this.addSig = this.preferences.addSig;
	this.signature = this.preferences.signature;
	this.preferencesForm = new Ext.FormPanel({
		autoHeight: true,
		bodyStyle:'padding:10px;',
		baseCls: 'x-plain',
        labelWidth: 55,
        items: [
			{
			xtype: 'fieldset',
			baseClass: 'x-plain',
			bodyStyle: 'background: transparent !important',
			title: 'General options',
			autoHeight: true,
			items: [
				{
					xtype: 'textfield',
		            fieldLabel: 'Name',
		            name: 'realName',
					value: this.realName,
		            anchor:'100%'  
		        },
				{
					xtype: 'textfield',
		            fieldLabel: 'Reply to',
		            name: 'replyTo',
					value: this.replyTo,
		            anchor:'100%'  
		        },
				{
					xtype: 'fieldset',
					title: 'Editor mode',
					baseClass: 'x-plain',
					bodyStyle: 'background: transparent !important',
					hideLabels: true,
					autoHeight: true,
					items:[
						{
							xtype: 'radio',
							name: 'editorMode',
							boxLabel: 'HTML',
							checked: this.editorMode
						},
						{
							xtype: 'radio',
							name: 'editorMode',
							boxLabel: 'Text',
							checked: !this.editorMode
						}
					]
				}	
			]
		},
		{
			xtype: 'fieldset',
			title: 'Trash options',
			autoHeight: true,
			hideLabels: true,
			items:[
				{
					xtype: 'checkbox',
					name: 'saveToTrash',
					boxLabel: 'Move <b>deleted messages</b> to Trash',
					checked: this.saveToTrash
				},
				{
					xtype: 'checkbox',
					name: 'stOnlyRead',
					boxLabel: 'Messages in Trash <b>read only</b>',
					checked: this.stOnlyRead
				},
				{
					xtype: 'checkbox',
					name: 'emptyTrash',
					boxLabel: 'Empty Trash on <b>logout</b>',
					checked: this.emptyTrash
				}
			]
		},
		{
			xtype: 'fieldset',
			title: 'Signature options',
			height: 190,
			hideLabels: false,
			labelAlign: 'top',
			items:[
				{
					xtype: 'checkbox',
					hideLabel: true,
					name: 'addSig',
					boxLabel: 'Add signature',
					checked: this.addSig
				},
				{
					xtype: 'htmleditor',
					fieldLabel: 'Signature',
					name: 'signature',
					autoHeight: false,
					height: 100,
					value: this.signature,
					width: 450,
					enableLists: false,
					enableSourceEdit: false
					
				}
			]
		}]
    });
	Ext.ux.PreferencesWindow.superclass.constructor.call(this,{
		id: 'windowPreferences',
		title: 'Preferences',
        width: 510,
        height: 570,
		bodyBorder: false,
        minWidth: 300,
        stateful: false,
        minHeight: 200,
        layout: 'fit',
		modal: true,
		iconCls: 'preferences-icon-cls',
        plain:true,
        buttonAlign:'right',
		maximizable: false,
		resizable: false,
		plugins:[new Ext.ux.IconMenu({})],
		buttons:[
			{
				text: 'Save',
				handler: this.doSave,
				scope: this
			},
			{
				text: 'Cancel',
				handler: function(){
					this.close();
				},
				scope: this
			}		
		],
        items: [
			this.preferencesForm
		]
	});
}
Ext.extend(Ext.ux.PreferencesWindow, Ext.Window);

Ext.ux.MapAccordionPanel = function(config){
	this.config = config || {};
	this.textFindAddress = new Ext.form.TextField({
		id: 'texfieldFindAddress',
		name: 'Address',
		hideLabel: true,
		value: ''
	});
	this.findAddressForm = new Ext.form.FormPanel({
		id: 'formFindAddress',
		title: 'Find Address',
		collapsible: true,
		autoHeight: true,
		border:true,
		frame: true,
		buttonAlign: 'center',
		bodyStyle: 'text-align:center;background-color:#ffffff;padding:5px',
		buttons:[
			{
				id: 'buttonFindAddress',
				text: 'Find',
				handler: function(){ 
					this.config.mapPanel.getFrame().execScript('findAddress("' + this.findAddressForm.getForm().findField('Address').getValue() + '")');
				},
				scope: this
			}
		],
		items:[
			this.textFindAddress 
		]
	});
	this.textFrom = new Ext.form.TextField({
		id: 'textfieldDirectionsFrom',
		name: 'From',
		hideLabel: true,
		value:''
	});
	this.textTo = new Ext.form.TextField({
		id: 'textfieldDirectionsTo',
		name: 'To',
		hideLabel: true,
		value:''
	});
	this.showDirectionsForm = new Ext.form.FormPanel({
		id: 'formShowDirections',
		title: 'Show Directions',
		collapsible: true,
		autoHeight: true,
		border:true,
		frame: true,
		buttonAlign: 'center',
		bodyStyle: 'text-align:center;background-color:#ffffff;padding:5px',
		buttons:[
			{
				id: 'buttonShowDirections',
				text: 'Show',
				handler: function(){ 
					this.config.mapPanel.getFrame().execScript('showDirections("' + this.showDirectionsForm.getForm().findField('From').getValue() + ' to '+ this.showDirectionsForm.getForm().findField('To').getValue() +'")');
				},
				scope: this
			}
		],
		items:[
			this.textFrom,
			new Ext.BoxComponent({autoEl: {cn: 'To'}}),
			this.textTo
		]
	});
	Ext.ux.MapAccordionPanel.superclass.constructor.call(this, {
		id: 'panelMapAccordionPanel',
		title: 'Maps',
		iconCls: 'map-icon-cls',
		autoShow: true,
		hideMode: 'visibility',
		bodyStyle:'background-color: transparent',
		layout: 'accordion',
		layoutConfig: {
	        titleCollapse: true,
			hideCollapseTool: false,
	        animate: true,
	        activeOnTop: true,
			renderHidden: false,
			border:true
		},
		items: [
			this.findAddressForm,
			this.showDirectionsForm
		]
	});
};
Ext.extend(Ext.ux.MapAccordionPanel, Ext.Panel);

 
regionData = new Ext.data.SimpleStore({
	fields: ['display', 'value'],
	data: [
		['Entire Great Britain (slow)', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/rtm_tpeg.xml'],
		['Motorways', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/motorways_tpeg.xml'],
		['Beds, Herts and Bucks', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/bedshertsandbucks_tpeg.xml'],
		['Bedfordshire', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/bedfordshire_tpeg.xml'],
		['Berkshire', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/berkshire_tpeg.xml'],
		['Birmingham', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/birmingham_tpeg.xml'],
		['Bradford', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/bradford_tpeg.xml'],
		['Bristol', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/bristol_tpeg.xml'],
		['Buckinghamshire', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/buckinghamshire_tpeg.xml'],
		['Cambridgeshire', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/cambridgeshire_tpeg.xml'],
		['Cheshire', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/cheshire_tpeg.xml'],
		['Cornwall', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/cornwall_tpeg.xml'],
		['Coventry and Warwickshire', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/coventry_tpeg.xml'],
		['Cumbria', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/cumbria_tpeg.xml'],
		['Derbyshire', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/derby_tpeg.xml'],
		['Devon', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/devon_tpeg.xml'],
		['Dorset', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/dorset_tpeg.xml'],
		['Essex', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/essex_tpeg.xml'],
		['Gloucestershire', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/gloucestershire_tpeg.xml'],
		['Hereford and Worcester', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/herefordandworcester_tpeg.xml'],
		['Hertfordshire', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/hertfordshire_tpeg.xml'],
		['Humber', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/humber_tpeg.xml'],
		['Kent', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/kent_tpeg.xml'],
		['Lancashire', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/lancashire_tpeg.xml'],
		['Leeds', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/leeds_tpeg.xml'],
		['Leicestershire', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/leicester_tpeg.xml'],
		['Lincolnshire', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/lincolnshire_tpeg.xml'],
		['Liverpool', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/liverpool_tpeg.xml'],
		['Manchester', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/manchester_tpeg.xml'],
		['Nofolk', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/norfolk_tpeg.xml'],
		['Northamptonshire', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/northamptonshire_tpeg.xml'],
		['Nottingham', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/nottingham_tpeg.xml'],
		['Oxford', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/oxford_tpeg.xml'],
		['Shropshire', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/shropshire_tpeg.xml'],
		['Somerset', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/somerset_tpeg.xml'],
		['Southampton', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/southampton_tpeg.xml'],
		['South Yorkshire', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/southyorkshire_tpeg.xml'],
		['Stoke and Stafford', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/stoke_tpeg.xml'],
		['Suffolk', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/suffolk_tpeg.xml'],
		['Surrey', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/surrey_tpeg.xml'],
		['Sussex', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/sussex_tpeg.xml'],
		['Tees', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/tees_tpeg.xml'],
		['Tyne', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/tyne_tpeg.xml'],
		['Wear', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/wearside_tpeg.xml'],
		['Wiltshire', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/wiltshire_tpeg.xml'],
		['the Black Country', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/wolverhampton_tpeg.xml'],
		['North Yorkshire', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/york_tpeg.xml'],
		['All of London', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/london_tpeg.xml'],
		['Central London', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/londoncentral_tpeg.xml'],
		['East London', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/londoneast_tpeg.xml'],
		['London M25', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/londonm25_tpeg.xml'],
		['North London', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/londonnorth_tpeg.xml'],
		['South London', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/londonsouth_tpeg.xml'],
		['West London', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/londonwest_tpeg.xml'],
		['Borders', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/borders_tpeg.xml'],
		['Central and Fife', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/csescotland_tpeg.xml'],
		['Edinburgh and Lothian', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/lothian_tpeg.xml'],
		['Highlands and Northern Isles', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/highlands_tpeg.xml'],
		['North East', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/nescotland_tpeg.xml'],
		['Perth and Tayside', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/cescotland_tpeg.xml'],
		['South West and Ayrshire', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/swscotland_tpeg.xml'],
		['Strathclyde', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/clyde_tpeg.xml'],
		['Western Highlands and Islands', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/nwscotland_tpeg.xml'],
		['All Scotland (Cycling)', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/scotlandcycling_tpeg.xml'],
		['Mid Wales', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/midwales_tpeg.xml'],
		['North East Wales', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/northeastwales_tpeg.xml'],
		['North West Wales', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/northwestwales_tpeg.xml'],
		['South East Wales', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/southeastwales_tpeg.xml'],
		['South West Wales', 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/southwestwales_tpeg.xml']
	]
});
Ext.ux.MapPanel = function(){

	this.topbar = new Ext.Toolbar({
		items: [
			{
				id: 'tbbuttonShowLocalSearch',
	            icon: 'images/Search16.png', 
		        cls: 'x-btn-text-icon',
				text: 'Show Local Search',
				disabled: false,
	        	tooltip: '<b>Show Local Search</b><br/>Show or Hide the Local Search Window',
				enableToggle: true,
				pressed: true,
				handler: function() {
					this.getFrame().execScript('showSearchWindow()');
				},
				scope: this
			},
			'-',
			{
				id: 'tbbuttonShowOverview',
	            icon: 'images/Overview16.png', 
		        cls: 'x-btn-text-icon',
				text: 'Show Overview',
				disabled: false,
	        	tooltip: '<b>Show Overview</b><br/>Show or Hide the Overview',
				enableToggle: true,
				pressed: true,
				handler: function(button) {
					this.getFrame().execScript('showOverview(' + button.pressed + ')');
				},
				scope: this
			},
			'->',
			{
	        	xtype: 'cycle',
				id: 'tbbuttonnMapType',
	            prependText: 'Map Type - ',
	            cls: 'x-btn-text',
	            showText: true,
				disabled: false,
	        	tooltip: '<b>Map Type</b><br/>Toggles the display of the different map types',
	        	items:[{
	        		text: 'Map',
	        		
	        		checked: true
	        	},{
					text: 'Satellite'
					
				},{
					text: 'Hybrid'
					
					
				},{
					text: 'Terrain'
				}],
				changeHandler: function(btn, item) {
					this.getFrame().execScript('showMapType("' + item.text + '")');
				},
				scope: this
	        },
	        '-',
	        'Traffic Data:',
	        ' ',
	        ' ',
	        ' ',
	        ' '
		]
	});
	this.comboRegion = new Ext.form.ComboBox({
		id: 'comboRegion',
		store: regionData,
		displayField:'display',
		typeAhead: true,
		mode: 'local',
		triggerAction: 'all',
		selectOnFocus:true,
		resizable:false,
		listWidth: 200,
		width: 200,
		valueField: 'value',
		value: 'www.bbc.co.uk/travelnews/tpeg/en/local/rtm/norfolk_tpeg.xml'
	});
    this.comboRegion.on('select', function(combo){
		this.getFrame().execScript('getTrafficData("' + combo.getValue() + '")');
    }, this);
    Ext.ux.MapPanel.superclass.constructor.call(this,{
		id: 'iframepanelMapPanel',
		tbar : this.topbar,
		title: 'Maps',
		autoScroll: false,
		deferredRender: false,
		border: true,
		bodyStyle: 'padding: 5px;',
		loadMask: true
	});
}
Ext.extend(Ext.ux.MapPanel, Ext.ux.ManagedIframePanel,{
	addRegionCombo: function(){
		this.getTopToolbar().addField(this.comboRegion);
	}
});

Ext.Ajax.timeout = 60000;
Ext.Ajax.on('requestexception', function(conn, response, options){
	switch(response.status){
		case 401:
			delete options.failure;
			Ext.Msg.alert('Error','Your Session has expired please login again', function(){
		    	Ext.state.Manager.clear('SessionID');
				window.location.reload();
			}, this);
			break;
	} 	
},this);
themeData = new Ext.data.SimpleStore({
	fields: ['display', 'value'],
	data: [
		['Default (Blue)', '../css/xtheme-default.css'],
		['Black', '../css/xtheme-black.css'],
		['Dark Gray', '../css/xtheme-darkgray.css'],
		['Indigo', '../css/xtheme-indigo.css'],
		['Midnight', '../css/xtheme-midnight.css'],
		['Olive', '../css/xtheme-olive.css'],
		['Pink', '../css/xtheme-pink.css'],
		['Purple', '../css/xtheme-purple.css'],
		['Silver Cherry', '../css/xtheme-silverCherry.css'],
		['Slate', '../css/xtheme-slate.css']
	]
});
contactsStore = new Ext.data.JsonStore({
	autoLoad: false,
	url: 'quick_address.php',
	root: 'addresses',
	fields: ['name', 'email', 'street', 'city', 'state', 'work'],
	method: 'GET'
});
printTemplate = new Ext.XTemplate(
	'<html>',
		'<head>',
			'<style>',
				'body, td { font-family: Verdana; font-size: 10pt;}',
			'</style>',
		'</head>',
		'<body>',
			'<div>',
				'<table>',
					'<tr>',
						'<td width="75px">From:</td>',
						'<td>{from}</td>',
					'</tr>',
					'<tr>',
						'<td width="75px">To:</td>',
						'<td>{to}</td>',
					'</tr>',
					'<tr>',
						'<td width="75px">Cc:</td>',
						'<td>{cc}</td>',
					'</tr>',
					'<tr>',
						'<td width="75px">Subject:</td>',
						'<td>{subject}</td>',
					'</tr>',
				'</table>',
				'<hr>',
			'</div>',
			'<div>{body}</div>',
		'</body>',
	'</html>'
);
printTemplate.compile();
Main=function(){
    return{
		init:function(){
			Ext.QuickTips.init();
			contactsStore.load();
			this.mapFirstActive = true;
            this.mailboxTree = new Ext.ux.MailboxTreePanel({
            	username: Ext.state.Manager.get('UserName')
            });
			this.accordionContacts = new Ext.ux.ContactsAccordionPanel();
			this.centerMapPanel = new Ext.ux.MapPanel();
			this.accordionMaps = new Ext.ux.MapAccordionPanel({
				mapPanel: this.centerMapPanel
			});
			this.accordion = new Ext.Panel({
				id: 'panelAccordion',
				region: 'west',
			    layout:'accordion',
				width: 200,
				minSize: 150,
				maxSize: 300,
				split: true,
				frame: false,
				collapsible: true,
			    defaults: {
			        bodyStyle: 'background: transparent;',
					border:true,
					frame:true
			    },
			    layoutConfig: {
			        titleCollapse: true,
					hideCollapseTool: false,
			        animate: true,
			        activeOnTop: true,
					renderHidden: false,
					border:true
				},
			    items: [
					this.mailboxTree,
					this.accordionContacts,
					this.accordionMaps
				]
			});
			this.inboxPanel = new Ext.ux.MailBoxPanel({
				folder:'INBOX', 
				title: 'Inbox', 
				iconCls: 'icon-inbox', 
				preferences: Ext.state.Manager.get('Preferences'), 
				printTemplate: printTemplate, 
				contactsStore: contactsStore, 
				system: true, 
				groupField: 'from', 
				drafts: false,
				dateTitle: 'Received'
			});
			this.centerTabPanel = new Ext.TabPanel({
				id: 'tabpanelMailboxes',
				autoShow: true,
                deferredRender:false,
				enableTabScroll:true,
				monitorResize: true,
				border: true,
                activeTab:0,
                hideMode: 'offsets',
				items: [this.inboxPanel]
            });
			this.centerContactsPanel = new Ext.ux.ContactsPanel({contactsStore: contactsStore});
			this.centerPanel = new Ext.Panel({
				id: 'panelCenterPanel',
				baseCls: 'x-plain',
				deferredRender: false,
				region:'center',
				autoShow: true,
				activeItem: 0,
				border: false,
				layout: 'card',
				hideMode: 'offsets',
				items: [
					this.centerTabPanel,
					this.centerContactsPanel,
					this.centerMapPanel
				]
			});
			this.northToolbar = new Ext.Toolbar({
				id: 'toolbarNorthToolbar',
				items: [
			        {
			        	id: 'tbbuttonLogout',
			        	text: 'Logout',
			            icon: 'images/Logout16.png', 
				        cls: 'x-btn-text-icon',
			        	tooltip: '<b>Logout</b><br/>Logout of the E-mail application',
						enableToggle: false,
						pressed: false,
						handler: function() {
							var waitMsg = Ext.MessageBox.wait('Please Wait...', 'Logging Out...',{text:'Logging Out...'});
							Ext.Ajax.request({
					            url:'logout.php',
								method: 'GET',
					            success: function (response, options){
									 var responseData = Ext.util.JSON.decode(response.responseText);
									 if (responseData.success){
									 	waitMsg.hide();
										window.location.reload();
									 }
					            },
					            failure: function(response, options){
					            	waitMsg.hide();
					                Ext.Msg.alert('Error','Unable to Logout!');
							    },
							    scope: this
					        });
						},
						scope: this
			        }
			    ]
			});
		   	this.viewport = new Ext.Viewport({
		   		id: 'viewportMain',
		   		cls: 'x-plain',
	            layout:'border',
				monitorResize: true,
	            items:[
	                { 
	                	id: 'panelNorth',
	                    region:'north',
						autoShow: true,
						contentEl: 'header',
						split:false,
	                    height:71,
						minSize: 71,
						maxSize:71,
						border: false,
						collapsible: false,
						margins:'0 0 0 0',
						bbar: this.northToolbar
	                },
					{ 
						id: 'panelSouth',
	                    region:'south',
						contentEl: 'footer',
						split:false,
	                    height:24,
						minSize: 24,
						maxSize:24,
						border: false,
						collapsible: false,
						margins:'0 0 0 0'
	                },
					this.accordion,
	           		this.centerPanel     
	             ]
	        });
            this.mailboxTree.on('refresh', function(){
				this.refreshMailboxes();
			}, this);
			this.mailboxTree.on('refreshtree', function(){
				this.refreshMailboxTree();
			},this);
			this.mailboxTree.on('reloadtree', function(){
				this.reloadMailboxTree();
			},this);
			this.mailboxTree.on('removefolder', function(folderId){
				var comp = Ext.getCmp(folderId); 
				if (comp) this.centerTabPanel.remove(comp, true);
			}, this);
			this.mailboxTree.on('expand', function(){
				if (this.centerPanel.rendered) this.centerPanel.getLayout().setActiveItem(0);
			},this);
			this.mailboxTree.tree.on('click', function(newNode, e){
				if (newNode.isLeaf()){
					var comp = Ext.getCmp('panelMailboxPanel_' + newNode.id); 
					if (!comp){
 					    newComp = this.centerTabPanel.add(new Ext.ux.MailBoxPanel({
 					    	folder: newNode.id, 
 					    	title: newNode.text, 
 					    	iconCls: newNode.attributes.iconCls, 
 					    	preferences: Ext.state.Manager.get('Preferences'), 
 					    	printTemplate: printTemplate, 
 					    	contactsStore: contactsStore, 
 					    	system: newNode.attributes.system, 
 					    	groupField: newNode.attributes.groupField, 
 					    	drafts: newNode.attributes.drafts, 
 					    	dateTitle: newNode.attributes.dateTitle
 					    }));
						this.centerTabPanel.setActiveTab(newComp);
						if (this.centerTabPanel.hidden){
							this.centerTabPanel.show();
							this.centerTabPanel.doLayout();
							this.centerPanel.getLayout().setActiveItem(0);
							this.centerPanel.doLayout();
						}
						this.mailboxTree.selectNode(newNode.id);
						newComp.on('activate', function(){
							this.mailboxTree.selectNode(newNode.id);
						}, this);
						newComp.on('refresh', function(mailbox){
							this.refreshMailboxes();
						}, this);
						newComp.on('refreshtree', function(){
							this.refreshMailboxTree();
						},this);
						newComp.on('selectnode', function(panel){
							this.mailboxTree.selectNode(panel.config.folder);
						},this);
					}
					else{
						this.centerTabPanel.setActiveTab(comp);
					}
				}
				return true;
			}, this);
			this.accordionContacts.on('business', function(){
				this.centerContactsPanel.showBusiness();
			}, this);
			this.accordionContacts.on('address', function(){
				this.centerContactsPanel.showAddress();
			}, this);
			this.accordionContacts.on('expand', function(p){
				this.centerPanel.getLayout().setActiveItem(1);
			},this);
			this.inboxPanel.on('activate', function(){
				if (this.mailboxTree.rendered){
					this.mailboxTree.selectNode('INBOX');
				}
			}, this, {delay:500});
			this.inboxPanel.on('refresh', function(){
				this.refreshMailboxes();
			}, this);
			this.inboxPanel.on('refreshtree', function(){
				this.refreshMailboxTree();
			},this);
			this.inboxPanel.on('selectnode', function(panel){
				this.mailboxTree.selectNode(panel.config.folder);
			},this);
			this.centerTabPanel.on('remove', function(){
				if (this.centerTabPanel.items.getCount() == 0)
				{
					this.centerTabPanel.hide();		
				}
			}, this);
			this.centerContactsPanel.on('refresh', function(){
				this.refreshContacts();
			}, this);
			this.accordionMaps.on('expand', function(p){
				this.centerPanel.getLayout().setActiveItem(2);
				if (this.mapFirstActive){
					this.centerMapPanel.addRegionCombo();
					this.centerMapPanel.setSrc('googlemaps.html?_dc=' + new Date().getTime(), true, function(){
						this.execScript('checkResize()');
						this.execScript('getTrafficData("www.bbc.co.uk/travelnews/tpeg/en/local/rtm/norfolk_tpeg.xml")');
						
						
					});
					this.mapFirstActive = false;
				}
				this.centerMapPanel.getFrame().execScript('checkResize()');
			},this);
			this.centerPanel.on('show', function(){
				this.centerPanel.doLayout();
			}, this);
	        this.prefsButton = new Ext.Toolbar.Button({
	        	id: 'tbbuttonPreferences',
	        	text: 'Preferences',
	        	icon: 'images/Preferences16.png', 
		        cls: 'x-btn-text-icon',
	        	tooltip: '<b>Preferences</b><br/>Change the User Preferences',
				enableToggle: false,
				pressed: false,
				handler: function() {
					var prefWindow  = new Ext.ux.PreferencesWindow(Ext.state.Manager.get('Preferences'));
					prefWindow.on('saved', function(win, prefs){
						Ext.state.Manager.set('Preferences', prefs);
					}, this);
					prefWindow.show(this.prefsButton.getId());
				},
				scope: this
	        });
	        this.northToolbar.addItem(this.prefsButton);
			this.comboTheme = new Ext.form.ComboBox({
				id: 'comboTheme',
        		store: themeData,
        		displayField:'display',
        		typeAhead: true,
        		mode: 'local',
        		triggerAction: 'all',
        		selectOnFocus:true,
        		resizable:false,
        		listWidth: 100,
        		width: 100,
        		valueField: 'value',
        		value: Ext.state.Manager.get('Theme', '../css/xtheme-default.css')
    		});
		    this.comboTheme.on('select', function(combo){
				Ext.state.Manager.set('Theme', combo.getValue())
				Ext.util.CSS.swapStyleSheet('theme', combo.getValue());
		    }, this);
			this.northToolbar.setHeight(32);
			this.northToolbar.addFill();
			this.northToolbar.addText('Theme:');
			this.northToolbar.addSpacer();
			this.northToolbar.addSpacer();
			this.northToolbar.addField(this.comboTheme);
			this.selectInbox = new Ext.util.DelayedTask(function(){this.mailboxTree.tree.getNodeById('INBOX').select();}, this);
			this.selectInbox.delay(1000);
		},
		refreshMailboxes: function(){
			this.centerTabPanel.items.each(function(item, index, length){
				item.refreshMailbox();
			}, this);
		},
		refreshContacts: function(){
			contactsStore.reload();
		},
		refreshMailboxTree: function(){
			this.mailboxTree.refreshQuota();
			this.mailboxTree.refreshTree();
		},
		reloadMailboxTree: function(){
			this.mailboxTree.refreshQuota();
			this.mailboxTree.reloadTree();
		}
	};
}();
