webpackJsonp([5],{"42Hy":function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var s=["极弱","较弱","一般","较强","极强"],r=["#F56C6C","#E6A23C","#409EFF","#67C23A","green"],i={data:function(){return{collapsed:!1,avatar_color:"#00a2ae",avatar_head:"",parent:"默认",self:"欢迎页",list:[{title:"管理员",type:"safety",item:[{title:"管理员列表",path:"/admin/adminlist"}]},{title:"API管理",type:"profile",item:[{title:"项目列表",path:"/api/gameList"}]}],visible:!1,user_pwd:"",new_pwd:"",new_pwd_repeat:"",qr_visible:!1,allow_change_pwd:!0,PWD_LEVEL:s,pwd_level:0}},watch:{new_pwd:function(t){var e=this.checkPassWordLevel(t);this.pwd_level=e,this.allow_change_pwd=!(e>1)}},methods:{getText:function(t){return s[t]},getColor:function(t){return r[t]},handleClick:function(t){var e=t.keyPath[1];this.parent=this.list[e].title;var a=t.keyPath[0].charAt(t.keyPath[0].length-1);this.self=this.list[e].item[a].title;var s=this.list[e].item[a].path;this.$router.push(s)},handleOk:function(){if(""==this.user_pwd)return this.$message.error("旧密码不能为空!",1),!1;if(""==this.new_pwd)return this.$message.error("新密码不能为空!",1),!1;if(this.new_pwd.length>32)return this.$message.error("新密码长度不能超过32位!",1),!1;if(this.new_pwd_repeat!=this.new_pwd)return this.$message.error("两次输入的新密码不一致!",2),!1;this.visible=!1,this.$message.loading("修改密码中...",3);var t={user_name:this.user_name,user_pwd:this.user_pwd,new_pwd:this.new_pwd},e=this;this.$http.post("index.php?a=Admin&m=changeAdminPassword",t).then(function(t){e.$message.destroy(),e.user_pwd=e.new_pwd=e.new_pwd_repeat="",200==t.data.status?e.$message.success(t.data.msg,2):e.$message.error(t.data.msg,2)})},handleCancel:function(){this.visible=!1,this.qr_visible=!1,this.$message.destroy()},showModal:function(){this.visible=!0},showQRModal:function(){var t=this;this.qr_visible=!0;var e={user_name:this.user_name};this.$http.get("index.php?a=Admin&m=getSignature",{params:e}).then(function(e){200==e.data.status&&t.$nextTick(function(){this.createQR(this.$blind_api+this.user_name+"&signature="+e.data.signature)})})},createQR:function(t){var e=document.getElementById("qrccode-blind");this.QRCode.toCanvas(e,t,function(t){})},logOut:function(){var t=this;localStorage.removeItem(this.$game_user_name),localStorage.removeItem(this.$game_token);var e=this.$success({title:"退出成功!",content:"将在1秒后跳转至登录界面~"});setTimeout(function(){e.destroy(),t.$router.push("/login")},1e3)},checkPassWordLevel:function(t){var e=0;return/\d/.test(t)&&e++,/[a-z]/.test(t)&&e++,/[A-Z]/.test(t)&&e++,/\W/.test(t)&&e++,t.length>5||(e=0),t.length>9&&e++,e}},computed:{user_name:function(){return localStorage.getItem(this.$game_user_name)}},mounted:function(){var t=localStorage.getItem(this.$game_token);if(null==t)return this.$router.push("/login"),!1;var e=window.atob(t);e=JSON.parse(e);var a=this.$moment().valueOf();e.exp<a/1e3&&this.$router.push("/login")}},n={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"index"},[a("a-layout",{attrs:{id:"components-layout-demo-custom-trigger"}},[a("a-layout-sider",{attrs:{trigger:null,collapsible:""},model:{value:t.collapsed,callback:function(e){t.collapsed=e},expression:"collapsed"}},[a("div",{staticClass:"logo"},[a("a",{attrs:{href:""}},[a("a-avatar",{attrs:{size:46,src:"static/dummy.png",calss:"logo_img"}}),t._v(" "),a("span",[t._v(t._s(t.$game_zch))])],1)]),t._v(" "),a("a-menu",{attrs:{theme:"dark",mode:"inline",defaultSelectedKeys:["0"]},on:{click:t.handleClick}},t._l(t.list,function(e,s){return a("a-sub-menu",{key:s},[a("span",{attrs:{slot:"title"},slot:"title"},[a("a-icon",{attrs:{type:e.type}}),a("span",[t._v(t._s(e.title))])],1),t._v(" "),t._l(e.item,function(e,r){return a("a-menu-item",{key:s+"_"+r},[t._v("\n                    "+t._s(e.title)+"\n                ")])})],2)}))],1),t._v(" "),a("a-layout",[a("a-layout-header",{staticStyle:{background:"#fff",padding:"0","box-shadow":"0 1px 4px rgba(0,21,41,.08)","z-index":"100"}},[a("a-icon",{staticClass:"trigger",attrs:{type:t.collapsed?"menu-unfold":"menu-fold"},on:{click:function(){return t.collapsed=!t.collapsed}}}),t._v(" "),a("div",{staticClass:"header-right"},[a("div",{staticClass:"header-item"},[a("a-dropdown",{attrs:{placement:"bottomCenter"}},[a("a-badge",{attrs:{count:100,overflowCount:99}},[a("a-icon",{staticClass:"badge",attrs:{type:"bell"}})],1),t._v(" "),a("a-menu",{attrs:{slot:"overlay"},slot:"overlay"},[a("a-menu-item",[t._v("\n                                tom评论了您的最新朋友圈\n                            ")]),t._v(" "),a("a-menu-item",[t._v("\n                                Jim给您点赞\n                            ")])],1)],1)],1),t._v("\n                "+t._s(t.user_name)+"\n                "),a("div",{staticClass:"header-item"},[a("a-dropdown",{attrs:{placement:"bottomCenter"}},[a("a-avatar",{style:{backgroundColor:t.avatar_color},attrs:{size:48,src:t.avatar_head}},[t._v(t._s(t.user_name))]),t._v(" "),a("a-menu",{attrs:{slot:"overlay"},slot:"overlay"},[a("a-menu-item",{on:{click:t.showModal}},[a("a-icon",{attrs:{type:"lock"}}),t._v("修改密码\n                            ")],1),t._v(" "),a("a-menu-item",{on:{click:t.showQRModal}},[a("a-icon",{attrs:{type:"qrcode"}}),t._v("绑定令牌\n                            ")],1),t._v(" "),a("a-menu-item",{on:{click:t.logOut}},[a("a-icon",{attrs:{type:"poweroff"}}),t._v("退出登录\n                            ")],1)],1)],1)],1)])],1),t._v(" "),a("a-breadcrumb",{staticClass:"page-header"},[a("a-breadcrumb-item",[a("a",{attrs:{href:""}},[t._v("主页")])]),t._v(" "),a("a-breadcrumb-item",[t._v(t._s(t.parent))]),t._v(" "),a("a-breadcrumb-item",[t._v(t._s(t.self))])],1),t._v(" "),a("a-layout-content",{style:{margin:"6px",padding:"8px"}},[a("router-view")],1)],1)],1),t._v(" "),a("a-modal",{attrs:{title:"修改密码"},model:{value:t.visible,callback:function(e){t.visible=e},expression:"visible"}},[a("a-alert",{staticStyle:{"margin-bottom":"12px"},attrs:{message:"并请牢记您的新密码，否则只能联系PHP哦",banner:""}}),t._v(" "),a("a-form",[a("a-form-item",{staticStyle:{"margin-bottom":"12px"}},[a("a-input",{attrs:{placeholder:"请输入旧密码"},model:{value:t.user_pwd,callback:function(e){t.user_pwd=e},expression:"user_pwd"}},[a("a-icon",{attrs:{slot:"prefix",type:"user"},slot:"prefix"})],1)],1),t._v(" "),a("a-form-item",{staticStyle:{"margin-bottom":"12px"}},[a("a-input",{attrs:{placeholder:"请输入新密码,6-32位字符组合",type:"password"},model:{value:t.new_pwd,callback:function(e){t.new_pwd=e},expression:"new_pwd"}},[a("a-icon",{attrs:{slot:"prefix",type:"lock"},slot:"prefix"})],1)],1),t._v(" "),a("a-form-item",{staticStyle:{"margin-bottom":"12px"}},[a("a-input",{attrs:{placeholder:"请重复输入新密码,6-32位字符组合",type:"password"},model:{value:t.new_pwd_repeat,callback:function(e){t.new_pwd_repeat=e},expression:"new_pwd_repeat"}},[a("a-icon",{attrs:{slot:"prefix",type:"lock"},slot:"prefix"})],1)],1),t._v(" "),a("a-row",[a("a-col",{attrs:{span:4}},[a("label",[t._v("密码强度:")])]),t._v(" "),a("a-col",{attrs:{span:20}},[a("a-progress",{attrs:{percent:20+20*t.pwd_level,size:"small",strokeColor:t.getColor(t.pwd_level),format:function(){return t.getText(t.pwd_level)}}})],1)],1)],1),t._v(" "),a("template",{slot:"footer"},[a("a-button",{on:{click:t.handleCancel}},[t._v("取消")]),t._v(" "),a("a-button",{attrs:{type:"primary",disabled:t.allow_change_pwd},on:{click:t.handleOk}},[t._v("确认修改")])],1)],2),t._v(" "),a("a-modal",{attrs:{title:"绑定手机令牌"},model:{value:t.qr_visible,callback:function(e){t.qr_visible=e},expression:"qr_visible"}},[a("a-alert",{attrs:{message:"微信扫描此二维码，提示【绑定成功】即完成操作，以后即可使用微信扫码完成登录验证!重要信息，请勿泄露至他人!以防账号被盗",banner:""}}),t._v(" "),a("div",{staticStyle:{"text-align":"center"}},[a("canvas",{attrs:{id:"qrccode-blind"}})]),t._v(" "),a("a-row",[a("a-col",{staticStyle:{"text-align":"right",padding:"0.1rem 0.5rem"},attrs:{span:12}},[a("a-icon",{staticStyle:{color:"#00c1de","font-size":"2rem"},attrs:{type:"scan"}})],1),t._v(" "),a("a-col",{staticStyle:{"font-size":"0.5rem"},attrs:{span:12}},[a("p",{staticStyle:{"margin-bottom":"0"}},[t._v("打开"),a("span",{staticStyle:{color:"#00c1de"}},[t._v("微信")])]),t._v(" "),a("p",{staticStyle:{"margin-bottom":"0"}},[t._v("扫一扫绑定")])])],1),t._v(" "),a("template",{slot:"footer"},[a("span")])],2)],1)},staticRenderFns:[]};var o=a("VU/8")(i,n,!1,function(t){a("sa0s")},null,null);e.default=o.exports},sa0s:function(t,e){}});