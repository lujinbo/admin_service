webpackJsonp([8],{a96K:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var n=[{title:"管理员账号",dataIndex:"user_name"},{title:"请求后台",dataIndex:"game"},{title:"请求API",dataIndex:"api"},{title:"请求时间",dataIndex:"r_time"},{title:"请求IP",dataIndex:"r_ip"}],i={data:function(){return{columns:n,loading:!1,data:[],pagination:{pageSize:10},alter:{message:"数据加载中!",type:"info"},keyword:""}},computed:{table_data:function(){var e=this;return""==this.keyword?this.data:this.data.filter(function(t){return-1!=t.user_name.indexOf(e.keyword)||-1!=t.api.indexOf(e.keyword)||-1!=t.game.indexOf(e.keyword)||-1!=t.r_ip.indexOf(e.keyword)})}},methods:{fetch:function(e){var t=this;this.$http.get("index.php?a=Admin&m=getAdminRequestRecord&user_name="+e).then(function(e){t.loading=!1,200==e.data.status?(t.data=e.data.data,t.alter={message:"加载成功!",type:"success"}):t.alter={message:e.data.msg,type:"error"}})}},mounted:function(){var e=this.$route.params.user_name;this.fetch(e)}},r={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"com-item"},[a("a-input-search",{staticStyle:{width:"200px"},model:{value:e.keyword,callback:function(t){e.keyword=t},expression:"keyword"}}),e._v(" "),a("a-alert",{staticStyle:{margin:"12px 0"},attrs:{message:e.alter.message,type:e.alter.type,showIcon:"",closable:""}}),e._v(" "),a("a-table",{attrs:{columns:e.columns,dataSource:e.table_data,pagination:e.pagination,loading:e.loading}})],1)},staticRenderFns:[]},s=a("VU/8")(i,r,!1,null,null,null);t.default=s.exports}});